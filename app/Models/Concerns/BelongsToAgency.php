<?php

namespace App\Models\Concerns;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; // Use the Auth facade

trait BelongsToAgency
{
    /**
     * The "boot" method of the model.
     *
     * This method is called when the trait is booted, and it applies our multi-tenancy rules.
     */
    protected static function bootBelongsToAgency(): void
    {
        // Global scope to automatically filter all data by the current user's agency.
        static::addGlobalScope('agency', function (Builder $builder) {
            // **THE FIX:** We only apply the agency filter if the user is logged in,
            // has an agency_id, AND is NOT a super_admin.
            if (Auth::check() && Auth::user()->agency_id && Auth::user()->role !== 'super_admin') {
                $builder->where(static::getTable() . '.agency_id', Auth::user()->agency_id);
            }
        });

        // Automatically set the agency_id when a new record is created by a regular user.
        static::creating(function ($model) {
            // **THE FIX:** We only set the agency_id automatically if the user is a regular
            // agency user. The Super Admin will need to set it manually when creating records.
            if (Auth::check() && Auth::user()->agency_id && Auth::user()->role !== 'super_admin') {
                $model->agency_id = Auth::user()->agency_id;
            }
        });
    }

    /**
     * Defines the relationship where this model belongs to an Agency.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}

