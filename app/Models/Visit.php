<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Visit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'shift_id',
        'agency_id',
        'clock_in_time',
        'clock_out_time',
        'signature_path',
        'clock_out_signature_path',
        'progress_notes',           // ✅ ADDED: Allow mass assignment of progress notes
        'caregiver_first_name',     // ✅ ADDED: Preserve caregiver name at visit creation
        'caregiver_last_name',      // ✅ ADDED: Preserve caregiver name at visit creation
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    /**
     * Get the shift that this visit belongs to.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the agency that this visit belongs to.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the invoice items that reference this visit.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if this visit has been invoiced.
     */
    public function isInvoiced(): bool
    {
        return $this->invoiceItems()->exists();
    }

    /**
     * Get the full caregiver name from the visit record
     * This preserves the caregiver information even if the caregiver is deleted
     */
    public function getCaregiverFullNameAttribute(): string
    {
        return trim($this->caregiver_first_name . ' ' . $this->caregiver_last_name);
    }

    /**
     * ✅ NEW: Get all modifications/audit trail for this visit.
     */
    public function modifications()
    {
        return $this->hasMany(VisitModification::class)->orderBy('modified_at', 'desc');
    }

    /**
     * ✅ NEW: Log a modification to this visit.
     */
    public function logModification(string $action, ?array $changes = null, ?string $reason = null): void
    {
        VisitModification::create([
            'visit_id' => $this->id,
            'modified_by' => Auth::id(),
            'action' => $action,
            'changes' => $changes,
            'reason' => $reason,
            'modified_at' => now(),
        ]);
    }
}