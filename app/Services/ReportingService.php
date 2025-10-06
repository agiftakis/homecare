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


        // --- 2. Calculate Caregiver Performance ---
        // This query joins visits with shifts and caregivers to aggregate data per caregiver.
        $caregiverPerformance = Visit::query()
            ->join('shifts', 'visits.shift_id', '=', 'shifts.id')
            ->join('caregivers', 'shifts.caregiver_id', '=', 'caregivers.id')
            ->where('visits.agency_id', $agencyId)
            ->whereNotNull('visits.clock_out_time')
            ->whereBetween('visits.clock_in_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select(
                'caregivers.id as caregiver_id',
                'caregivers.first_name',
                'caregivers.last_name',
                DB::raw('COUNT(visits.id) as total_visits'),
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, visits.clock_in_time, visits.clock_out_time)) as total_seconds')
            )
            ->groupBy('caregivers.id', 'caregivers.first_name', 'caregivers.last_name')
            ->orderByDesc('total_seconds')
            ->get()
            ->map(function ($row) {
                // Add a calculated 'total_hours' field to each result for easy display.
                $row->total_hours = round($row->total_seconds / 3600, 2);
                return $row;
            });

        return [
            'total_hours_worked' => $totalHoursWorked,
            'caregiver_performance' => $caregiverPerformance, // Add the new data to the return array
        ];
    }
}