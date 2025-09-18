<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * ✅ NEW: Get the full caregiver name from the visit record
     * This preserves the caregiver information even if the caregiver is deleted
     */
    public function getCaregiverFullNameAttribute(): string
    {
        return trim($this->caregiver_first_name . ' ' . $this->caregiver_last_name);
    }
}