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
     * Display the correct dashboard based on the user's role.
     */
    public function index()
    {
        $user = Auth::user();

        // Check the user's role.
        if ($user->role === 'super_admin') {
            // If they're a super admin, redirect them to their own dashboard.
            return redirect()->route('superadmin.dashboard');
        }

        // If not a super admin, continue with the original agency dashboard logic.
        $agency = $user->agency;

        // Safety check in case a regular user isn't assigned to an agency.
        if (!$agency) {
            abort(403, 'You are not associated with an agency.');
        }

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