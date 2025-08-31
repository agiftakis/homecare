<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgencyRegistrationController extends Controller
{
    /**
     * Display the agency registration view.
     */
    public function showRegistrationForm(Request $request)
    {
        // Get the selected plan from the URL, default to 'basic' if not provided
        $plan = $request->query('plan', 'basic');

        return view('auth.register-agency', ['plan' => $plan]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @todo Implement the logic to store the new agency and user.
     */
    public function store(Request $request)
    {
        // We will implement this in the next step (Phase 2, Part 2)
    }
}
