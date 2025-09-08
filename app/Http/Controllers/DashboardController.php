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

            if (!$caregiver) {
                abort(403, 'Your caregiver profile is not accessible.');
            }

            // ✅ FIX: Changed where('date', ...) to whereDate('start_time', ...) to match the database schema.
            $upcoming_shifts = Shift::with('client')
                ->where('caregiver_id', $caregiver->id)
                ->whereDate('start_time', '>=', Carbon::today())
                ->where('status', '!=', 'Completed')
                ->orderBy('start_time', 'asc')
                ->get();

            // ✅ FIX: Changed where('date', ...) to whereDate('start_time', ...) to match the database schema.
            $all_past_shifts = Shift::with('client')
                ->where('caregiver_id', $caregiver->id)
                ->where('status', 'Completed')
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

        $clientCount = Client::where('agency_id', $user->agency_id)->count();
        $caregiverCount = Caregiver::where('agency_id', $user->agency_id)->count();

        // ✅ FIX: Changed where('date', ...) to whereDate('start_time', ...) to match the database schema.
        $todaysShifts = Shift::with(['client', 'caregiver'])
            ->where('agency_id', $user->agency_id)
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

