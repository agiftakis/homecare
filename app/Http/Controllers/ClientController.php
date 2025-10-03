<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\User;
use App\Models\Visit;
use App\Services\FirebaseStorageService;
use App\Traits\HandlesErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    use HandlesErrors;

    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        try {
            //SUPER ADMIN UPDATE: Check user role to determine which clients to show.
            $user = Auth::user();
            $query = Client::with('user'); // Start with a base query.

            if ($user->role === 'super_admin') {
                // If the user is a super_admin, we remove the default agency scope
                // to fetch clients from ALL agencies. We also eager-load the 'agency'
                // relationship so we can display the agency name in the clients.index view.
                $query->withoutGlobalScope('agencyScope')->with('agency');
            }

            // For agency_admins, the global scope will apply automatically,
            // showing only clients from their own agency.

            $clients = $query->latest()->get();
            return view('clients.index', compact('clients'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load clients. Please try again.', 'clients_index');
        }
    }

    public function create()
    {
        try {
            // ✅ NEW: Client Limit Check - Block access if limit reached
            if (Auth::user()->role === 'agency_admin') {
                $limitCheck = $this->checkClientLimit();
                if (!$limitCheck['allowed']) {
                    return redirect()->route('clients.index')->with('error', $limitCheck['message']);
                }
            }

            $agencies = [];
            if (Auth::user()->role === 'super_admin') {
                $agencies = Agency::orderBy('name')->get();
            }
            return view('clients.create', compact('agencies'));
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load client creation form.', 'clients_create');
        }
    }

    public function store(Request $request)
    {
        // ✅ NEW: Client Limit Check - Block creation if limit reached
        if (Auth::user()->role === 'agency_admin') {
            $limitCheck = $this->checkClientLimit();
            if (!$limitCheck['allowed']) {
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['error' => $limitCheck['message']], 403);
                }
                return back()->with('error', $limitCheck['message']);
            }
        }

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

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($e->errors());
        }

        // ✅ MODIFIED: Success message flashing and redirect back pattern
        try {
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

                // 3. Handle profile picture upload
                if ($request->hasFile('profile_picture')) {
                    try {
                        $clientData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                            $request->file('profile_picture'),
                            'client_profile_pictures'
                        );
                    } catch (\Exception $e) {
                        // Clean up the user if file upload fails
                        $user->delete();
                        throw new \Exception('Profile picture upload failed: ' . $e->getMessage());
                    }
                }

                // 4. Create the Client record with all data, including the user_id link.
                $client = Client::create($clientData);

                // 5. Generate and store the password setup token for the new user.
                $token = Str::random(60);
                $user->forceFill([
                    'password_setup_token' => hash('sha256', $token),
                    'password_setup_expires_at' => now()->addHours(48),
                ])->save();

                // 6. Flash the setup link to the session for the modal popup.
                $setupUrl = route('password.setup.show', ['token' => $token]);
                session()->flash('setup_link', $setupUrl);
            });

            // 7. Flash success message and redirect back - Alpine.js will handle timing and dashboard redirect
            session()->flash('success_message', 'New Client Added Successfully');
            session()->flash('redirect_to', route('dashboard'));
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Client creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create client. Please check your information and try again.')
                ->withInput();
        }
    }

    public function edit(Client $client)
    {
        $this->authorizeWithError('update', $client, 'You do not have permission to edit this client.');

        try {
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
        } catch (\Exception $e) {
            return $this->handleException($e, 'Unable to load client editing form.', 'clients_edit');
        }
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeWithError('update', $client, 'You do not have permission to update this client.');

        $validationRules = [
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
        ];

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($e->errors());
        }

        try {
            // Update the associated user record first
            if ($client->user) {
                $client->user->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }

            $updateData = $validated;

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                try {
                    // Delete old profile picture if exists
                    if ($client->profile_picture_path) {
                        $this->firebaseStorageService->deleteFile($client->profile_picture_path);
                    }

                    $updateData['profile_picture_path'] = $this->firebaseStorageService->uploadProfilePicture(
                        $request->file('profile_picture'),
                        'client_profile_pictures'
                    );

                    // ✅ CACHE FIX: Instantly forget the old URL so the new one can be fetched.
                    Cache::forget("client_{$client->id}_profile_picture_url");
                } catch (\Exception $e) {
                    throw new \Exception('Profile picture upload failed: ' . $e->getMessage());
                }
            }

            $client->update($updateData);

            // Flash success message and redirect back - Alpine.js will handle timing and dashboard redirect
            session()->flash('success_message', 'Client Updated Successfully');
            session()->flash('redirect_to', route('dashboard'));
            return redirect()->back();
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to update client. Please check your information and try again.', 'clients_update');
        }
    }

    /**
     * Soft delete implementation: This method performs a soft delete
     * and records which user performed the action. Associated files are NOT
     * deleted from storage, allowing for future restoration.
     */
    public function destroy(Client $client)
    {
        $this->authorizeWithError('delete', $client, 'You do not have permission to delete this client.');

        return $this->handleDatabaseTransaction(function () use ($client) {
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

            return $client;
        }, 'Client has been deactivated and archived.', 'Failed to delete client. Please try again.');
    }

    public function resendOnboardingLink(Client $client)
    {
        $this->authorizeWithError('update', $client, 'You do not have permission to resend the onboarding link for this client.');

        try {
            $user = $client->user;

            if (!$user) {
                return $this->successResponse('This client does not have a user account.', null, 400);
            }

            $token = Str::random(60);
            $user->forceFill([
                'password_setup_token' => hash('sha256', $token),
                'password_setup_expires_at' => now()->addHours(48),
            ])->save();

            $setupUrl = route('password.setup.show', ['token' => $token]);

            session()->flash('setup_link', $setupUrl);

            return $this->successResponse('New onboarding link generated successfully!');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to generate onboarding link.', 'client_resend_onboarding');
        }
    }

    // ✅ ENHANCED METHODS FOR MANAGING NOTES

    /**
     * Update a specific progress note.
     */
    public function updateNote(Request $request, Visit $visit)
    {
        $this->authorizeWithError('update', $visit->shift->client, 'You do not have permission to update notes for this client.');

        $validationRules = [
            'progress_notes' => 'required|string|max:5000',
        ];

        try {
            $validated = $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationError($e->errors());
        }

        return $this->handleDatabaseTransaction(function () use ($visit, $validated) {
            // ✅ NEW: Capture old value before update for audit trail
            $oldNotes = $visit->progress_notes;

            $visit->update([
                'progress_notes' => $validated['progress_notes'],
            ]);

            // ✅ NEW: Log the modification in the audit trail
            $visit->logModification('note_updated', [
                'progress_notes' => [
                    'from' => $oldNotes,
                    'to' => $validated['progress_notes']
                ]
            ]);

            return $visit;
        }, 'Care note updated successfully.', 'Failed to update care note. Please try again.');
    }

    /**
     * Delete a specific progress note.
     */
    public function destroyNote(Visit $visit)
    {
        $this->authorizeWithError('delete', $visit->shift->client, 'You do not have permission to delete notes for this client.');

        return $this->handleDatabaseTransaction(function () use ($visit) {
            // ✅ NEW: Capture old value before deletion for audit trail
            $oldNotes = $visit->progress_notes;

            // We don't delete the visit, just the notes associated with it.
            $visit->update(['progress_notes' => null]);

            // ✅ NEW: Log the modification in the audit trail
            $visit->logModification('note_deleted', [
                'progress_notes' => [
                    'from' => $oldNotes,
                    'to' => null
                ]
            ]);

            return $visit;
        }, 'Care note deleted successfully.', 'Failed to delete care note. Please try again.');
    }

    /**
     * ✅ NEW: Check if the agency has reached their client limit based on subscription plan
     */
    private function checkClientLimit()
    {
        $user = Auth::user();
        $agency = $user->agency;

        if (!$agency) {
            return ['allowed' => false, 'message' => 'Agency not found.'];
        }

        // ✅ FIXED: Get current client count for this agency using query builder
        $currentClientCount = Client::where('agency_id', $agency->id)->count();

        // Get client limit based on subscription plan
        $clientLimit = $this->getClientLimitForPlan($agency);

        if ($currentClientCount >= $clientLimit) {
            return [
                'allowed' => false,
                'message' => "You have reached your client limit of {$clientLimit} clients with your current subscription plan. Please upgrade your subscription to add more clients."
            ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * ✅ NEW: Get client limit based on agency's subscription plan
     */
    private function getClientLimitForPlan($agency)
    {
        // Get the subscription to determine the plan
        $subscription = $agency->subscription('default');

        if (!$subscription || !$subscription->active()) {
            return 10; // Default to basic plan limit if no active subscription
        }

        // Get plan name from subscription
        $stripePriceId = $subscription->stripe_price;

        return match ($stripePriceId) {
            env('STRIPE_PROFESSIONAL_PRICE_ID') => 30,
            env('STRIPE_PREMIUM_PRICE_ID') => 60,
            env('STRIPE_ENTERPRISE_PRICE_ID') => 350,
            env('STRIPE_BASIC_PRICE_ID') => 10,
            default => 10, // Default to basic plan limit
        };
    }

    /**
     * ✅ NEW: AJAX endpoint to check client limit (for frontend validation)
     */
    public function checkLimit()
    {
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['allowed' => true]);
        }

        $limitCheck = $this->checkClientLimit();

        return response()->json([
            'allowed' => $limitCheck['allowed'],
            'message' => $limitCheck['message']
        ]);
    }
}
