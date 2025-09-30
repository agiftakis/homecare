<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Allow Super Admins to pass through without any checks
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Proceed if user is part of an agency
        if ($user && $user->agency) {
            $agency = $user->agency;

            // Use the hasActiveSubscription method we created in the Agency model.
            // This method already includes the check for isLifetimeFree().
            if ($agency->hasActiveSubscription()) {
                return $next($request);
            }
        }

        // If not a super admin and no active subscription, redirect to billing page.
        // We also allow access to the subscription management page itself to avoid a redirect loop.
        if ($request->routeIs('subscription.create') || $request->routeIs('subscription.store')) {
             return $next($request);
        }

        return redirect()->route('subscription.create')->with('error', 'Your subscription is not active. Please select a plan to continue.');
    }
}