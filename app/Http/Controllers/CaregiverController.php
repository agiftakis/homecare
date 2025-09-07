<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Models\Agency;
use App\Models\User; // <-- NEW: Import User model
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // <-- NEW: Import Str for token generation

class CaregiverController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        $caregivers = Caregiver::latest()->get();
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

    public function store(Request $request)
    {
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // NEW: Added unique check on the 'users' table for system-wide email uniqueness
            'email' => ['required', 'email', 'unique:users,email'],
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

        // This rule is still good for ensuring a caregiver isn't added twice to the *same agency*
        $validationRules['email'][] = Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId));

        $validated = $request->validate($validationRules);

        $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];

        // Handle profile picture upload (your existing logic is perfect)
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'caregiver_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // Handle document uploads (your existing logic is perfect)
        if ($request->hasFile('certifications_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('certifications_document'), $caregiverName, 'Certifications');
            $validated['certifications_filename'] = $documentInfo['descriptive_filename'];
            $validated['certifications_path'] = $documentInfo['firebase_path'];
        }
        if ($request->hasFile('professional_licenses_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('professional_licenses_document'), $caregiverName, 'Professional_Licenses');
            $validated['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
            $validated['professional_licenses_path'] = $documentInfo['firebase_path'];
        }
        if ($request->hasFile('state_province_id_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('state_province_id_document'), $caregiverName, 'State_Province_ID');
            $validated['state_province_id_filename'] = $documentInfo['descriptive_filename'];
            $validated['state_province_id_path'] = $documentInfo['firebase_path'];
        }

        $validated['agency_id'] = $agencyId;

        Caregiver::create($validated);

        // --- NEW LOGIC STARTS HERE ---

        // 1. Create a corresponding User record for the caregiver
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'agency_id' => $agencyId,
            'role' => 'caregiver', // Assign the caregiver role
            'password' => null, // No password is set yet
        ]);

        // 2. Generate and store the secure token
        $token = Str::random(60);
        $user->forceFill([
            'password_setup_token' => hash('sha256', $token),
            'password_setup_expires_at' => now()->addHours(48),
        ])->save();

        // 3. Generate the setup link to flash to the session
        // Note: We need to create this route 'password.setup.show' in the next step
        $setupUrl = route('password.setup.show', ['token' => $token]);

        // --- NEW LOGIC ENDS HERE ---

        return redirect()->route('caregivers.index')->with([
            'success' => 'Caregiver added successfully!',
            'setup_link' => $setupUrl // Pass the link to the view
        ]);
    }

    public function edit(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);
        return view('caregivers.edit', compact('caregiver'));
    }

    public function update(Request $request, Caregiver $caregiver)
    {
        // ... (Your update method is unchanged and correct)
        $this->authorize('update', $caregiver);
        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId))->ignore($caregiver->id),
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ]);

        $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];

        if ($request->hasFile('profile_picture')) {
            if ($caregiver->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
            }
            $validated['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture($request->file('profile_picture'), 'caregiver_profile_pictures');
        }

        if ($request->hasFile('certifications_document')) {
            if ($caregiver->certifications_path) {
                $this->firebaseStorageService->deleteFile($caregiver->certifications_path);
            }
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('certifications_document'), $caregiverName, 'Certifications');
            $validated['certifications_filename'] = $documentInfo['descriptive_filename'];
            $validated['certifications_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('professional_licenses_document')) {
            if ($caregiver->professional_licenses_path) {
                $this->firebaseStorageService->deleteFile($caregiver->professional_licenses_path);
            }
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('professional_licenses_document'), $caregiverName, 'Professional_Licenses');
            $validated['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
            $validated['professional_licenses_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('state_province_id_document')) {
            if ($caregiver->state_province_id_path) {
                $this->firebaseStorageService->deleteFile($caregiver->state_province_id_path);
            }
            $documentInfo = $this->firebaseStorageService->uploadDocument($request->file('state_province_id_document'), $caregiverName, 'State_Province_ID');
            $validated['state_province_id_filename'] = $documentInfo['descriptive_filename'];
            $validated['state_province_id_path'] = $documentInfo['firebase_path'];
        }

        $caregiver->update($validated);

        return redirect()->route('caregivers.index')->with('success', 'Caregiver updated successfully!');
    }

    public function destroy(Caregiver $caregiver)
    {
        // ... (Your destroy method is unchanged and correct)
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

        $caregiver->delete();

        return redirect()->route('caregivers.index')->with('success', 'Caregiver deleted successfully.');
    }
}