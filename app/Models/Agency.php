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
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
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

    // **THE FIX:** These two methods are required by Laravel Cashier when you use a
    // model other than User as the billable entity. They tell Stripe which
    // name and email to use for the customer record.

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

    public function owner()
    {
        // This assumes your 'agencies' table has a 'user_id' column
        // that links to the 'id' on the 'users' table.
        return $this->belongsTo(User::class, 'user_id');
    }
}

