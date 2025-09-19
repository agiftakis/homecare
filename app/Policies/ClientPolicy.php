<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * This method is executed before any other method in the policy.
     * If it returns true, the user is automatically authorized for ANY action
     * and the specific policy method (e.g., update, delete) is never called.
     * This is the recommended way to handle super admin permissions.
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

        return null; // Return null to fall through to the specific policy method.
    }

    /**
     * Determine whether the user can view any models.
     * This is for the index page.
     */
    public function viewAny(User $user): bool
    {
        // Any logged-in agency admin can view the client list.
        // The controller will scope the list to their agency.
        return $user->role === 'agency_admin';
    }

    /**
     * Determine whether the user can view the model.
     * This is for the show/edit page.
     */
    public function view(User $user, Client $client): bool
    {
        // An agency admin can only view clients that belong to their own agency.
        return $user->agency_id === $client->agency_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any agency admin can create new clients.
        return $user->role === 'agency_admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // An agency admin can only update clients that belong to their own agency.
        return $user->agency_id === $client->agency_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // An agency admin can only delete clients that belong to their own agency.
        return $user->agency_id === $client->agency_id;
    }
}