<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class AgencyRegistrationController extends Controller
{
    /**
     * Show the agency registration form.
     */
    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        if (!in_array($plan, ['basic', 'professional', 'enterprise', 'premium'])) {
            $plan = 'basic';
        }
        return view('auth.register-agency', compact('plan'));
    }

    /**
     * Handle an incoming registration request for a new agency.
     */
    public function store(Request $request)
    {
        $request->validate([
            'agency_name' => ['required', 'string', 'max:255', 'unique:'.Agency::class.',name'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan' => ['required', 'in:basic,professional,enterprise,premium'],
        ]);

        // Use a database transaction to ensure both records are created or neither are.
        DB::transaction(function () use ($request) {
            // Create the Agency
            $agency = Agency::create([
                'name' => $request->agency_name,
                'slug' => Str::slug($request->agency_name),
                'contact_email' => $request->email,
                'subscription_plan' => $request->plan,
                'subscription_status' => 'trial', // All new sign-ups start on a trial
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Create the Admin User for the Agency
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'agency_id' => $agency->id,
                'role' => 'agency_admin', // Assign the admin role
            ]);

            // Log the new user in
            Auth::login($user);
        });

        // Redirect to the dashboard with a success message
        return redirect()->route('dashboard')->with('success', 'Welcome! Your 14-day trial has started.');
    }
}

