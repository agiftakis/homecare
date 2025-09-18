<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\User;
use App\Models\Visit;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// ✅ IMPORT: Added the Cache facade to clear the profile picture URL.
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        // Eager load the 'user' relationship to check onboarding status in the view.
        $clients = Client::with('user')->latest()->get();
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
            'email' => ['required', 'email', 'unique:users,email'], // Must be unique in the main users table
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

        $agencyId = Auth::user()->role === 'super_admin' ? $request->agency_id : Auth::user()->agency_id;

        if (Auth::user()->role === 'super_admin') {
            $validationRules['agency_id'] = 'required|exists:agencies,id';
        }

        $validated = $request->validate($validationRules);

        try {
            // Use a database transaction to ensure both records are created or neither are.
            DB::transaction(function () use ($validated, $agencyId, $request) {

                // 1. Create the User record FIRST to get its ID.
                $user = User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'agency_id' => $agencyId,
                    'role' => 'client',
                    'password' => bcrypt(Str::random(32)), // Temporary secure password
                ]);

                // 2. Prepare Client data, now including the new user_id.
                $clientData = $validated;
                $clientData['agency_id'] = $agencyId;
                $clientData['user_id'] = $user->id;

                if ($request->hasFile('profile_picture')) {
                    $clientData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                        $request->file('profile_picture'),
                        'client_profile_pictures'
                    );
                }

                // 3. Create the Client record with all data, including the user_id link.
                $client = Client::create($clientData);

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

            session()->flash('success', 'Client added successfully!');
            return redirect()->route('clients.index');
        } catch (\Exception $e) {
            Log::error('Client creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        // Fetch all visits for this client that have progress notes.
        // Order them by the most recent first.
        // Eager load the caregiver, including those who might have been soft-deleted.
        $visitsWithNotes = Visit::whereHas('shift', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
            ->whereNotNull('progress_notes')
            ->where('progress_notes', '!=', '')
            ->with(['shift.caregiver' => function ($query) {
                $query->withTrashed(); // Get caregiver's name even if they are soft-deleted
            }])
            ->orderBy('clock_out_time', 'desc')
            ->get();

        return view('clients.edit', compact('client', 'visitsWithNotes'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                // Ensure the email is unique in the users table, ignoring the current user.
                Rule::unique('users')->ignore($client->user_id),
            ],
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

        DB::transaction(function () use ($client, $validated, $request) {
            // Update the associated user record first
            if ($client->user) {
                $client->user->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }

            $updateData = $validated;

            if ($request->hasFile('profile_picture')) {
                if ($client->profile_picture_path) {
                    $this->firebaseStorageService->deleteFile($client->profile_picture_path);
                }
                $updateData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                    $request->file('profile_picture'),
                    'client_profile_pictures'
                );

                // ✅ CACHE FIX: Instantly forget the old URL so the new one can be fetched.
                Cache::forget("client_{$client->id}_profile_picture_url");
            }

            $client->update($updateData);
        });

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }

    /**
     * Soft delete implementation: This method performs a soft delete
     * and records which user performed the action. Associated files are NOT
     * deleted from storage, allowing for future restoration.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        DB::transaction(function () use ($client) {
            $adminId = Auth::id();
            $user = $client->user;

            // Update the deleted_by field for the audit trail, then soft delete.
            $client->update(['deleted_by' => $adminId]);
            $client->delete(); // This is now a soft delete.

            // Soft delete the associated user account as well.
            if ($user) {
                $user->update(['deleted_by' => $adminId]);
                $user->delete(); // This is now a soft delete.
            }

            // NOTE: We do NOT delete files from Firebase storage on a soft delete.
            // This ensures that if the client is ever restored, their profile
            // picture will also be restored.
        });

        return redirect()->route('clients.index')->with('success', 'Client has been deactivated and archived.');
    }

    public function resendOnboardingLink(Client $client)
    {
        $this->authorize('update', $client);

        $user = $client->user;

        if (!$user) {
            return redirect()->route('clients.index')->with('error', 'This client does not have a user account.');
        }

        $token = Str::random(60);
        $user->forceFill([
            'password_setup_token' => hash('sha256', $token),
            'password_setup_expires_at' => now()->addHours(48),
        ])->save();

        $setupUrl = route('password.setup.show', ['token' => $token]);

        session()->flash('success', 'New onboarding link generated successfully!');
        session()->flash('setup_link', $setupUrl);

        return redirect()->route('clients.index');
    }

    // ✅ NEW METHODS FOR MANAGING NOTES

    /**
     * Update a specific progress note.
     */
    public function updateNote(Request $request, Visit $visit)
    {
        // Use the client policy to authorize this action.
        // Ensures the admin belongs to the same agency as the client.
        $this->authorize('update', $visit->shift->client);

        $validated = $request->validate([
            'progress_notes' => 'required|string',
        ]);

        $visit->update([
            'progress_notes' => $validated['progress_notes'],
        ]);

        return redirect()->back()->with('success', 'Care note updated successfully.');
    }

    /**
     * Delete a specific progress note.
     */
    public function destroyNote(Visit $visit)
    {
        // Use the client policy to authorize this action.
        $this->authorize('delete', $visit->shift->client);

        // We don't delete the visit, just the notes associated with it.
        $visit->update(['progress_notes' => null]);

        return redirect()->back()->with('success', 'Care note deleted successfully.');
    }
}
