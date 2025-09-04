<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToAgency;

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
        'profile_picture_path',
        // ADD CODE HERE - new document fields
        'certifications_filename',
        'certifications_path',
        'professional_licenses_filename',
        'professional_licenses_path',
        'state_province_id_filename',
        'state_province_id_path',
        'agency_id'
    ];


    public function getCertificationsUrlAttribute(): ?string
    {
        if (!$this->certifications_path) {
            return null;
        }

        $firebaseService = new FirebaseStorageService();
        return $firebaseService->getPublicUrl($this->certifications_path);
    }

    public function getProfessionalLicensesUrlAttribute(): ?string
    {
        if (!$this->professional_licenses_path) {
            return null;
        }

        $firebaseService = new FirebaseStorageService();
        return $firebaseService->getPublicUrl($this->professional_licenses_path);
    }

    public function getStateProvinceIdUrlAttribute(): ?string
    {
        if (!$this->state_province_id_path) {
            return null;
        }

        $firebaseService = new FirebaseStorageService();
        return $firebaseService->getPublicUrl($this->state_province_id_path);
    }

    /**
     * Display methods for documents
     */
    public function getCertificationsDisplayAttribute(): string
    {
        return $this->certifications_filename ?: 'No document uploaded';
    }

    public function getProfessionalLicensesDisplayAttribute(): string
    {
        return $this->professional_licenses_filename ?: 'No document uploaded';
    }

    public function getStateProvinceIdDisplayAttribute(): string
    {
        return $this->state_province_id_filename ?: 'No document uploaded';
    }

    /**
     * Check if a document exists
     */
    public function hasCertifications(): bool
    {
        return !empty($this->certifications_path);
    }

    public function hasProfessionalLicenses(): bool
    {
        return !empty($this->professional_licenses_path);
    }

    public function hasStateProvinceId(): bool
    {
        return !empty($this->state_province_id_path);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
