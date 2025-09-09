<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if a user is authenticated and belongs to an agency.
     * If the agency has a specific timezone set, it dynamically configures the
     * application's timezone for the duration of that user's request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if there is a user logged in.
        if (Auth::check()) {
            // Get the authenticated user.
            $user = Auth::user();

            // Check if the user is associated with an agency and that agency has a timezone set.
            // The 'agency' relationship should be defined on the User model.
            if ($user->agency && $user->agency->timezone) {
                // Dynamically set the application's timezone for this request.
                config(['app.timezone' => $user->agency->timezone]);

                // Also set the default timezone for Carbon instances.
                date_default_timezone_set($user->agency->timezone);
            }
        }

        // Continue processing the request.
        return $next($request);
    }
}
