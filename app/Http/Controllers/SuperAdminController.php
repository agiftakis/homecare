<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Caregiver;
use Illuminate\Http\Request;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    /**
     * Display the super admin dashboard.
     */
    public function index()
    {
        $agencies = Agency::with('owner')->get();
        return view('superadmin.dashboard', compact('agencies'));
    }

    /**
     * Display a listing of all clients from all agencies.
     */
    public function clientsIndex()
    {
        // Use withoutGlobalScope to bypass the BelongsToAgency scope
        $clients = Client::withoutGlobalScope('agencyScope')->with('agency')->get();
        return view('superadmin.clients.index', compact('clients'));
    }

    /**
     * Show the form for viewing/editing the specified client.
     */
    public function clientShow(Client $client)
    {
        // No scope needed here as route model binding handles it
        return view('superadmin.clients.show', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function clientUpdate(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Ensure email is unique within its own agency, but allow the current client's email.
                Rule::unique('clients')->where(function ($query) use ($client) {
                    return $query->where('agency_id', $client->agency_id);
                })->ignore($client->id),
            ],
            'phone_number' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string|max:255',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if it exists
            if ($client->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($client->profile_picture_path);
            }
            // Upload new picture and get the path
            $path = $this->firebaseStorageService->uploadProfilePicture($request->file('profile_picture'), 'client_profiles');
            $validatedData['profile_picture_path'] = $path;
        }

        $client->update($validatedData);

        return redirect()->route('superadmin.clients.show', $client)->with('success', 'Client profile has been updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function clientDestroy(Client $client)
    {
        if ($client->profile_picture_path) {
            $this->firebaseStorageService->deleteFile($client->profile_picture_path);
        }
        $client->delete();

        return redirect()->route('superadmin.clients.index')->with('success', 'Client profile has been deleted successfully.');
    }


    /**
     * Display a listing of all caregivers from all agencies.
     */
    public function caregiversIndex()
    {
        $caregivers = Caregiver::withoutGlobalScope('agencyScope')->with('agency')->get();
        return view('superadmin.caregivers.index', compact('caregivers'));
    }

    /**
     * Show the form for viewing/editing the specified caregiver.
     */
    public function caregiverShow(Caregiver $caregiver)
    {
        return view('superadmin.caregivers.show', compact('caregiver'));
    }

    /**
     * Update the specified caregiver in storage.
     */
    public function caregiverUpdate(Request $request, Caregiver $caregiver)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                 Rule::unique('caregivers')->where(function ($query) use ($caregiver) {
                    return $query->where('agency_id', $caregiver->agency_id);
                })->ignore($caregiver->id),
            ],
            'phone_number' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'certifications' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'certifications_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'professional_licenses_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
            'state_province_id_document' => 'nullable|file|mimes:pdf,docx,jpeg,png,jpg,gif|max:10240',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($caregiver->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
            }
            $path = $this->firebaseStorageService->uploadProfilePicture($request->file('profile_picture'), 'caregiver_profiles');
            $validatedData['profile_picture_path'] = $path;
        }

        $documentTypes = [
            'certifications',
            'professional_licenses',
            'state_province_id',
        ];

        foreach ($documentTypes as $type) {
            if ($request->hasFile("{$type}_document")) {
                if ($caregiver->{"{$type}_path"}) {
                    $this->firebaseStorageService->deleteFile($caregiver->{"{$type}_path"});
                }
                $file = $request->file("{$type}_document");
                $caregiverName = $validatedData['first_name'] . ' ' . $validatedData['last_name'];
                $documentInfo = $this->firebaseStorageService->uploadDocument($file, $caregiverName, $type);
                
                $validatedData["{$type}_path"] = $documentInfo['firebase_path'];
                $validatedData["{$type}_filename"] = $documentInfo['descriptive_filename'];
            }
        }

        $caregiver->update($validatedData);

        return redirect()->route('superadmin.caregivers.show', $caregiver)->with('success', 'Caregiver profile updated successfully.');
    }

    /**
     * Remove the specified caregiver from storage.
     */
    public function caregiverDestroy(Caregiver $caregiver)
    {
        // Delete profile picture
        if ($caregiver->profile_picture_path) {
            $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
        }
        // Delete all associated documents
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

        return redirect()->route('superadmin.caregivers.index')->with('success', 'Caregiver profile deleted successfully.');
    }
}

