<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use App\Services\FirebaseStorageService;
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

    /**
     * ✅ ORPHANED SHIFTS FIX: This method now shows orphaned shifts to admins.
     * ✅ SECURITY FIX: This method is now role-aware.
     * It shows all shifts for an admin, but only assigned shifts for a caregiver.
     * ✅ ENHANCEMENT: Now eager-loads visit data to show clock-in/out times.
     * ✅ ENHANCEMENT: Now includes signature URLs for admins.
     */
    public function index()
    {
        $user = Auth::user();
        $clients = Client::orderBy('first_name')->get();
        $caregivers = Caregiver::orderBy('first_name')->get();
        $is_admin = ($user->role === 'agency_admin');

        // ✅ ORPHANED SHIFTS FIX: Different query logic based on user role
        if ($user->role === 'agency_admin') {
            // For admins: Show ALL shifts including orphaned ones (where caregiver was deleted)
            $shiftsQuery = Shift::whereNotNull('client_id')
                ->with(['client', 'caregiver', 'visit']); // Don't filter by caregiver_id for admins
        } else {
            // For caregivers: Only show their assigned shifts (must have valid caregiver_id)
            $shiftsQuery = Shift::whereNotNull('client_id')
                ->whereNotNull('caregiver_id')
                ->with(['client', 'caregiver', 'visit']);
                
            // Find the caregiver record associated with the logged-in user
            $caregiverProfile = $user->caregiver;
            if ($caregiverProfile) {
                $shiftsQuery->where('caregiver_id', $caregiverProfile->id);
            } else {
                // If for some reason the user has no caregiver profile, show no shifts.
                $shiftsQuery->where('caregiver_id', -1); // Failsafe
            }
        }
        
        $shifts = $shiftsQuery->get();

        // ✅ ENHANCEMENT: Add signature URLs for admin users
        if ($is_admin) {
            $shifts->each(function ($shift) {
                if ($shift->visit) {
                    // Add signature URLs to the visit data
                    $shift->visit->clock_in_signature_url = $shift->visit->signature_path 
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->signature_path) 
                        : null;
                    
                    $shift->visit->clock_out_signature_url = $shift->visit->clock_out_signature_path 
                        ? $this->firebaseStorageService->getPublicUrl($shift->visit->clock_out_signature_path) 
                        : null;
                }
            });
        }

        // Pass the new is_admin flag to the view.
        return view('schedule.index', compact('clients', 'caregivers', 'shifts', 'is_admin'));
    }

    /**
     * ✅ SECURITY FIX: Only agency admins can create new shifts.
     * ✅ ENHANCEMENT: Now includes visit data in response.
     */
    public function store(Request $request)
    {
        // Authorization check: Block anyone who is not an agency admin.
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shift = Shift::create($validator->validated());
        $shift->load(['client', 'caregiver', 'visit']); // ✅ ENHANCEMENT: Load visit data

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
                'visit' => $shift->visit ? [ // ✅ ENHANCEMENT: Include visit data
                    'clock_in_time' => $shift->visit->clock_in_time,
                    'clock_out_time' => $shift->visit->clock_out_time,
                ] : null
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    /**
     * ✅ ORPHANED SHIFTS FIX: Allow updating shifts even if caregiver was deleted.
     * ✅ SECURITY FIX: Only agency admins can update shifts.
     * ✅ ENHANCEMENT: Now includes visit data in response.
     */
    public function update(Request $request, Shift $shift)
    {
        // Authorization check: Block anyone who is not an agency admin.
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id', // ✅ ORPHANED SHIFTS FIX: Allow null caregiver for reassignment
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shift->update($validator->validated());
        $shift->load(['client', 'caregiver', 'visit']); // ✅ ENHANCEMENT: Load visit data

        // ✅ ENHANCEMENT: Add signature URLs if visit exists
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
                'visit' => $shift->visit ? [ // ✅ ENHANCEMENT: Include visit data with signature URLs
                    'clock_in_time' => $shift->visit->clock_in_time,
                    'clock_out_time' => $shift->visit->clock_out_time,
                    'clock_in_signature_url' => $shift->visit->clock_in_signature_url,
                    'clock_out_signature_url' => $shift->visit->clock_out_signature_url,
                ] : null
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    /**
     * ✅ SECURITY FIX: Only agency admins can delete shifts.
     */
    public function destroy(Shift $shift)
    {
        // Authorization check: Block anyone who is not an agency admin.
        if (Auth::user()->role !== 'agency_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $shift->delete();
        return response()->json(['success' => true]);
    }
}