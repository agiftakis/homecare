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

        // 2. Client gets a personalized dashboard with their care information.
        if ($user->role === 'client') {
            $client = $user->client;

            if (!$client) {
                abort(403, 'Your client profile is not accessible.');
            }

            // Load upcoming shifts for this client
            $upcomingShifts = Shift::with([
                    'caregiver' => function ($query) {
                        $query->withTrashed(); // Include deleted caregivers for historical accuracy
                    }
                ])
                ->where('client_id', $client->id)
                ->whereDate('start_time', '>=', Carbon::today())
                ->where('status', '!=', 'completed')
                ->orderBy('start_time', 'asc')
                ->limit(5) // Show next 5 appointments
                ->get();

            // Load recent completed shifts
            $recentShifts = Shift::with([
                    'caregiver' => function ($query) {
                        $query->withTrashed();
                    }
                ])
                ->where('client_id', $client->id)
                ->where('status', 'completed')
                ->orderBy('start_time', 'desc')
                ->limit(3) // Show last 3 completed visits
                ->get();

            return view('dashboard', [
                'client' => $client,
                'upcomingShifts' => $upcomingShifts,
                'recentShifts' => $recentShifts,
            ]);
        }

        // 3. Caregiver gets a specialized view with their own shifts.
        if ($user->role === 'caregiver') {
            $caregiver = $user->caregiver;

            if (!$caregiver) {
                abort(403, 'Your caregiver profile is not accessible.');
            }

            // ✅ FIXED: Changed 'Completed' to 'completed' (lowercase)
            // Load clients with soft-deleted ones and filter based on deletion logic
            $allUpcomingShifts = Shift::with([
                    'client' => function ($query) {
                        $query->withTrashed(); // Include deleted clients for proper filtering
                    }
                ])
                ->where('caregiver_id', $caregiver->id)
                ->whereDate('start_time', '>=', Carbon::today())
                ->where('status', '!=', 'completed') // ✅ FIXED: lowercase
                ->orderBy('start_time', 'asc')
                ->get();

            // Filter out future shifts with deleted clients
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

            // ✅ FIXED: Changed 'Completed' to 'completed' (lowercase)
            // Load past shifts with deleted clients for historical accuracy
            $all_past_shifts = Shift::with([
                    'client' => function ($query) {
                        $query->withTrashed(); // Include deleted clients for historical accuracy
                    }
                ])
                ->where('caregiver_id', $caregiver->id)
                ->where('status', 'completed') // ✅ FIXED: lowercase
                ->orderBy('start_time', 'desc')
                ->get();

            return view('dashboard', [
                'upcoming_shifts' => $upcoming_shifts,
                'all_past_shifts' => $all_past_shifts,
            ]);
        }

        // 4. Agency Admin gets the agency overview.
        $agency = $user->agency;

        if (!$agency) {
            abort(403, 'You are not associated with an agency.');
        }

        // Only count active (non-deleted) clients and caregivers
        $clientCount = Client::where('agency_id', $user->agency_id)
                           ->whereNull('deleted_at')
                           ->count();
        
        $caregiverCount = Caregiver::where('agency_id', $user->agency_id)
                                 ->whereNull('deleted_at')
                                 ->count();

        // Load today's shifts with both deleted clients and caregivers for proper display
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