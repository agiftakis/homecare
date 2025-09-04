<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToAgency;

class Client extends Model
{
    use HasFactory, BelongsToAgency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'address',
        'care_plan',
        'profile_picture_path',
        // ADD CODE HERE - new client fields
        'current_medications',
        'discontinued_medications',
        'recent_hospitalizations',
        'current_concurrent_dx',
        'designated_poa',
        'current_routines_am_pm',
        'fall_risk',
        'agency_id'
    ];


    // ADD CODE HERE - before the shifts() method
    /**
     * Get formatted value or N/A for optional fields
     */
    public function getCurrentMedicationsDisplayAttribute(): string
    {
        return $this->current_medications ?: 'N/A';
    }

    public function getDiscontinuedMedicationsDisplayAttribute(): string
    {
        return $this->discontinued_medications ?: 'N/A';
    }

    public function getRecentHospitalizationsDisplayAttribute(): string
    {
        return $this->recent_hospitalizations ?: 'N/A';
    }

    public function getCurrentConcurrentDxDisplayAttribute(): string
    {
        return $this->current_concurrent_dx ?: 'N/A';
    }

    public function getDesignatedPoaDisplayAttribute(): string
    {
        return $this->designated_poa ?: 'N/A';
    }

    public function getCurrentRoutinesAmPmDisplayAttribute(): string
    {
        return $this->current_routines_am_pm ?: 'N/A';
    }

    public function getFallRiskDisplayAttribute(): string
    {
        if (!$this->fall_risk) {
            return 'N/A';
        }
        return ucfirst($this->fall_risk);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
