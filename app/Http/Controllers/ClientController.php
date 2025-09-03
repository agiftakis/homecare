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
        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // CHANGE: Email uniqueness is now scoped to the current agency
            'email' => [
                'required',
                'email',
                Rule::unique('clients')->where(function ($query) use ($agencyId) {
                    return $query->where('agency_id', $agencyId);
                }),
            ],
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture_url'] = $this->firebaseStorageService->uploadImage($request->file('profile_picture'));
        }

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Client added successfully!');
    }

    public function edit(Client $client)
    {
        // ADDITION: Authorize that the user can edit this client
        $this->authorize('update', $client);
        
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        // ADDITION: Authorize that the user can update this client
        $this->authorize('update', $client);

        $agencyId = Auth::user()->agency_id;

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // CHANGE: Email uniqueness is now scoped to the current agency
            'email' => [
                'required',
                'email',
                Rule::unique('clients')->where(function ($query) use ($agencyId) {
                    return $query->where('agency_id', $agencyId);
                })->ignore($client->id),
            ],
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            // First, delete the old picture
            $this->firebaseStorageService->deleteImage($client->profile_picture_url);
            // Then, upload the new one
            $validated['profile_picture_url'] = $this->firebaseStorageService->uploadImage($request->file('profile_picture'));
        }

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }
    
    /**
     * ADDITION: New destroy method
     */
    public function destroy(Client $client)
    {
        // ADDITION: Authorize that the user can delete this client
        $this->authorize('delete', $client);

        // Delete the profile picture from Firebase
        $this->firebaseStorageService->deleteImage($client->profile_picture_url);

        // Delete the client from the database
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}