<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ SECURITY FIX: Import Auth facade
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * ✅ SECURITY FIX: This method is now role-aware.
     * It shows all shifts for an admin, but only assigned shifts for a caregiver.
     * ✅ ENHANCEMENT: Now eager-loads visit data to show clock-in/out times.
     */
    public function index()
    {
        $user = Auth::user();
        $clients = Client::orderBy('first_name')->get();
        $caregivers = Caregiver::orderBy('first_name')->get();
        $is_admin = ($user->role === 'agency_admin');

        $shiftsQuery = Shift::whereNotNull('client_id')
            ->whereNotNull('caregiver_id')
            ->with(['client', 'caregiver', 'visit']); // ✅ ENHANCEMENT: Added visit relationship

        // If the logged-in user is a caregiver, only show their shifts.
        if ($user->role === 'caregiver') {
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
            'title' => $shift->client->first_name . ' w/ ' . $shift->caregiver->first_name,
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
            'caregiver_id' => 'required|exists:caregivers,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shift->update($validator->validated());
        $shift->load(['client', 'caregiver', 'visit']); // ✅ ENHANCEMENT: Load visit data

        $eventData = [
            'id' => $shift->id,
            'title' => $shift->client->first_name . ' w/ ' . $shift->caregiver->first_name,
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