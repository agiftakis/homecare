<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use App\Services\FirebaseStorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for best practice
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED: Import the SoftDeletes trait

class Client extends Model
{
    // ✅ ADDED: Use the SoftDeletes trait
    use HasFactory, BelongsToAgency, SoftDeletes;

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
        'address',
        'date_of_birth',
        'care_plan',
        'agency_id',
        'profile_picture_path',
        // Medical Fields
        'current_medications',
        'discontinued_medications',
        'recent_hospitalizations',
        'current_concurrent_dx',
        'designated_poa',
        'current_routines_am_pm',
        'fall_risk',
        'deleted_by', // ✅ ADDED: Allow mass assignment for the audit trail
    ];

    /**
     * The attributes that should be cast.
     * Added this to ensure date_of_birth is handled correctly.
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the client's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the public URL for the profile picture from its path.
     * This is the crucial new method.
     * @return string
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture_path) {
            $firebaseStorageService = new FirebaseStorageService();
            return $firebaseStorageService->getPublicUrl($this->profile_picture_path);
        }

        // Return a default placeholder if no picture is set
        return 'https://via.placeholder.com/150';
    }

    // Display Attributes for Medical Info
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

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Get the user account associated with the client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}