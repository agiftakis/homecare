<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        // In a real application, you might pull these from a database
        $plans = [
            [
                'name' => 'Basic',
                'price' => 49,
                'features' => [
                    'Up to 5 caregivers',
                    'Up to 25 clients',
                    'Basic scheduling',
                    'Email support'
                ]
            ],
            [
                'name' => 'Professional',
                'price' => 99,
                'features' => [
                    'Up to 25 caregivers',
                    'Up to 100 clients',
                    'Advanced scheduling',
                    'Caregiver portal',
                    'Basic reporting',
                    'Priority support'
                ]
            ],
            [
                'name' => 'Enterprise',
                'price' => 199,
                'features' => [
                    'Unlimited caregivers',
                    'Unlimited clients',
                    'Full feature access',
                    'Custom integrations',
                    'Dedicated support'
                ]
            ]
        ];

        return view('pricing.index', compact('plans'));
    }
}
