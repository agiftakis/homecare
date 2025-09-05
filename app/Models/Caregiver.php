<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\FirebaseStorageService;

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
        'agency_id',
        'profile_picture_path',
        // Document Fields
        'certifications_filename',
        'certifications_path',
        'professional_licenses_filename',
        'professional_licenses_path',
        'state_province_id_filename',
        'state_province_id_path',
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
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture_path) {
            $firebaseStorageService = new FirebaseStorageService();
            return $firebaseStorageService->getPublicUrl($this->profile_picture_path);
        }
        return 'https://via.placeholder.com/150';
    }

    // --- Document Management Methods ---

    // Certifications
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

    // Professional Licenses
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

    // State/Province ID
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
     * Get the shifts for the caregiver.
     */
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
