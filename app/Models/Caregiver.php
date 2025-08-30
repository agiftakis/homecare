<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToAgency;

class Caregiver extends Model
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
        'certifications',
        'profile_picture_url',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
