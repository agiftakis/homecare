<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Models\Agency;
use App\Models\User;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class CaregiverController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        // Eager load the 'user' relationship to check onboarding status in the view.
        $caregivers = Caregiver::with('user')->latest()->get();
        return view('caregivers.index', compact('caregivers'));
    }

    public function create()
    {
        $agencies = [];
        if (Auth::user()->role === 'super_admin') {
            $agencies = Agency::orderBy('name')->get();
        }
        return view('caregivers.create', compact('agencies'));
    }

    /**
     * Store a newly created resource in storage.
     * ✅ THIS IS THE FULLY CORRECTED METHOD
     */
    public function store(Request $request)
    {
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email'], // Must be unique in the main users table
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ];

        $agencyId = Auth::user()->role === 'super_admin' ? $request->agency_id : Auth::user()->agency_id;

        if (Auth::user()->role === 'super_admin') {
            $validationRules['agency_id'] = 'required|exists:agencies,id';
        }

        $validated = $request->validate($validationRules);

        // Use a database transaction to ensure both records are created or neither are.
        DB::transaction(function () use ($validated, $agencyId, $request) {

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

            if ($request->hasFile('profile_picture')) {
                $caregiverData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture($request->file('profile_picture'), 'caregiver_profile_pictures');
            }
            if ($request->hasFile('certifications_document')) {
                $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('certifications_document'), $caregiverName, 'Certifications');
                $caregiverData['certifications_filename'] = $documentInfo['descriptive_filename'];
                $caregiverData['certifications_path'] = $documentInfo['firebase_path'];
            }
            if ($request->hasFile('professional_licenses_document')) {
                $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('professional_licenses_document'), $caregiverName, 'Professional_Licenses');
                $caregiverData['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
                $caregiverData['professional_licenses_path'] = $documentInfo['firebase_path'];
            }
            if ($request->hasFile('state_province_id_document')) {
                $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('state_province_id_document'), $caregiverName, 'State_Province_ID');
                $caregiverData['state_province_id_filename'] = $documentInfo['descriptive_filename'];
                $caregiverData['state_province_id_path'] = $documentInfo['firebase_path'];
            }

            // 3. Create the Caregiver record with all data, including the user_id link.
            Caregiver::create($caregiverData);

            // 4. Generate and store the password setup token for the new user.
            $token = Str::random(60);
            $user->forceFill([
                'password_setup_token' => hash('sha256', $token),
                'password_setup_expires_at' => now()->addHours(48),
            ])->save();

            // 5. Flash the setup link to the session for the modal popup.
            $setupUrl = route('password.setup.show', ['token' => $token]);
            session()->flash('setup_link', $setupUrl);
        });

        session()->flash('success', 'Caregiver added successfully!');
        return redirect()->route('caregivers.index');
    }

    public function edit(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);
        return view('caregivers.edit', compact('caregiver'));
    }

    public function update(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $validated = $request->validate([
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
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ]);

        DB::transaction(function () use ($caregiver, $validated, $request) {
            // Update the associated user record first
            if ($caregiver->user) {
                $caregiver->user->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }

            $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];
            $updateData = $validated;

            if ($request->hasFile('profile_picture')) {
                if ($caregiver->profile_picture_path) {
                    $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
                }
                $updateData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture($request->file('profile_picture'), 'caregiver_profile_pictures');
            }
            // ... (Handle other file uploads similarly) ...

            $caregiver->update($updateData);
        });

        return redirect()->route('caregivers.index')->with('success', 'Caregiver updated successfully!');
    }

    public function destroy(Caregiver $caregiver)
    {
        $this->authorize('delete', $caregiver);

        if ($caregiver->profile_picture_path) {
            $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
        }
        if ($caregiver->certifications_path) {
            $this->firebaseStorageService->deleteFile($caregiver->certifications_path);
        }
        if ($caregiver->professional_licenses_path) {
            $this->firebaseStorageService->deleteFile($caregiver->professional_licenses_path);
        }
        if ($caregiver->state_province_id_path) {
            $this->firebaseStorageService->deleteFile($caregiver->state_province_id_path);
        }
        
        // Use a transaction to ensure both deletions succeed or fail together.
        DB::transaction(function() use ($caregiver) {
            // Delete the associated user first to maintain data integrity
            if ($caregiver->user) {
                $caregiver->user->delete();
            }
            // Then delete the caregiver record
            $caregiver->delete();
        });

        return redirect()->route('caregivers.index')->with('success', 'Caregiver deleted successfully.');
    }

    public function resendOnboardingLink(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $user = $caregiver->user;

        if (!$user) {
            return redirect()->route('caregivers.index')->with('error', 'This caregiver does not have a user account.');
        }

        $token = Str::random(60);
        $user->forceFill([
            'password_setup_token' => hash('sha256', $token),
            'password_setup_expires_at' => now()->addHours(48),
        ])->save();

        $setupUrl = route('password.setup.show', ['token' => $token]);
        
        session()->flash('success', 'New onboarding link generated successfully!');
        session()->flash('setup_link', $setupUrl);

        return redirect()->route('caregivers.index');
    }
}

