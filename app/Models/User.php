<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes; // Import the SoftDeletes trait
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // ✅ ADDED: Use the SoftDeletes trait
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agency_id',
        'role',
        'deleted_by', // ✅ ADDED: Allow mass assignment for the audit trail
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_setup_expires_at' => 'datetime',
        ];
    }

    /**
     * This method defines the relationship that a User belongs to an Agency.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the caregiver profile associated with the user.
     */
    public function caregiver(): HasOne
    {
        return $this->hasOne(Caregiver::class);
    }

    /**
     * ✅ MISSING RELATIONSHIP: Get the client profile associated with the user.
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }
}