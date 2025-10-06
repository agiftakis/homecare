<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display the operational reports dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function operationalDashboard(Request $request)
    {
        // We will add the logic to fetch operational data here in a future step.
        $agency = Auth::user()->agency;

        return view('reports.operational.dashboard', [
            'agency' => $agency
        ]);
    }

    /**
     * Display the simplified revenue dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function revenueDashboard(Request $request)
    {
        // We will add the logic to fetch revenue data here in a future step.
        $agency = Auth::user()->agency;

        return view('reports.revenue.dashboard', [
            'agency' => $agency
        ]);
    }
}