<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // <-- Add this line

class AgencyRegistrationController extends Controller
{
    /**
     * Show the agency registration form.
     */
    public function create(Request $request)
    {
        // Get the plan from the query string, default to 'basic' if not present
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
            // Create the agency
            $agency = Agency::create([
                'name' => $validated['agency_name'],
                'contact_email' => $validated['email'],
                'subscription_plan' => $validated['plan'],
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Create the admin user for the agency
            $user = User::create([
                'agency_id' => $agency->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'agency_admin',
            ]);

            // **THE FIX:** Use the Auth facade to log in the new user
            Auth::login($user);
        });

        // Redirect to the subscription page with the chosen plan
        return redirect()->route('subscription.create', ['plan' => $validated['plan']])
                         ->with('success', 'Welcome! Your agency account has been created. Please enter your payment details to start your subscription.');
    }
}

