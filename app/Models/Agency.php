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
        'is_lifetime_free', // NEW: Add new field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_lifetime_free' => 'boolean', // NEW: Cast to boolean
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

    // START: NEW METHODS FOR LIFETIME FREE FEATURE
    /**
     * Check if the agency has a lifetime free plan.
     */
    public function isLifetimeFree(): bool
    {
        return $this->is_lifetime_free === true;
    }

    /**
     * Check if the agency has an active subscription, including lifetime free plans.
     */
    public function hasActiveSubscription(): bool
    {
        // Lifetime free agencies always count as having an active subscription.
        if ($this->isLifetimeFree()) {
            return true;
        }

        // Fallback for regular, paying customers.
        // Note: The 'subscription_status' is managed by your webhook logic.
        return in_array($this->subscription_status, ['active', 'trialing']);
    }
    // END: NEW METHODS FOR LIFETIME FREE FEATURE
}