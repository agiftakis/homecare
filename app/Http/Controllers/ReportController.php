<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportingService;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the operational reports dashboard.
     *
     * @param Request $request
     * @param ReportingService $reportingService
     * @return \Illuminate\View\View
     */
    public function operationalDashboard(Request $request, ReportingService $reportingService)
    {
        // --- Date Range Handling ---
        // Set default date range to the last 30 days.
        $defaultEndDate = Carbon::now();
        $defaultStartDate = Carbon::now()->subDays(29);

        // Validate and parse user-provided start and end dates.
        // If parsing fails (e.g., invalid format), it gracefully falls back to the default range.
        try {
            $startDate = $request->has('start_date') && !empty($request->input('start_date'))
                ? Carbon::parse($request->input('start_date'))
                : $defaultStartDate;
            
            $endDate = $request->has('end_date') && !empty($request->input('end_date'))
                ? Carbon::parse($request->input('end_date'))
                : $defaultEndDate;
        } catch (\Exception $e) {
            // If date parsing fails for any reason, revert to the safe defaults.
            $startDate = $defaultStartDate;
            $endDate = $defaultEndDate;
        }

        // --- Data Fetching ---
        // Use the ReportingService (injected by Laravel) to get our metrics.
        $metrics = $reportingService->getOperationalMetrics($startDate, $endDate);

        $agency = Auth::user()->agency;

        // Pass all necessary data to the view.
        return view('reports.operational.dashboard', [
            'agency' => $agency,
            'metrics' => $metrics,
            'startDate' => $startDate->toDateString(), // Format as 'YYYY-MM-DD' for input fields
            'endDate' => $endDate->toDateString(),
        ]);
    }

    /**
     * Display the simplified revenue dashboard.
     *
     * @param Request $request
     * @param ReportingService $reportingService
     * @return \Illuminate\View\View
     */
    public function revenueDashboard(Request $request, ReportingService $reportingService)
    {
        // --- Date Range Handling ---
        $defaultEndDate = Carbon::now();
        $defaultStartDate = Carbon::now()->subDays(29);

        try {
            $startDate = $request->has('start_date') && !empty($request->input('start_date'))
                ? Carbon::parse($request->input('start_date'))
                : $defaultStartDate;
            
            $endDate = $request->has('end_date') && !empty($request->input('end_date'))
                ? Carbon::parse($request->input('end_date'))
                : $defaultEndDate;
        } catch (\Exception $e) {
            $startDate = $defaultStartDate;
            $endDate = $defaultEndDate;
        }

        // --- Data Fetching ---
        $metrics = $reportingService->getRevenueMetrics($startDate, $endDate);

        $agency = Auth::user()->agency;

        // Pass all necessary data to the view.
        return view('reports.revenue.dashboard', [
            'agency' => $agency,
            'metrics' => $metrics,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }
}