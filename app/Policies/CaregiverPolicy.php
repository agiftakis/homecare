<?php

namespace App\Policies;

use App\Models\Caregiver;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CaregiverPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Caregiver $caregiver): bool
    {
        return $user->agency_id === $caregiver->agency_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Caregiver $caregiver): bool
    {
        return $user->agency_id === $caregiver->agency_id;
    }
}