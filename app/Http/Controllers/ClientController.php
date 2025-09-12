<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
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
        $agencies = [];
        if (Auth::user()->role === 'super_admin') {
            $agencies = Agency::orderBy('name')->get();
        }
        return view('clients.create', compact('agencies'));
    }

    public function store(Request $request)
    {
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

        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $this->firebaseStorageService->uploadProfilePicture(
                $request->file('profile_picture'),
                'client_profile_pictures'
            );
            $validated['profile_picture_path'] = $profilePicturePath;
        }

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

    /**
     * ✅ SOFT DELETE IMPLEMENTATION: This method now performs a soft delete
     * and records which user performed the action. Associated files are NOT
     * deleted from storage, allowing for future restoration.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Update the deleted_by field for the audit trail.
        $client->update(['deleted_by' => Auth::id()]);

        // This will now perform a soft delete because the trait is used in the model.
        $client->delete();

        // NOTE: We do NOT delete the profile picture on soft delete to allow for restoration.

        return redirect()->route('clients.index')->with('success', 'Client has been deactivated and archived.');
    }
}