<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'visit_id',
        'service_description',
        'service_type',
        'service_date',
        'start_time',
        'end_time',
        'hours_worked',
        'hourly_rate',
        'line_total',
        'caregiver_name',
    ];

    protected $casts = [
        'service_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hours_worked' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns this item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the visit that this item is based on.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Calculate hours worked from start and end times.
     * FIXED: Ensure positive values always
     */
    public static function calculateHours($startTime, $endTime): float
    {
        $start = \Carbon\Carbon::parse($startTime);
        $end = \Carbon\Carbon::parse($endTime);
        
        // Calculate the difference in minutes (always positive)
        $diffInMinutes = $end->diffInMinutes($start);
        
        // Convert to hours with 2 decimal places
        return round($diffInMinutes / 60, 2);
    }

    /**
     * Extract caregiver name from signature path.
     */
    public static function extractCaregiverName($signaturePath): string
    {
        if (!$signaturePath) {
            return 'Unknown Caregiver';
        }

        // Extract name from path like "caregiver_documents/68bf2486dcffe_Ronda_Bellford_S..."
        $parts = explode('_', basename($signaturePath));
        
        if (count($parts) >= 3) {
            // Remove the hash part and get the name parts
            $firstName = $parts[1] ?? '';
            $lastName = $parts[2] ?? '';
            
            return trim(ucfirst(strtolower($firstName)) . ' ' . ucfirst(strtolower($lastName)));
        }

        return 'Unknown Caregiver';
    }

    /**
     * Create an invoice item from a visit.
     * ✅ UPDATED: Now accepts a custom hourly rate.
     */
    public static function createFromVisit(Visit $visit, Invoice $invoice, float $hourlyRate): static
    {
        $shift = $visit->shift;
        $startTime = $visit->clock_in_time;
        $endTime = $visit->clock_out_time;
        
        // Use the Shift model's billing logic for hours
        $billableHours = $shift->getBillableHours(); // This applies the 1-hour minimum rule
        $caregiverName = static::extractCaregiverName($visit->signature_path);
        
        return static::create([
            'invoice_id' => $invoice->id,
            'visit_id' => $visit->id,
            'service_description' => ucfirst($shift->service_type) . ' - ' . $visit->clock_in_time->format('m/d/Y'),
            'service_type' => $shift->service_type,
            'service_date' => $visit->clock_in_time->toDateString(),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'hours_worked' => $billableHours,
            'hourly_rate' => $hourlyRate, // ✅ USE the custom rate from the form
            'line_total' => $billableHours * $hourlyRate, // ✅ CALCULATE using the custom rate
            'caregiver_name' => $caregiverName,
        ]);
    }

    /**
     * Get formatted time range for display.
     */
    public function getTimeRangeAttribute(): string
    {
        return $this->start_time . ' - ' . $this->end_time;
    }
}