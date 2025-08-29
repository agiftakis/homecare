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
        // Fetch all clients and caregivers to populate dropdowns in the form
        $clients = Client::orderBy('first_name')->get();
        $caregivers = Caregiver::orderBy('first_name')->get();

        // Fetch all existing shifts to display on the calendar
        $shifts = Shift::with(['client', 'caregiver'])->get();

        return view('schedule.index', compact('clients', 'caregivers', 'shifts'));
    }

    /**
     * Store a newly created shift in storage.
     */
    public function store(Request $request)
    {
        // We will implement this logic in the next step
    }
}
