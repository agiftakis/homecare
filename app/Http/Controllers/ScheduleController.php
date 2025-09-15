<?php

namespace App\Http\Controllers;

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

        // Redirect clients to their dedicated schedule view
        if ($user->role === 'client') {
            return redirect()->route('schedule.client');
        }
        
        // ✅ CRITICAL FIX: Only show active (non-deleted) clients and caregivers in dropdowns
        $clients = Client::whereNull('deleted_at')->orderBy('first_name')->get();

        $caregivers = Caregiver::whereNull('deleted_at')->orderBy('first_name')->get();

        $is_admin = ($user->role === 'agency_admin');

        if ($user->role === 'agency_admin') {
            // ✅ ENHANCED: Load shifts with special handling for deleted clients
            $shiftsQuery = Shift::with([
                'client' => function ($query) {
                    $query->withTrashed(); // Include deleted clients
                },
                'visit',
                'caregiver' => function ($query) {
                    $query->withTrashed();
                }
            ]);
        } else { // This else now implicitly handles the 'caregiver' role
            // ✅ ENHANCED: Caregiver view with deleted client handling
            $shiftsQuery = Shift::with([
                'client' => function ($query) {
                    $query->withTrashed(); // Include deleted clients for historical accuracy
                },
                'visit',
                'caregiver'
            ]);

            $caregiverProfile = $user->caregiver;
            if ($caregiverProfile) {
                $shiftsQuery->where('caregiver_id', $caregiverProfile->id);
            } else {
                $shiftsQuery->where('caregiver_id', -1);
            }
        }

        $shifts = $shiftsQuery->get();

        // ✅ NEW: Filter shifts based on client deletion logic
        $filteredShifts = $shifts->filter(function ($shift) use ($is_admin) {
            // If client exists and is not deleted, always show
            if ($shift->client && !$shift->client->deleted_at) {
                return true;
            }

            // If client is deleted, apply the enhanced logic
            if ($shift->client && $shift->client->deleted_at) {
                $clientDeletionDate = Carbon::parse($shift->client->deleted_at);
                $shiftDate = Carbon::parse($shift->start_time);

                // For admin view: Show all shifts but mark them appropriately
                if ($is_admin) {
                    return true;
                }

                // For caregiver view: Only show past shifts, hide future ones
                if (!$is_admin) {
                    // Show shifts that occurred before or on the deletion date
                    return $shiftDate->lte($clientDeletionDate);
                }
            }

            // If no client at all (shouldn't happen but safety check)
            return false;
        });

        // ✅ ENHANCED: Add client deletion status to each shift
        $enhancedShifts = $filteredShifts->map(function ($shift) {
            if ($shift->client && $shift->client->deleted_at) {
                $clientDeletionDate = Carbon::parse($shift->client->deleted_at);
                $shiftDate = Carbon::parse($shift->start_time);

                // Determine if this is a past or future shift relative to deletion
                $shift->client_deletion_status = [
                    'is_deleted' => true,
                    'deletion_date' => $clientDeletionDate,
                    'is_past_shift' => $shiftDate->lt($clientDeletionDate),
                    'is_future_shift' => $shiftDate->gt($clientDeletionDate),
                    'formatted_deletion_date' => $clientDeletionDate->format('M j, Y @ g:i A')
                ];
            } else {
                $shift->client_deletion_status = [
                    'is_deleted' => false
                ];
            }

            return $shift;
        });

        if ($is_admin) {
            $enhancedShifts->each(function ($shift) {
                if ($shift->visit) {
                    $shift->visit->clock_in_signature_url = $shift->visit->signature_path
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path)
                        : null;

                    $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path)
                        : null;
                }
            });
        }

        return view('schedule.index', compact('clients', 'caregivers', 'shifts', 'is_admin'))->with('shifts', $enhancedShifts->values());
    }

    /**
     * ✅ NEW: Display a read-only schedule for the authenticated client.
     */
    public function clientSchedule()
    {
        $user = Auth::user();
        $clientProfile = $user->client;

        if (!$clientProfile) {
            // Or handle this scenario appropriately, maybe redirect with an error.
            return view('schedule.client-schedule', ['shifts' => collect()]);
        }
        
        // Fetch shifts for the specific client
        // CRITICAL: Include withTrashed() for caregivers to show historical data correctly
        $shifts = Shift::where('client_id', $clientProfile->id)
            ->with([
                'caregiver' => function ($query) {
                    $query->withTrashed();
                },
                'visit'
            ])
            ->get();
            
        return view('schedule.client-schedule', compact('shifts'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        // ✅ FIX: Added server-side validation to prevent creating shifts in the past.
        // 'today' is timezone-aware based on the application's config.
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

        $shift = Shift::create($validator->validated());
        $shift->load(['client', 'caregiver', 'visit']);

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
        if (Auth::user()->role !== 'agency_admin') {
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

        if ($shift->visit) {
            $shift->visit->clock_in_signature_url = $shift->visit->signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path)
                : null;

            $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path)
                : null;
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
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $shift->delete();
        return response()->json(['success' => true]);
    }
}