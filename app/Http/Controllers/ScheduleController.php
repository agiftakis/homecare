<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Caregiver;
use App\Models\Shift;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string',
        ]);

        Shift::create($validated);

        return response()->json(['success' => true]);
    }
}