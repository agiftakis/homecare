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
        // Logic will be added here
    }

    /**
     * Show the form for viewing/editing the specified caregiver.
     */
    public function caregiverShow(Caregiver $caregiver)
    {
        // Logic will be added here
    }
}

