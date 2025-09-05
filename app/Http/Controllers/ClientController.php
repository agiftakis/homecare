<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Agency; // <-- Import the Agency model
use Illuminate\Http\Request;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        // **THE FIX: Step 1**
        // If the user is a super_admin, fetch all agencies to pass to the view.
        $agencies = [];
        if (Auth::user()->role === 'super_admin') {
            $agencies = Agency::orderBy('name')->get();
        }
        return view('clients.create', compact('agencies'));
    }

    public function store(Request $request)
    {
        // **THE FIX: Step 2**
        // Add agency_id to validation rules ONLY for super_admin
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email',
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
        ];

        if (Auth::user()->role === 'super_admin') {
            $validationRules['agency_id'] = 'required|exists:agencies,id';
        }

        $validated = $request->validate($validationRules);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'client_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

        // **THE FIX: Step 3**
        // Assign agency_id based on user role
        if (Auth::user()->role === 'super_admin') {
            $validated['agency_id'] = $request->agency_id;
        } else {
            $validated['agency_id'] = Auth::user()->agency_id;
        }


        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Client added successfully!');
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email,' . $client->id,
            'phone_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($client->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($client->profile_picture_path);
            }
            $validated['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'client_profile_pictures'
            );
        }

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        if ($client->profile_picture_path) {
            $this->firebaseStorageService->deleteFile($client->profile_picture_path);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
