<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Client-specific channel for real-time updates
Broadcast::channel('client.{clientId}', function ($user, $clientId) {
    // Allow access if the authenticated user is a client and the clientId matches their client profile
    if ($user->role === 'client' && $user->client) {
        return (int) $user->client->id === (int) $clientId;
    }
    
    // Also allow agency admins to listen to any client in their agency for testing/monitoring
    if ($user->role === 'agency_admin') {
        $client = \App\Models\Client::find($clientId);
        return $client && (int) $client->agency_id === (int) $user->agency_id;
    }
    
    return false;
});