<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display the scheduling calendar.
     */
    public function index()
    {
        $clients = Client::orderBy('first_name')->get();
        $caregivers = Caregiver::orderBy('first_name')->get();
        $shifts = Shift::with(['client', 'caregiver'])->get();

        return view('schedule.index', compact('clients', 'caregivers', 'shifts'));
    }

    /**
     * Store a newly created shift in storage.
     */
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
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $shift = Shift::create($validator->validated());
        $shift->load(['client', 'caregiver']); // Eager load relationships

        // Prepare data for FullCalendar
        $eventData = [
            'title' => $shift->client->first_name . ' w/ ' . $shift->caregiver->first_name,
            'start' => $shift->start_time,
            'end' => $shift->end_time,
        ];

        return response()->json(['success' => true, 'shift' => $eventData]);
    }
}
