<?php

namespace App\Policies;

use App\Models\Caregiver;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CaregiverPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * This method is the perfect place to authorize a super_admin
     * for all actions related to caregivers, preventing code repetition.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return null; // Fall through to the specific policy method below.
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view the list of caregivers.
        return $user->role === 'agency_admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Caregiver $caregiver): bool
    {
        // An agency admin can only view caregivers that belong to their own agency.
        return $user->agency_id === $caregiver->agency_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create new caregivers.
        return $user->role === 'agency_admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Caregiver $caregiver): bool
    {
        // An agency admin can only update caregivers that belong to their own agency.
        return $user->agency_id === $caregiver->agency_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Caregiver $caregiver): bool
    {
        // An agency admin can only delete caregivers that belong to their own agency.
        return $user->agency_id === $caregiver->agency_id;
    }
}