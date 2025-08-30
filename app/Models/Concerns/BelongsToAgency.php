<?php

namespace App\Models\Concerns;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToAgency
{
    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // This global scope will automatically filter all queries to only include
        // records that belong to the currently logged-in user's agency.
        static::addGlobalScope('agency', function (Builder $builder) {
            if (auth()->check() && auth()->user()->agency_id) {
                $builder->where(static::getTable() . '.agency_id', auth()->user()->agency_id);
            }
        });

        // This will automatically set the agency_id on any new records
        // that are created, so you don't have to do it manually.
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->agency_id) {
                $model->agency_id = auth()->user()->agency_id;
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
