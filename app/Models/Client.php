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

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
