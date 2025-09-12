<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use App\Services\FirebaseStorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Import the SoftDeletes trait

class Caregiver extends Model
{
    // ✅ ADDED: Use the SoftDeletes trait along with the others
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
        'date_of_birth',
        'certifications',
        'agency_id',
        'user_id',
        'profile_picture_path',
        // Document Fields
        'certifications_filename',
        'certifications_path',
        'professional_licenses_filename',
        'professional_licenses_path',
        'state_province_id_filename',
        'state_province_id_path',
        'deleted_by', // ✅ ADDED: Allow mass assignment for the audit trail
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the caregiver's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the public URL for the profile picture.
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture_path) {
            $firebaseStorageService = new FirebaseStorageService();
            return $firebaseStorageService->getPublicUrl($this->profile_picture_path);
        }
        return null;
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

    /**
     * Get the user account associated with the caregiver.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shifts for the caregiver.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}