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

        // 1. Allow Super Admins to pass through without any checks
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // 2. Check for an agency and 'is_lifetime_free' status
        // This is now the ONLY way for a non-admin to have access.
        // We no longer call hasActiveSubscription() as that checks Stripe.
        if ($user && $user->agency && $user->agency->is_lifetime_free) {
            return $next($request);
        }

        // 3. Avoid redirect loop if they are already on the 'subscription.required' page.
        // We will create this route in web.php next.
        if ($request->routeIs('subscription.required')) {
            return $next($request);
        }

        // 4. If not a super admin and not lifetime free, redirect to our new "contact us" page.
        // We will define the 'subscription.required' route in routes/web.php next.
        return redirect()->route('subscription.required');
    }
}