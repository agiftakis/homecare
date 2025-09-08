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

        // 1. Super Admin gets redirected to their own dashboard.
        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }

        // 2. Caregiver gets a specialized view with their own shifts.
        if ($user->role === 'caregiver') {
            $caregiver = $user->caregiver;

            // Safety check for caregiver profile
            if (!$caregiver) {
                abort(403, 'Your caregiver profile is not accessible.');
            }

            // Get all upcoming shifts (today or in the future that are not completed)
            $upcoming_shifts = Shift::with('client')
                ->where('caregiver_id', $caregiver->id)
                ->where('date', '>=', Carbon::today()->toDateString())
                ->where('status', '!=', 'Completed')
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            // Get ALL previously completed shifts for their history log
            $all_past_shifts = Shift::with('client')
                ->where('caregiver_id', $caregiver->id)
                ->where('status', 'Completed')
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();


            return view('dashboard', [
                'upcoming_shifts' => $upcoming_shifts,
                'all_past_shifts' => $all_past_shifts,
            ]);
        }

        // 3. Agency Admin gets the agency overview.
        $agency = $user->agency;

        if (!$agency) {
            abort(403, 'You are not associated with an agency.');
        }

        // CORRECTED: Counts are now scoped to the logged-in user's agency.
        $clientCount = Client::where('agency_id', $user->agency_id)->count();
        $caregiverCount = Caregiver::where('agency_id', $user->agency_id)->count();

        // Shifts are now correctly scoped to the agency and query the 'date' column.
        $todaysShifts = Shift::with(['client', 'caregiver'])
            ->where('agency_id', $user->agency_id)
            ->where('date', Carbon::today()->toDateString())
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
