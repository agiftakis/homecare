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

            // ✅ ENHANCED: Load clients with soft-deleted ones and filter based on deletion logic
            $allUpcomingShifts = Shift::with([
                    'client' => function ($query) {
                        $query->withTrashed(); // Include deleted clients for proper filtering
                    }
                ])
                ->where('caregiver_id', $caregiver->id)
                ->whereDate('start_time', '>=', Carbon::today())
                ->where('status', '!=', 'Completed')
                ->orderBy('start_time', 'asc')
                ->get();

            // ✅ ENHANCED: Filter out future shifts with deleted clients
            $upcoming_shifts = $allUpcomingShifts->filter(function ($shift) {
                // If client exists and is not deleted, always show
                if ($shift->client && !$shift->client->deleted_at) {
                    return true;
                }
                
                // If client is deleted, apply the enhanced logic
                if ($shift->client && $shift->client->deleted_at) {
                    $clientDeletionDate = Carbon::parse($shift->client->deleted_at);
                    $shiftDate = Carbon::parse($shift->start_time);
                    
                    // Only show shifts that occurred before or on the deletion date
                    return $shiftDate->lte($clientDeletionDate);
                }
                
                // If no client at all (shouldn't happen but safety check)
                return false;
            });

            // ✅ ENHANCED: Load past shifts with deleted clients for historical accuracy
            $all_past_shifts = Shift::with([
                    'client' => function ($query) {
                        $query->withTrashed(); // Include deleted clients for historical accuracy
                    }
                ])
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

        // ✅ ENHANCED: Only count active (non-deleted) clients and caregivers
        $clientCount = Client::where('agency_id', $user->agency_id)
                           ->whereNull('deleted_at')
                           ->count();
        
        $caregiverCount = Caregiver::where('agency_id', $user->agency_id)
                                 ->whereNull('deleted_at')
                                 ->count();

        // ✅ ENHANCED: Load today's shifts with both deleted clients and caregivers for proper display
        $todaysShifts = Shift::with([
                'client' => function ($query) {
                    $query->withTrashed(); // Include deleted clients
                },
                'caregiver' => function ($query) {
                    $query->withTrashed(); // Include deleted caregivers
                }
            ])
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