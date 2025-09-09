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
        'clock_in_time',
        'clock_out_time',
        'clock_in_signature',
        'clock_out_signature',
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
     * ✅ NEW RELATIONSHIP: Get the visit record for this shift
     * A shift can have one visit (clock-in/out record)
     */
    public function visit()
    {
        return $this->hasOne(Visit::class);
    }
}