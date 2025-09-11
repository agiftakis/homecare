<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Agency extends Model
{
    use HasFactory, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_email',
        'phone',
        'address',
        'timezone', //THE FIX: Allow timezone to be mass-assigned.
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Get the users for the agency.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the Stripe-compatible name for the billable model.
     */
    public function stripeName(): string|null
    {
        return $this->name;
    }

    /**
     * Get the Stripe-compatible email address for the billable model.
     */
    public function stripeEmail(): string|null
    {
        return $this->contact_email;
    }

    /**
     * Get the user that owns the agency.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the clients for the agency.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get the caregivers for the agency.
     */
    public function caregivers()
    {
        return $this->hasMany(Caregiver::class);
    }
}