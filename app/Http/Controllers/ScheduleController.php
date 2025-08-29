<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('first_name')->get();
        $caregivers = Caregiver::orderBy('first_name')->get();
        
        $shifts = Shift::whereNotNull('client_id')
                       ->whereNotNull('caregiver_id')
                       ->with(['client', 'caregiver'])
                       ->get();

        return view('schedule.index', compact('clients', 'caregivers', 'shifts'));
    }

    public function store(Request $request)
    {
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
        $shift->load(['client', 'caregiver']);

        $eventData = [
            'id' => $shift->id,
            'title' => $shift->client->first_name . ' w/ ' . $shift->caregiver->first_name,
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    public function update(Request $request, Shift $shift)
    {
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
        $shift->load(['client', 'caregiver']);

        $eventData = [
            'id' => $shift->id,
            'title' => $shift->client->first_name . ' w/ ' . $shift->caregiver->first_name,
            'start' => $shift->start_time,
            'end' => $shift->end_time,
            'extendedProps' => [
                'client_id' => $shift->client_id,
                'caregiver_id' => $shift->caregiver_id,
                'notes' => $shift->notes,
            ]
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(['success' => true]);
    }
}
