<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FeaturePageController extends Controller
{
    /**
     * Display the Intelligent Scheduling feature page.
     */
    public function showScheduling(): View
    {
        return view('features.scheduling');
    }

    /**
     * Display the Centralized Client Data feature page.
     */
    public function showClientData(): View
    {
        return view('features.client-data');
    }

    /**
     * Display the Seamless Communication feature page.
     */
    public function showCommunication(): View
    {
        return view('features.communication');
    }

    /**
     * Display the Exceptional Billing feature page.
     */
    public function showBilling(): View
    {
        return view('features.billing');
    }
}