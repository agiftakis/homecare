<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth; // <-- Add this line

class SubscriptionController extends Controller
{
    /**
     * Show the subscription creation page.
     */
    public function create(Request $request)
    {
        // Get the plan from the query string (e.g., ?plan=basic)
        $plan = $request->query('plan', 'basic'); 
        
        // **THE FIX:** Use the Auth facade for better IDE support
        $intent = Auth::user()->agency->createSetupIntent();

        return view('subscription.create', compact('plan', 'intent'));
    }

    /**
     * Store the new subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:basic,professional,premium,enterprise',
            'payment_method' => 'required|string',
        ]);

        // **THE FIX:** Use the Auth facade here as well
        $agency = Auth::user()->agency;
        $planName = $request->plan;
        $paymentMethod = $request->payment_method;

        try {
            // Create the subscription in Stripe
            $agency->newSubscription('default', $this->getStripePriceId($planName))
                   ->create($paymentMethod);

            // Update the agency's status in your database
            $agency->update([
                'subscription_status' => 'active',
                'subscription_plan' => $planName,
                'trial_ends_at' => null, // End the trial period
            ]);
            
            return redirect()->route('dashboard')->with('success', 'Subscription activated successfully!');

        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    /**
     * A helper function to get the correct Stripe Price ID from the .env file.
     */
    private function getStripePriceId($plan)
    {
        return match(strtolower($plan)) {
            'professional' => env('STRIPE_PROFESSIONAL_PRICE_ID'),
            'premium' => env('STRIPE_PREMIUM_PRICE_ID'),
            'enterprise' => env('STRIPE_ENTERPRISE_PRICE_ID'),
            'basic' => env('STRIPE_BASIC_PRICE_ID'),
            default => env('STRIPE_BASIC_PRICE_ID'),
        };
    }
}

