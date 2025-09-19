<?php

namespace App\Http\Controllers;

use App\Events\ShiftUpdated;
use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use App\Services\FirebaseStorageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    protected $firebaseStorageService;

    public function __construct(FirebaseStorageService $firebaseStorageService)
    {
        $this->firebaseStorageService = $firebaseStorageService;
    }

    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role;

        // Redirect clients to their dedicated schedule view
        if ($userRole === 'client') {
            return redirect()->route('schedule.client');
        }

        // ✅ SUPER ADMIN UPDATE: Determine if the user is any kind of admin
        $is_admin = in_array($userRole, ['agency_admin', 'super_admin']);

        // Base queries for dropdowns
        $clientQuery = Client::query();
        $caregiverQuery = Caregiver::query();
        $shiftsQuery = Shift::query();

        // ✅ SUPER ADMIN UPDATE: Modify queries based on user role
        if ($userRole === 'super_admin') {
            // Super admins see all active clients/caregivers from all agencies for the dropdowns
            $clientQuery->withoutGlobalScope('agencyScope');
            $caregiverQuery->withoutGlobalScope('agencyScope');
            // Super admins see all shifts from all agencies
            $shiftsQuery->withoutGlobalScope('agencyScope');
        } elseif ($userRole === 'caregiver') {
            // Caregivers only see shifts assigned to them
            $caregiverProfile = $user->caregiver;
            if ($caregiverProfile) {
                $shiftsQuery->where('caregiver_id', $caregiverProfile->id);
            } else {
                $shiftsQuery->where('caregiver_id', -1); // No shifts if no profile
            }
        }
        // For agency_admin, the default global scope applies to all queries correctly.

        $clients = $clientQuery->whereNull('deleted_at')->orderBy('first_name')->get();
        $caregivers = $caregiverQuery->whereNull('deleted_at')->orderBy('first_name')->get();

        // Eager load relationships with soft-deleted models for historical accuracy
        $shiftsQuery->with([
            'client' => fn($query) => $query->withTrashed(),
            'visit',
            'caregiver' => fn($query) => $query->withTrashed(),
            'agency' // ✅ SUPER ADMIN UPDATE: Eager load agency for display
        ]);

        $shifts = $shiftsQuery->get();

        // Filter shifts based on client deletion logic (this logic is the same for both admin types)
        $filteredShifts = $shifts->filter(function ($shift) use ($is_admin) {
            if ($shift->client && !$shift->client->deleted_at) {
                return true;
            }
            if ($shift->client && $shift->client->deleted_at) {
                $clientDeletionDate = Carbon::parse($shift->client->deleted_at);
                $shiftDate = Carbon::parse($shift->start_time);
                if ($is_admin) {
                    return true;
                }
                return $shiftDate->lte($clientDeletionDate);
            }
            return false;
        });

        // Add client deletion status to each shift
        $enhancedShifts = $filteredShifts->map(function ($shift) {
            if ($shift->client && $shift->client->deleted_at) {
                $clientDeletionDate = Carbon::parse($shift->client->deleted_at);
                $shiftDate = Carbon::parse($shift->start_time);
                $shift->client_deletion_status = [
                    'is_deleted' => true,
                    'deletion_date' => $clientDeletionDate,
                    'is_past_shift' => $shiftDate->lt($clientDeletionDate),
                    'is_future_shift' => $shiftDate->gt($clientDeletionDate),
                    'formatted_deletion_date' => $clientDeletionDate->format('M j, Y @ g:i A')
                ];
            } else {
                $shift->client_deletion_status = ['is_deleted' => false];
            }
            return $shift;
        });

        // Fetch signature URLs for admins
        if ($is_admin) {
            $enhancedShifts->each(function ($shift) {
                if ($shift->visit) {
                    $shift->visit->clock_in_signature_url = $shift->visit->signature_path
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path) : null;
                    $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path) : null;
                }
            });
        }

        return view('schedule.index', compact('clients', 'caregivers', 'is_admin'))->with('shifts', $enhancedShifts->values());
    }

    /**
     * Display a read-only schedule for the authenticated client.
     */
    public function clientSchedule()
    {
        $user = Auth::user();
        $clientProfile = $user->client;

        if (!$clientProfile) {
            return view('schedule.client-schedule', ['shifts' => collect()]);
        }

        $shifts = Shift::where('client_id', $clientProfile->id)
            ->with([
                'caregiver' => fn($query) => $query->withTrashed(),
                'visit'
            ])
            ->get();

        return view('schedule.client-schedule', compact('shifts'));
    }

    public function store(Request $request)
    {
        // ✅ SUPER ADMIN UPDATE: Allow both admin types to create shifts
        if (!in_array(Auth::user()->role, ['agency_admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'start_time' => 'required|date|after_or_equal:today',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // ✅ SUPER ADMIN UPDATE: Determine agency_id from the selected client
        // This ensures the shift is correctly associated with an agency.
        $client = Client::find($validated['client_id']);
        $validated['agency_id'] = $client->agency_id;

        $shift = Shift::create($validated);
        $shift->load(['client', 'caregiver', 'visit']);

        ShiftUpdated::dispatch($shift);

        $eventData = [
            'id' => $shift->id,
            'title' => $shift->client->first_name . ' w/ ' . ($shift->caregiver ? $shift->caregiver->first_name : 'N/A'),
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
                'status' => $shift->status,
                'visit' => $shift->visit ? [
                    'clock_in_time' => $shift->visit->clock_in_time,
                    'clock_out_time' => $shift->visit->clock_out_time,
                ] : null
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    public function update(Request $request, Shift $shift)
    {
        // ✅ SUPER ADMIN UPDATE: Allow both admin types to update shifts
        if (!in_array(Auth::user()->role, ['agency_admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shift->update($validator->validated());
        $shift->load(['client', 'caregiver', 'visit']);

        ShiftUpdated::dispatch($shift);

        if ($shift->visit) {
            $shift->visit->clock_in_signature_url = $shift->visit->signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path) : null;
            $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path) : null;
        }

        $eventData = [
            'id' => $shift->id,
            'title' => $shift->client->first_name . ' w/ ' . ($shift->caregiver ? $shift->caregiver->first_name : 'N/A'),
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
                'status' => $shift->status,
                'visit' => $shift->visit ? [
                    'clock_in_time' => $shift->visit->clock_in_time,
                    'clock_out_time' => $shift->visit->clock_out_time,
                    'clock_in_signature_url' => $shift->visit->clock_in_signature_url,
                    'clock_out_signature_url' => $shift->visit->clock_out_signature_url,
                ] : null
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    public function destroy(Shift $shift)
    {
        // ✅ SUPER ADMIN UPDATE: Allow both admin types to delete shifts
        if (!in_array(Auth::user()->role, ['agency_admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $shift->delete();

        return response()->json(['success' => true]);
    }
}
