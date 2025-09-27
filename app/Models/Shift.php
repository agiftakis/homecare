<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToAgency;

class Shift extends Model
{
    use HasFactory, BelongsToAgency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'caregiver_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'hourly_rate',      // Added for billing
        'service_type',     // Added for billing
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hourly_rate' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    /**
     * Get the visit record for this shift
     */
    public function visit()
    {
        return $this->hasOne(Visit::class);
    }

    /**
     * Check if this shift has a completed visit
     */
    public function hasCompletedVisit(): bool
    {
        return $this->visit && 
               $this->visit->clock_in_time && 
               $this->visit->clock_out_time;
    }

    /**
     * Get the actual hours worked (for records/display purposes)
     */
    public function getActualHours(): float
    {
        if (!$this->hasCompletedVisit()) {
            return 0;
        }

        $visit = $this->visit;
        $start = $visit->clock_in_time;
        $end = $visit->clock_out_time;

        return round($end->diffInMinutes($start) / 60, 2);
    }

    /**
     * Get the total billable hours for this shift (minimum 1 hour billing rule)
     * Agency billing policy: Any visit under 1 hour is billed as 1 full hour
     */
    public function getBillableHours(): float
    {
        if (!$this->hasCompletedVisit()) {
            return 0;
        }

        $actualHours = $this->getActualHours();
        
        // Apply 1-hour minimum billing rule
        return max(1.0, $actualHours);
    }

    /**
     * Calculate the total billable amount for this shift
     */
    public function getBillableAmount(): float
    {
        return $this->getBillableHours() * $this->hourly_rate;
    }

    /**
     * Check if this visit qualifies for minimum billing (under 1 hour)
     */
    public function isMinimumBilling(): bool
    {
        return $this->hasCompletedVisit() && $this->getActualHours() < 1.0;
    }

    /**
     * Get billing details for display purposes
     */
    public function getBillingDetails(): array
    {
        if (!$this->hasCompletedVisit()) {
            return [
                'actual_hours' => 0,
                'billable_hours' => 0,
                'is_minimum_billing' => false,
                'amount' => 0,
            ];
        }

        $actualHours = $this->getActualHours();
        $billableHours = $this->getBillableHours();

        return [
            'actual_hours' => $actualHours,
            'billable_hours' => $billableHours,
            'is_minimum_billing' => $this->isMinimumBilling(),
            'amount' => $this->getBillableAmount(),
        ];
    }
}