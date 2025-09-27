<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Caregiver;
use App\Models\User;
use App\Services\FirebaseStorageService;
use App\Traits\HandlesErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CaregiverController extends Controller
{
    use HandlesErrors;

    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        try {
            // ✅ SUPER ADMIN UPDATE: Check user role to determine which caregivers to show.
            $user = Auth::user();
            $query = Caregiver::with('user'); // Start with a base query.

            if ($user->role === 'super_admin') {
                // If the user is a super_admin, we remove the default agency scope
                // to fetch caregivers from ALL agencies. We also eager-load the 'agency'
                // relationship so we can display the agency name in the view.
                $query->withoutGlobalScope('agencyScope')->with('agency');
            }

            // For agency_admins, the global scope will apply automatically.
            $caregivers = $query->latest()->get();
            return view('caregivers.index', compact('caregivers'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load caregivers. Please try again.', 'caregivers_index');
        }
    }

    public function create()
    {
        try {
            $agencies = [];
            if (Auth::user()->role === 'super_admin') {
                $agencies = Agency::orderBy('name')->get();
            }
            return view('caregivers.create', compact('agencies'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load caregiver creation form.', 'caregivers_create');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email'], // Must be unique in the main users table
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ];

        $agencyId = Auth::user()->role === 'super_admin' ? $request->agency_id : Auth::user()->agency_id;

        if (Auth::user()->role === 'super_admin') {
            $validationRules['agency_id'] = 'required|exists:agencies,id';
        }

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($e->errors());
        }

        try {
            // 1. Create the User record FIRST to get its ID.
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'agency_id' => $agencyId,
                'role' => 'caregiver',
                'password' => bcrypt(Str::random(32)), // Temporary secure password
            ]);

            // 2. Prepare Caregiver data, now including the new user_id.
            $caregiverData = $validated;
            $caregiverData['agency_id'] = $agencyId;
            $caregiverData['user_id'] = $user->id;

            $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];

            // 3. Handle file uploads with proper error handling
            try {
                if ($request->hasFile('profile_picture')) {
                    $caregiverData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                        $request->file('profile_picture'),
                        'caregiver_profile_pictures'
                    );
                }

                if ($request->hasFile('certifications_document')) {
                    $documentInfo = $this->firebaseStorageService->uploadDocument(
                        $request->file('certifications_document'),
                        $caregiverName,
                        'Certifications'
                    );
                    $caregiverData['certifications_filename'] = $documentInfo['descriptive_filename'];
                    $caregiverData['certifications_path'] = $documentInfo['firebase_path'];
                }

                if ($request->hasFile('professional_licenses_document')) {
                    $documentInfo = $this->firebaseStorageService->uploadDocument(
                        $request->file('professional_licenses_document'),
                        $caregiverName,
                        'Professional_Licenses'
                    );
                    $caregiverData['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
                    $caregiverData['professional_licenses_path'] = $documentInfo['firebase_path'];
                }

                if ($request->hasFile('state_province_id_document')) {
                    $documentInfo = $this->firebaseStorageService->uploadDocument(
                        $request->file('state_province_id_document'),
                        $caregiverName,
                        'State_Province_ID'
                    );
                    $caregiverData['state_province_id_filename'] = $documentInfo['descriptive_filename'];
                    $caregiverData['state_province_id_path'] = $documentInfo['firebase_path'];
                }
            } catch (\Exception $e) {
                // Clean up user if file upload fails
                $user->delete();
                throw new \Exception('Document upload failed: ' . $e->getMessage());
            }

            // 4. Create the Caregiver record with all data, including the user_id link.
            $caregiver = Caregiver::create($caregiverData);

            // 5. Generate and store the password setup token for the new user.
            $token = Str::random(60);
            $user->forceFill([
                'password_setup_token' => hash('sha256', $token),
                'password_setup_expires_at' => now()->addHours(48),
            ])->save();

            // 6. Flash the setup link to the session for the modal popup.
            $setupUrl = route('password.setup.show', ['token' => $token]);
            session()->flash('setup_link', $setupUrl);

            // 7. Flash success message and redirect back - Alpine.js will handle timing and dashboard redirect
            session()->flash('success_message', 'New Caregiver Added Successfully');
            session()->flash('redirect_to', route('dashboard'));
            return redirect()->back();

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create caregiver. Please check your information and try again.', 'caregivers_store');
        }
    }

    public function edit(Caregiver $caregiver)
    {
        $this->authorizeWithError('update', $caregiver, 'You do not have permission to edit this caregiver.');

        try {
            return view('caregivers.edit', compact('caregiver'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load caregiver editing form.', 'caregivers_edit');
        }
    }

    public function update(Request $request, Caregiver $caregiver)
    {
        $this->authorizeWithError('update', $caregiver, 'You do not have permission to update this caregiver.');

        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                // Ensure the email is unique in the users table, ignoring the current user.
                Rule::unique('users')->ignore($caregiver->user_id),
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ];

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($e->errors());
        }

        try {
            // Update the associated user record first
            if ($caregiver->user) {
                $caregiver->user->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }

            $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];
            $updateData = $validated;

            // Handle file uploads with proper error handling
            try {
                if ($request->hasFile('profile_picture')) {
                    if ($caregiver->profile_picture_path) {
                        $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
                    }
                    $updateData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                        $request->file('profile_picture'),
                        'caregiver_profile_pictures'
                    );

                    // Clear cached URL so the new picture appears immediately.
                    Cache::forget("caregiver_{$caregiver->id}_profile_picture_url");
                }

                // ✅ FUNCTIONALITY FIX: Handle document uploads on update.
                $documentTypes = [
                    'certifications',
                    'professional_licenses',
                    'state_province_id',
                ];

                foreach ($documentTypes as $type) {
                    if ($request->hasFile("{$type}_document")) {
                        // Delete the old file if it exists
                        if ($caregiver->{"{$type}_path"}) {
                            $this->firebaseStorageService->deleteFile($caregiver->{"{$type}_path"});
                        }

                        $file = $request->file("{$type}_document");
                        $documentInfo = $this->firebaseStorageService->uploadDocument(
                            $file,
                            $caregiverName,
                            Str::studly($type)
                        );

                        $updateData["{$type}_path"] = $documentInfo['firebase_path'];
                        $updateData["{$type}_filename"] = $documentInfo['descriptive_filename'];
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception('Document upload failed: ' . $e->getMessage());
            }

            $caregiver->update($updateData);

            // Flash success message and redirect back - Alpine.js will handle timing and dashboard redirect
            session()->flash('success_message', 'Caregiver Updated Successfully');
            session()->flash('redirect_to', route('dashboard'));
            return redirect()->back();

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update caregiver. Please check your information and try again.', 'caregivers_update');
        }
    }

    /**
     * Soft delete implementation.
     */
    public function destroy(Caregiver $caregiver)
    {
        $this->authorizeWithError('delete', $caregiver, 'You do not have permission to delete this caregiver.');

        return $this->handleDatabaseTransaction(function () use ($caregiver) {
            $adminId = Auth::id();
            $user = $caregiver->user;

            // Update the deleted_by field for the audit trail, then soft delete.
            $caregiver->update(['deleted_by' => $adminId]);
            $caregiver->delete(); // This is now a soft delete.

            // Soft delete the associated user account as well.
            if ($user) {
                $user->update(['deleted_by' => $adminId]);
                $user->delete(); // This is now a soft delete.
            }

            return $caregiver;
        }, 'Caregiver has been deactivated and archived.', 'Failed to delete caregiver. Please try again.');
    }

    public function resendOnboardingLink(Caregiver $caregiver)
    {
        $this->authorizeWithError('update', $caregiver, 'You do not have permission to resend the onboarding link for this caregiver.');

        try {
            $user = $caregiver->user;

            if (!$user) {
                return $this->successResponse('This caregiver does not have a user account.', null, 400);
            }

            $token = Str::random(60);
            $user->forceFill([
                'password_setup_token' => hash('sha256', $token),
                'password_setup_expires_at' => now()->addHours(48),
            ])->save();

            $setupUrl = route('password.setup.show', ['token' => $token]);

            session()->flash('setup_link', $setupUrl);

            return $this->successResponse('New onboarding link generated successfully!');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to generate onboarding link.', 'caregiver_resend_onboarding');
        }
    }
}