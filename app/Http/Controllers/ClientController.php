<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected $firebaseStorageService;

    // Use dependency injection to get our new service
    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        // This is already correctly scoped by your BelongsToAgency trait
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:clients,email,NULL,id,agency_id,' . Auth::user()->agency_id
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string|max:255',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'client_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // Add agency_id to the validated data
        $validated['agency_id'] = Auth::user()->agency_id;

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Client added successfully!');
    }

    public function edit(Client $client)
    {
        // Authorize that the user can edit this client
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        // Authorize that the user can update this client
        $this->authorize('update', $client);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:clients,email,' . $client->id . ',id,agency_id,' . Auth::user()->agency_id
            ],
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string|max:255',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($client->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($client->profile_picture_path);
            }
            
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'client_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Delete profile picture from Firebase if it exists
        if ($client->profile_picture_path) {
            $this->firebaseStorageService->deleteFile($client->profile_picture_path);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}