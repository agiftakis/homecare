<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DateTimeZone;

class AgencySettingsController extends Controller
{
    /**
     * Show the form for editing agency settings.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Get the agency for the currently authenticated admin
        $agency = Auth::user()->agency;

        // Filter timezones for North America to display in the dropdown
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        // Return the view, passing the agency and the timezone list to it
        return view('settings.edit', compact('agency', 'northAmericaTimezones'));
    }

    /**
     * Update the agency settings in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Get the agency for the currently authenticated admin
        $agency = Auth::user()->agency;

        // Generate the valid North American timezone list for validation
        $allTimezones = DateTimeZone::listIdentifiers();
        $northAmericaTimezones = array_filter($allTimezones, function ($timezone) {
            return strpos($timezone, 'America/') === 0;
        });

        // Validate the incoming request data
        $validated = $request->validate([
            'timezone' => ['required', 'string', Rule::in($northAmericaTimezones)],
            // You can add validation for other settings fields here in the future
        ]);

        // Update the agency's timezone in the database
        $agency->update([
            'timezone' => $validated['timezone'],
        ]);

        // Redirect back to the settings page with a success message
        return redirect()->route('settings.edit')->with('success', 'Agency settings have been updated successfully.');
    }
}
