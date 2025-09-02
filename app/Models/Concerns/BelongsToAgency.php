<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
            if (Auth::check() && Auth::user()->agency_id && Auth::user()->role !== 'super_admin') {
                // THIS IS THE FIX: We create a new instance to call getTable()
                $builder->where((new static)->getTable() . '.agency_id', Auth::user()->agency_id);
            }
        });

        // Automatically set the agency_id when a new record is created by a regular user.
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->agency_id && Auth::user()->role !== 'super_admin') {
                $model->agency_id = Auth::user()->agency_id;
            }
        });
    }

    /**
     * Relationship to the Agency.
     */
    public function agency()
    {
        return $this->belongsTo(\App\Models\Agency::class);
    }
}