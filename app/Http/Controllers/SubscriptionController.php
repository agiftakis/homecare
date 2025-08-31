<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Show the form for creating a new subscription.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $agency = Auth::user()->agency;

        // Get the plan from the query string (e.g., ?plan=basic)
        $plan = $request->query('plan', 'basic'); // Default to basic if not specified

        // Create a Stripe Setup Intent to collect payment details
        $intent = $agency->createSetupIntent();

        return view('subscription.create', [
            'intent' => $intent,
            'plan' => $plan,
            'stripe_key' => config('cashier.key')
        ]);
    }
}

