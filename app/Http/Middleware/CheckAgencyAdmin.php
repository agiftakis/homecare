<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAgencyAdmin
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that only users with the 'agency_admin' role can access the route.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and if their role is 'agency_admin'.
        if (!Auth::check() || Auth::user()->role !== 'agency_admin') {
            // If not, abort the request with a 403 Forbidden error.
            abort(403, 'Unauthorized action.');
        }

        // If the user is an agency admin, allow the request to proceed.
        return $next($request);
    }
}
