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
        $clients = Client::orderBy('first_name')->get();
        
        $caregivers = Caregiver::orderBy('first_name')->get();
        
        $is_admin = ($user->role === 'agency_admin');

        if ($user->role === 'agency_admin') {
            $shiftsQuery = Shift::with([
                'client',
                'visit',
                'caregiver' => function ($query) {
                    $query->withTrashed();
                }
            ]);
        } else {
            $shiftsQuery = Shift::with(['client', 'visit', 'caregiver']);
            $caregiverProfile = $user->caregiver;
            if ($caregiverProfile) {
                $shiftsQuery->where('caregiver_id', $caregiverProfile->id);
            } else {
                $shiftsQuery->where('caregiver_id', -1);
            }
        }

        $shifts = $shiftsQuery->get();

        if ($is_admin) {
            $shifts->each(function ($shift) {
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

        return view('schedule.index', compact('clients', 'caregivers', 'shifts', 'is_admin'));
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
