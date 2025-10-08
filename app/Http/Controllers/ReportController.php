<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportingService;
use Carbon\Carbon;
use League\Csv\Writer;
use Illuminate\Support\Facades\Response;

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

    /**
     * Export operational report data to CSV.
     *
     * @param Request $request
     * @param ReportingService $reportingService
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportOperationalReport(Request $request, ReportingService $reportingService)
    {
        // --- Date Range Handling (same as dashboard) ---
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
        $metrics = $reportingService->getOperationalMetrics($startDate, $endDate);
        $agency = Auth::user()->agency;

        // --- CSV Generation using league/csv ---
        // Create a CSV writer that outputs to memory
        $csv = Writer::createFromString('');

        // Insert the header row
        $csv->insertOne([
            'Caregiver Name',
            'Total Hours Worked',
            'Total Visits Completed',
            'Average Hours per Visit'
        ]);

        // Insert each caregiver's performance data
        foreach ($metrics['caregiverPerformance'] as $performance) {
            $avgHours = $performance->total_visits > 0 
                ? round($performance->total_hours / $performance->total_visits, 2) 
                : 0;

            $csv->insertOne([
                $performance->caregiver_name,
                number_format($performance->total_hours, 2),
                $performance->total_visits,
                number_format($avgHours, 2)
            ]);
        }

        // Generate filename with agency name and date range
        $filename = sprintf(
            '%s_Operational_Report_%s_to_%s.csv',
            str_replace(' ', '_', $agency->name),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        // Return the CSV as a download response
        return Response::streamDownload(function() use ($csv) {
            echo $csv->toString();
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}