<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function caregivers()
    {
        return $this->hasMany(Caregiver::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
