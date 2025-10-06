<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\Invoice;
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
        $totalSecondsWorked = Visit::query()
            ->where('agency_id', $agencyId)
            ->whereNotNull('clock_out_time')
            ->whereBetween('clock_in_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum(DB::raw('TIMESTAMPDIFF(SECOND, clock_in_time, clock_out_time)'));

        $totalHoursWorked = round($totalSecondsWorked / 3600, 2);


        // --- 2. Calculate Caregiver Performance ---
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
                $row->total_hours = round($row->total_seconds / 3600, 2);
                return $row;
            });

        return [
            'total_hours_worked' => $totalHoursWorked,
            'caregiver_performance' => $caregiverPerformance,
        ];
    }

    /**
     * Fetch key revenue metrics for the authenticated user's agency within a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getRevenueMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $agencyId = Auth::user()->agency_id;

        // --- 1. Calculate Total Revenue (Paid Invoices in Range) ---
        $totalRevenue = Invoice::query()
            ->where('agency_id', $agencyId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('total_amount');

        // --- 2. Calculate Outstanding Balance (Sent, Unpaid Invoices in Range) ---
        $outstandingBalance = Invoice::query()
            ->where('agency_id', $agencyId)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('total_amount');

        // --- 3. Calculate Overdue Balance (Sent, Unpaid, and Past Due Date Invoices in Range) ---
        $overdueBalance = Invoice::query()
            ->where('agency_id', $agencyId)
            ->where('status', 'sent')
            ->where('due_date', '<', now()) // Check if the due date is in the past
            ->whereBetween('sent_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('total_amount');

        return [
            'total_revenue' => number_format($totalRevenue, 2),
            'outstanding_balance' => number_format($outstandingBalance, 2),
            'overdue_balance' => number_format($overdueBalance, 2),
        ];
    }
}