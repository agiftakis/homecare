<?php

namespace App\Http\Controllers;

use App\Events\ShiftUpdated;
use App\Models\Agency;
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;

        if ($userRole === 'client') {
            return redirect()->route('schedule.client');
        }

        $is_admin = in_array($userRole, ['agency_admin', 'super_admin']);

        // ✅ SUPER ADMIN UPDATE: Prepare variables for agency filtering
        $agencies = collect();
        $agencyFilterId = $request->query('agency');

        $clientQuery = Client::query();
        $caregiverQuery = Caregiver::query();
        $shiftsQuery = Shift::query();

        if ($userRole === 'super_admin') {
            // Super admins get a list of all agencies for the filter dropdown
            $agencies = Agency::orderBy('name')->get();

            $clientQuery->withoutGlobalScope('agencyScope');
            $caregiverQuery->withoutGlobalScope('agencyScope');
            $shiftsQuery->withoutGlobalScope('agencyScope');

            // ✅ SUPER ADMIN UPDATE: Apply the agency filter if one is selected
            if ($agencyFilterId) {
                $clientQuery->where('agency_id', $agencyFilterId);
                $caregiverQuery->where('agency_id', $agencyFilterId);
                $shiftsQuery->where('agency_id', $agencyFilterId);
            }
        } elseif ($userRole === 'caregiver') {
            $caregiverProfile = $user->caregiver;
            if ($caregiverProfile) {
                $shiftsQuery->where('caregiver_id', $caregiverProfile->id);
            } else {
                $shiftsQuery->where('caregiver_id', -1);
            }
        }

        $clients = $clientQuery->whereNull('deleted_at')->orderBy('first_name')->get();
        $caregivers = $caregiverQuery->whereNull('deleted_at')->orderBy('first_name')->get();

        $shiftsQuery->with([
            'client' => fn($query) => $query->withTrashed(),
            'visit',
            'caregiver' => fn($query) => $query->withTrashed(),
            'agency'
        ]);

        $shifts = $shiftsQuery->get();

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

        // ✅ SUPER ADMIN UPDATE: Pass the new agency variables to the view
        return view('schedule.index', compact('clients', 'caregivers', 'is_admin', 'agencies', 'agencyFilterId'))
            ->with('shifts', $enhancedShifts->values());
    }

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

        $client = Client::withoutGlobalScope('agencyScope')->find($validated['client_id']);
        $validated['agency_id'] = $client->agency_id;

        $shift = Shift::create($validated);
        $shift->load(['client', 'caregiver', 'visit', 'agency']);

        ShiftUpdated::dispatch($shift);

        // 🔧 BUG FIX: Handle null client/caregiver safely
        $clientName = $shift->client ? ($shift->client->first_name ?? 'Unknown') : 'N/A';
        $caregiverName = $shift->caregiver ? ($shift->caregiver->first_name ?? 'Unknown') : 'Unassigned';

        $eventData = [
            'id' => $shift->id,
            'title' => $clientName . ' w/ ' . $caregiverName,
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
                'status' => $shift->status,
                'agency_name' => $shift->agency?->name,
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
        $shift->load(['client', 'caregiver', 'visit', 'agency']);

        ShiftUpdated::dispatch($shift);

        if ($shift->visit) {
            $shift->visit->clock_in_signature_url = $shift->visit->signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path) : null;
            $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path
                ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path) : null;
        }

        // 🔧 BUG FIX: Handle null client/caregiver safely
        $clientName = $shift->client ? ($shift->client->first_name ?? 'Unknown') : 'N/A';
        $caregiverName = $shift->caregiver ? ($shift->caregiver->first_name ?? 'Unknown') : 'Unassigned';

        $eventData = [
            'id' => $shift->id,
            'title' => $clientName . ' w/ ' . $caregiverName,
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
                'status' => $shift->status,
                'agency_name' => $shift->agency?->name,
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
        if (!in_array(Auth::user()->role, ['agency_admin', 'super_admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $shift->delete();

        return response()->json(['success' => true]);
    }
}
