<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use App\Services\FirebaseStorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// ✅ 1. IMPORT: Added the Cache facade for caching functionality.
use Illuminate\Support\Facades\Cache;

class Client extends Model
{
    use HasFactory, BelongsToAgency, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'date_of_birth',
        'care_plan',
        'agency_id',
        'user_id',
        'profile_picture_path',
        'current_medications',
        'discontinued_medications',
        'recent_hospitalizations',
        'current_concurrent_dx',
        'designated_poa',
        'current_routines_am_pm',
        'fall_risk',
        'deleted_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the public URL for the profile picture from its path.
     * ✅ MODIFICATION: This is the ONLY method that was changed.
     * I've added caching here to improve performance. No other code was removed.
     * @return string
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if (!$this->profile_picture_path) {
            // Return a default placeholder if no picture is set
            return 'https://via.placeholder.com/150';
        }

        // Define a unique cache key for this client's profile picture URL.
        $cacheKey = "client_{$this->id}_profile_picture_url";

        // Remember the URL for 60 minutes.
        return Cache::remember($cacheKey, now()->addMinutes(60), function () {
            // This code only runs if the URL is not already in the cache.
            $firebaseStorageService = new FirebaseStorageService();
            return $firebaseStorageService->getPublicUrl($this->profile_picture_path);
        });
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
