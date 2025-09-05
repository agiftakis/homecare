<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Models\Agency; // <-- Import the Agency model
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
        // **THE FIX: Step 1**
        // If the user is a super_admin, fetch all agencies to pass to the view.
        $agencies = [];
        if (Auth::user()->role === 'super_admin') {
            $agencies = Agency::orderBy('name')->get();
        }
        return view('caregivers.create', compact('agencies'));
    }

    public function store(Request $request)
    {
        // **THE FIX: Step 2**
        // Add agency_id to validation rules ONLY for super_admin
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email'],
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

        // Add the unique rule dynamically based on the agency ID
        $validationRules['email'][] = Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId));

        $validated = $request->validate($validationRules);

        $caregiverName = $validated['first_name'] . '_' . $validated['last_name'];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'caregiver_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // Handle document uploads
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

        // **THE FIX: Step 3**
        // The $agencyId variable is already correctly set for both user roles.
        $validated['agency_id'] = $agencyId;

        Caregiver::create($validated);

        return redirect()->route('caregivers.index')->with('success', 'Caregiver added successfully!');
    }

    public function edit(Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);
        return view('caregivers.edit', compact('caregiver'));
    }

    public function update(Request $request, Caregiver $caregiver)
    {
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
