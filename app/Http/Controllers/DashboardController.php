<?php

namespace App\Http\Controllers;

use App\Models\Caregiver;
use App\Models\Client;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the agency's dashboard.
     */
    public function index()
    {
        $agency = Auth::user()->agency;

        // Thanks to your BelongsToAgency scope, these queries are automatically
        // secure and scoped to the currently logged-in agency.
        $clientCount = Client::count();
        $caregiverCount = Caregiver::count();
        
        $todaysShifts = Shift::with(['client', 'caregiver'])
            ->whereDate('start_time', Carbon::today())
            ->orderBy('start_time')
            ->get();

        return view('dashboard', [
            'agency' => $agency,
            'clientCount' => $clientCount,
            'caregiverCount' => $caregiverCount,
            'todaysShifts' => $todaysShifts,
        ]);
    }
}