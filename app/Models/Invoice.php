<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, BelongsToAgency;

    protected $fillable = [
        'agency_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'period_start',
        'period_end',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'status',
        'sent_at',
        'paid_at',
        'pdf_path',
        'client_name',
        'client_email',
        'client_address',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the agency that owns the invoice.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the client that the invoice is for.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the invoice items (line items).
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Generate the next invoice number for an agency.
     */
    public static function generateInvoiceNumber($agencyId): string
    {
        $year = date('Y');
        $prefix = "VL-{$year}-";
        
        // Find the highest invoice number for this agency and year
        $latestInvoice = static::where('agency_id', $agencyId)
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$latestInvoice) {
            return "{$prefix}001";
        }

        // Extract the number part and increment
        $lastNumber = (int) substr($latestInvoice->invoice_number, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$newNumber}";
    }

    /**
     * Calculate and set the total amounts including tax.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->subtotal * $this->tax_rate;
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    /**
     * Get the status display with proper formatting.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Mark the invoice as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}