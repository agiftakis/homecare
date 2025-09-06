<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // SuperAdmin can access everything
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Regular users can only access their agency's clients
        return $user->agency_id === $client->agency_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // SuperAdmin can access everything
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Regular users can only access their agency's clients
        return $user->agency_id === $client->agency_id;
    }
}