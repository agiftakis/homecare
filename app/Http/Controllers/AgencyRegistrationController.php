<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AgencyRegistrationController extends Controller
{
    /**
     * Show the agency registration form.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        return view('auth.register-agency', compact('plan'));
    }

    /**
     * Handle the registration of a new agency.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'plan' => 'required|in:basic,professional,premium,enterprise'
        ]);

        $agency = null;

        DB::transaction(function () use ($validated, &$agency) {
            // **THE FIX:** We create the agency but DO NOT set any trial information here.
            // The subscription status will be handled by the payment controller.
            $agency = Agency::create([
                'name' => $validated['agency_name'],
                'contact_email' => $validated['email'],
                'subscription_plan' => $validated['plan'],
            ]);

            // Create the admin user for the agency
            $user = User::create([
                'agency_id' => $agency->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'agency_admin',
            ]);

            // Login the new user
            Auth::login($user);
        });

        // Redirect to the subscription page to collect payment details
        return redirect()->route('subscription.create', ['plan' => $validated['plan']]);
    }
}

