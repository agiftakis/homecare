<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Fetch key operational metrics for the authenticated user's agency within a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getOperationalMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $agencyId = Auth::user()->agency_id;

        // --- 1. Calculate Total Hours Worked ---
        // We find all completed visits within the date range and sum the duration of each visit.
        $totalSecondsWorked = Visit::query()
            ->where('agency_id', $agencyId)
            ->whereNotNull('clock_out_time') // Ensure the visit is completed
            ->whereBetween('clock_in_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum(DB::raw('TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)'));

        // Convert the total seconds to hours and round to two decimal places.
        $totalHoursWorked = round($totalSecondsWorked / 3600, 2);

        // We will add more metrics to this array in later steps.
        return [
            'total_hours_worked' => $totalHoursWorked,
        ];
    }
}