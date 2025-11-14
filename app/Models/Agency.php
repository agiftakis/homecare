<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Laravel\Cashier\Billable; // Removed Stripe

class Agency extends Model
{
    use HasFactory; // Removed Stripe 'Billable' trait

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
        'timezone',
        'user_id',
        'is_lifetime_free', // KEPT: This is the new "on" switch
        'suspended',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'trial_ends_at' => 'datetime', // Removed Stripe
        // 'subscription_ends_at' => 'datetime', // Removed Stripe
        'is_lifetime_free' => 'boolean', // KEPT: This is critical
    ];

    /**
     * Get the users for the agency.
     */
    public function users()
    {
        return $this->hasMany(User::class);
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
     * This is PRESERVED and is the core logic now.
     */
    public function isLifetimeFree(): bool
    {
        return $this->is_lifetime_free === true;
    }

    /**
     * Check if the agency has an active subscription.
     * UPDATED: This logic is now identical to isLifetimeFree(),
     * as this is the only way to be "active".
     * This prevents errors if other code calls this method.
     */
    public function hasActiveSubscription(): bool
    {
        // The ONLY way to have an active "subscription" now
        // is to be manually set as lifetime free.
        return $this->isLifetimeFree();
    }
    // END: NEW METHODS FOR LIFETIME FREE FEATURE
}
