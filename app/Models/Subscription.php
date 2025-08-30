<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'stripe_subscription_id',
        'plan_name',
        'status',
        'current_period_start',
        'current_period_end',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
