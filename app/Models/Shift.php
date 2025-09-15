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
    ];

    /**
     * ✅ NEW: The attributes that should be cast.
     * This tells Laravel to treat these columns as proper date objects
     * and format them correctly when sending them to the browser.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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