<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use DateTimeZone; // ✅ NEW: Add DateTimeZone for timezone logic

class SuperAdminController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    /**
     * Display the super admin dashboard with agency counts.
     */
    public function index()
    {
        $agencies = Agency::with('owner')
            ->withCount(['clients', 'caregivers'])
            ->get();

        return view('superadmin.dashboard', compact('agencies'));
    }

    /**
     * Display a listing of all clients from all agencies (with optional agency filter).
     */
    public function clientsIndex(Request $request)
    {
        $query = Client::withoutGlobalScope('agencyScope')->with('agency');

        // Apply agency filter if provided
        if ($request->has('agency') && $request->agency) {
            $query->where('agency_id', $request->agency);
            $agency = Agency::find($request->agency);
            $pageTitle = $agency ? "Clients from {$agency->name}" : "Filtered Clients";
        } else {
            $pageTitle = "All Clients (SuperAdmin View)";
        }

        $clients = $query->get();

        return view('superadmin.clients.index', compact('clients', 'pageTitle'));
    }

    /**
     * Show the form for viewing/editing the specified client.
     */
    public function clientShow(Client $client)
    {
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
                Rule::unique('clients')->where(function ($query) use ($client) {
                    return $query->where('agency_id', $client->agency_id);
                })->ignore($client->id),
            ],
            'phone_number' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'care_plan' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_medications' => 'nullable|string',
            'discontinued_medications' => 'nullable|string',
            'recent_hospitalizations' => 'nullable|string',
            'current_concurrent_dx' => 'nullable|string',
            'designated_poa' => 'nullable|string|max:255',
            'current_routines_am_pm' => 'nullable|string',
            'fall_risk' => 'nullable|in:yes,no',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($client->profile_picture_path) {
                $this->firebaseStorageService->deleteFile($client->profile_picture_path);
            }
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
     * Display a listing of all caregivers from all agencies (with optional agency filter).
     */
    public function caregiversIndex(Request $request)
    {
        $query = Caregiver::withoutGlobalScope('agencyScope')->with('agency');

        // Apply agency filter if provided
        if ($request->has('agency') && $request->agency) {
            $query->where('agency_id', $request->agency);
            $agency = Agency::find($request->agency);
            $pageTitle = $agency ? "Caregivers from {$agency->name}" : "Filtered Caregivers";
        } else {
            $pageTitle = "All Caregivers";
        }

        $caregivers = $query->get();

        return view('superadmin.caregivers.index', compact('caregivers', 'pageTitle'));
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

    /**
     * Completely delete an agency and all associated data.
     * This method should only be called for agencies with inactive status.
     */
    public function destroyAgency(Agency $agency)
    {
        // Safety check: Only allow deletion of agencies that are NOT activated (not lifetime free)
        if ($agency->is_lifetime_free) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Cannot delete activated agency. Please deactivate the agency first by unchecking "Lifetime Free" in the edit form.');
        }

        try {
            DB::transaction(function () use ($agency) {
                // 1. Delete all client profile pictures and records
                $clients = Client::withoutGlobalScope('agencyScope')
                    ->where('agency_id', $agency->id)
                    ->get();

                foreach ($clients as $client) {
                    if ($client->profile_picture_path) {
                        $this->firebaseStorageService->deleteFile($client->profile_picture_path);
                    }
                    $client->delete();
                }

                // 2. Delete all caregiver documents, profile pictures, and records
                $caregivers = Caregiver::withoutGlobalScope('agencyScope')
                    ->where('agency_id', $agency->id)
                    ->get();

                foreach ($caregivers as $caregiver) {
                    // Delete profile picture
                    if ($caregiver->profile_picture_path) {
                        $this->firebaseStorageService->deleteFile($caregiver->profile_picture_path);
                    }
                    // Delete all documents
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
                }

                // 3. Delete all shifts associated with this agency
                Shift::withoutGlobalScope('agencyScope')
                    ->where('agency_id', $agency->id)
                    ->delete();

                // 4. Delete all users associated with this agency (except the owner, we'll delete that last)
                $users = User::where('agency_id', $agency->id)
                    ->where('id', '!=', $agency->user_id)
                    ->get();

                foreach ($users as $user) {
                    $user->delete();
                }

                // 5. Delete the owner user
                if ($agency->owner) {
                    $agency->owner->delete();
                }

                // 6. Finally, delete the agency itself
                $agency->delete();
            });

            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Agency and all associated data have been permanently deleted.');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'An error occurred while deleting the agency. Please try again.');
        }
    }



    /**
     * Display a listing of all shifts from all agencies (with optional agency filter).
     */
    public function scheduleIndex(Request $request)
    {
        $query = Shift::withoutGlobalScope('agencyScope')->with(['client', 'caregiver', 'agency']);

        // Apply agency filter if provided
        if ($request->has('agency') && $request->agency) {
            $query->where('agency_id', $request->agency);
            $agency = Agency::find($request->agency);
            $pageTitle = $agency ? "Schedule for {$agency->name}" : "Filtered Schedule";
        } else {
            $pageTitle = "All Schedules (SuperAdmin View)";
        }

        $shifts = $query->orderBy('start_time', 'desc')->get();

        return view('superadmin.schedule.index', compact('shifts', 'pageTitle'));
    }

    // START: NEW METHODS FOR SUPER ADMIN AGENCY MANAGEMENT

    /**
     * Display a listing of the agencies.
     */
    public function agenciesIndex()
    {
        $agencies = Agency::withCount('users')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('superadmin.agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new agency.
     */
    public function agencyCreate()
    {
        // ✅ NEW: Generate North America timezone list for the dropdown
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        return view('superadmin.agencies.create', compact('northAmericaTimezones'));
    }

    /**
     * Store a newly created agency and its admin user in storage.
     */
    public function agencyStore(Request $request)
    {
        // ✅ NEW: Generate North America timezone list for validation
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        $validated = $request->validate([
            // Agency details
            'agency_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255|unique:agencies,contact_email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_lifetime_free' => 'sometimes|boolean',
            'timezone' => ['required', 'string', Rule::in($northAmericaTimezones)], // ✅ NEW: Timezone validation

            // Agency Admin user details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email|max:255',
            'admin_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        DB::beginTransaction();

        try {
            // Create Agency
            $agency = Agency::create([
                'name' => $validated['agency_name'],
                'contact_email' => $validated['contact_email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_lifetime_free' => $validated['is_lifetime_free'] ?? false,
                'timezone' => $validated['timezone'], // ✅ NEW: Save timezone
            ]);

            // Create Agency Admin User
            $adminUser = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'role' => 'agency_admin',
                'agency_id' => $agency->id,
                'email_verified_at' => now(), // Super admins create verified users
            ]);

            // Link the admin user as the agency owner
            $agency->user_id = $adminUser->id;
            $agency->save();

            DB::commit();

            return redirect()->route('superadmin.agencies.index')
                ->with('success', "Agency '{$agency->name}' created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception message for debugging
            // Log::error('Agency Creation Failed: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create agency. An unexpected error occurred.');
        }
    }

    /**
     * Show the form for editing the specified agency.
     */
    public function agencyEdit(Agency $agency)
    {
        return view('superadmin.agencies.edit', compact('agency'));
    }

    /**
     * Update the specified agency in storage.
     */
    public function agencyUpdate(Request $request, Agency $agency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => ['required', 'email', 'max:255', Rule::unique('agencies')->ignore($agency->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_lifetime_free' => 'sometimes|boolean',
        ]);

        // Ensure the value is a boolean
        $validated['is_lifetime_free'] = $request->has('is_lifetime_free');

        $agency->update($validated);

        return redirect()->route('superadmin.agencies.index')
            ->with('success', "Agency '{$agency->name}' updated successfully.");
    }

    // END: NEW METHODS FOR SUPER ADMIN AGENCY MANAGEMENT
}