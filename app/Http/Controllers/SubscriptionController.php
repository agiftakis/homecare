<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// We no longer need Stripe, Cashier, Logging, or Mail services here.
// This controller is now extremely simple.

class SubscriptionController extends Controller
{
    /**
     * ✅ NEW: Show the "Subscription Required" page.
     * This page is for logged-in users whose agency is not active
     * (i.e., 'is_lifetime_free' is false).
     * It will instruct them to contact support.
     */
    public function required()
    {
        $user = Auth::user();
        // Get agency name, handle case if agency is somehow null
        $agencyName = $user->agency ? $user->agency->name : 'Your Agency';
        
        // This is the contact email you provided
        $contactEmail = 'vitalink.notifications1@gmail.com';

        // We will create this view in the next step.
        return view('subscription.required', [
            'user' => $user,
            'agencyName' => $agencyName,
            'contactEmail' => $contactEmail
        ]);
    }

    // --- ALL PREVIOUS STRIPE METHODS HAVE BEEN REMOVED ---
    // ---------------------------------------------------
    // public function create(Request $request) { ... } // REMOVED
    // public function store(Request $request) { ... } // REMOVED
    // public function manage() { ... } // REMOVED
    // private function getStripePriceId($planName) { ... } // REMOVED
    // private function getPlanNameFromSubscription($subscription) { ... } // REMOVED
    // ---------------------------------------------------
}