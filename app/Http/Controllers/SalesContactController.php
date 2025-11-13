<?php

namespace App\Http\Controllers;

use App\Jobs\SendTransactionalEmail;
use App\Mail\SalesInquiryEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalesContactController extends Controller
{
    /**
     * Display the sales contact form.
     *
     * @return \Illuminate\View\View
     */
    public function showContactForm()
    {
        return view('sales.contact-agency');
    }

    /**
     * Handle the sales contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContactForm(Request $request)
    {
        $validatedData = $request->validate([
            'agency_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'location' => 'required|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        $adminEmail = 'vitalink.notifications1@gmail.com';

        try {
            // We will create SalesInquiryEmail in the next step.
            // This uses the SendTransactionalEmail job just like your welcome email.
            $mailable = new SalesInquiryEmail($validatedData);
            
            // We use 'gmail_1' as that's the established mailer from your project summary.
            SendTransactionalEmail::dispatch($mailable->to($adminEmail), 'gmail_1')
                ->afterResponse(); // Dispatch after response for speed

        } catch (\Exception $e) {
            // Log the error if the email dispatch fails
            Log::error('Sales inquiry email dispatch failed: ' . $e->getMessage(), [
                'data' => $validatedData
            ]);
            
            // Optionally, you could redirect back with an error
            // But for the user, we can still say it worked and just log the error.
        }

        // Redirect back to the contact page with a success flash message
        // This will trigger the Alpine.js 'showSuccess' variable.
        return redirect()->route('sales.contact')->with('success', true);
    }
}