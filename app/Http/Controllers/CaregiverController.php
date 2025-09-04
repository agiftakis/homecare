<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CaregiverController extends Controller
{
    protected $firebaseStorageService;

    // Inject the service we already built
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
        return view('caregivers.create');
    }

    public function store(Request $request)
    {
        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('caregivers')->where(fn ($query) => $query->where('agency_id', $agencyId)),
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

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'caregiver_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // Handle document uploads with descriptive names
        if ($request->hasFile('certifications_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('certifications_document'),
                $caregiverName,
                'Certifications'
            );
            $validated['certifications_filename'] = $documentInfo['descriptive_filename'];
            $validated['certifications_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('professional_licenses_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('professional_licenses_document'),
                $caregiverName,
                'Professional_Licenses'
            );
            $validated['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
            $validated['professional_licenses_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('state_province_id_document')) {
            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('state_province_id_document'),
                $caregiverName,
                'State_Province_ID'
            );
            $validated['state_province_id_filename'] = $documentInfo['descriptive_filename'];
            $validated['state_province_id_path'] = $documentInfo['firebase_path'];
        }

        // Add agency_id to the validated data
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

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($caregiver->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
            }
            
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'caregiver_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // Handle document uploads with descriptive names
        if ($request->hasFile('certifications_document')) {
            // Delete old document if it exists
            if ($caregiver->certifications_path) {
                $this->firebaseStorageService->deleteFile($caregiver->certifications_path);
            }

            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('certifications_document'),
                $caregiverName,
                'Certifications'
            );
            $validated['certifications_filename'] = $documentInfo['descriptive_filename'];
            $validated['certifications_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('professional_licenses_document')) {
            // Delete old document if it exists
            if ($caregiver->professional_licenses_path) {
                $this->firebaseStorageService->deleteFile($caregiver->professional_licenses_path);
            }

            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('professional_licenses_document'),
                $caregiverName,
                'Professional_Licenses'
            );
            $validated['professional_licenses_filename'] = $documentInfo['descriptive_filename'];
            $validated['professional_licenses_path'] = $documentInfo['firebase_path'];
        }

        if ($request->hasFile('state_province_id_document')) {
            // Delete old document if it exists
            if ($caregiver->state_province_id_path) {
                $this->firebaseStorageService->deleteFile($caregiver->state_province_id_path);
            }

            $documentInfo = $this->firebaseStorageService->uploadDocument(
                $request->file('state_province_id_document'),
                $caregiverName,
                'State_Province_ID'
            );
            $validated['state_province_id_filename'] = $documentInfo['descriptive_filename'];
            $validated['state_province_id_path'] = $documentInfo['firebase_path'];
        }

        $caregiver->update($validated);

        return redirect()->route('caregivers.index')->with('success', 'Caregiver updated successfully!');
    }

    public function destroy(Caregiver $caregiver)
    {
        $this->authorize('delete', $caregiver);

        // Delete all files from Firebase if they exist
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