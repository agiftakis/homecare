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

class Caregiver extends Model
{
    use HasFactory, BelongsToAgency, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'certifications',
        'agency_id',
        'user_id',
        'profile_picture_path',
        'certifications_filename',
        'certifications_path',
        'professional_licenses_filename',
        'professional_licenses_path',
        'state_province_id_filename',
        'state_province_id_path',
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
     * Get the public URL for the profile picture.
     * ✅ MODIFICATION: This is the ONLY method that was changed.
     * I've added caching here to improve performance. No other code was removed.
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        if (!$this->profile_picture_path) {
            return null;
        }

        // Define a unique cache key for this caregiver's profile picture URL.
        $cacheKey = "caregiver_{$this->id}_profile_picture_url";

        // Remember the URL for 60 minutes.
        return Cache::remember($cacheKey, now()->addMinutes(60), function () {
            // This code only runs if the URL is not already in the cache.
            $firebaseStorageService = new FirebaseStorageService();
            return $firebaseStorageService->getPublicUrl($this->profile_picture_path);
        });
    }

    // --- Document Management Methods ---

    public function getCertificationsUrlAttribute(): string
    {
        return $this->certifications_path ? (new FirebaseStorageService())->getPublicUrl($this->certifications_path) : '';
    }

    public function getCertificationsDisplayAttribute(): string
    {
        return $this->certifications_filename ?: 'N/A';
    }

    public function hasCertifications(): bool
    {
        return !empty($this->certifications_path);
    }

    public function getProfessionalLicensesUrlAttribute(): string
    {
        return $this->professional_licenses_path ? (new FirebaseStorageService())->getPublicUrl($this->professional_licenses_path) : '';
    }

    public function getProfessionalLicensesDisplayAttribute(): string
    {
        return $this->professional_licenses_filename ?: 'N/A';
    }

    public function hasProfessionalLicenses(): bool
    {
        return !empty($this->professional_licenses_path);
    }

    public function getStateProvinceIdUrlAttribute(): string
    {
        return $this->state_province_id_path ? (new FirebaseStorageService())->getPublicUrl($this->state_province_id_path) : '';
    }

    public function getStateProvinceIdDisplayAttribute(): string
    {
        return $this->state_province_id_filename ?: 'N/A';
    }

    public function hasStateProvinceId(): bool
    {
        return !empty($this->state_province_id_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
