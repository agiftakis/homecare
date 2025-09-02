<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription creation page.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
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

        $agency = Auth::user()->agency;
        $planPriceId = $this->getStripePriceId($request->plan);
        $paymentMethod = $request->payment_method;

        try {
            $agency->newSubscription('default', $planPriceId)
                ->trialDays(14)
                ->create($paymentMethod);

            // NO NEED to manually update the agency status. Cashier handles it.
            // You can check the status anytime using: $agency->onTrial()

            return redirect()->route('dashboard')->with('success', 'Subscription activated! Your 14-day trial has begun.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    /**
     * A helper function to get the correct Stripe Price ID from the .env file.
     */
    private function getStripePriceId($plan)
    {
        return match (strtolower($plan)) {
            'professional' => env('STRIPE_PROFESSIONAL_PRICE_ID'),
            'premium' => env('STRIPE_PREMIUM_PRICE_ID'),
            'enterprise' => env('STRIPE_ENTERPRISE_PRICE_ID'),
            'basic' => env('STRIPE_BASIC_PRICE_ID'),
            default => env('STRIPE_BASIC_PRICE_ID'),
        };
    }
}
