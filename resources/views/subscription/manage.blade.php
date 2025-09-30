<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- START: NEW LOGIC FOR LIFETIME PLAN --}}
                    @if (isset($isLifetime) && $isLifetime)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Subscription</h3>
                            
                            <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current Plan</h4>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            Lifetime Free Plan
                                        </p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</h4>
                                        <p class="mt-1 text-lg font-medium">
                                            <span class="text-green-600 dark:text-green-400">Active</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-green-200 dark:border-green-700 pt-4">
                                     <p class="text-sm text-gray-600 dark:text-gray-300">Your agency has unlimited, permanent access to all features. No billing information is required.</p>
                                </div>
                            </div>
                        </div>

                    @else
                    {{-- END: NEW LOGIC --}}

                        {{-- START: ORIGINAL CODE FOR STRIPE USERS --}}
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Subscription</h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Current Plan</h4>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $subscriptionData['plan'] }}
                                        </p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</h4>
                                        <p class="mt-1 text-lg font-medium">
                                            @if($subscriptionData['status'] === 'active')
                                                <span class="text-green-600 dark:text-green-400">Active</span>
                                            @elseif($subscriptionData['status'] === 'canceled')
                                                <span class="text-red-600 dark:text-red-400">Canceled</span>
                                            @elseif($subscriptionData['status'] === 'past_due')
                                                <span class="text-yellow-600 dark:text-yellow-400">Past Due</span>
                                            @else
                                                <span class="text-gray-600 dark:text-gray-400">{{ ucfirst($subscriptionData['status']) }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    @if($subscriptionData['current_period_end'])
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            @if($subscriptionData['cancel_at_period_end'])
                                                Subscription Ends
                                            @else
                                                Next Billing Date
                                            @endif
                                        </h4>
                                        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                                            {{ date('F j, Y', $subscriptionData['current_period_end']) }}
                                        </p>
                                    </div>
                                    @endif

                                    @if($subscriptionData['cancel_at_period_end'])
                                    <div>
                                        <h4 class="text-sm font-medium text-red-500 uppercase tracking-wide">Notice</h4>
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            Your subscription will be canceled at the end of the current billing period.
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Plan Features</h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                @php
                                    $planFeatures = [
                                        'Basic' => [
                                            'Up to 10 clients',
                                            'Basic scheduling',
                                            'Email support',
                                            'Standard reporting'
                                        ],
                                        'Professional' => [
                                            'Up to 30 clients',
                                            'Advanced scheduling',
                                            'Priority email support',
                                            'Advanced reporting',
                                            'Custom forms'
                                        ],
                                        'Premium' => [
                                            'Up to 60 clients',
                                            'Advanced scheduling',
                                            'Phone & email support',
                                            'Premium reporting',
                                            'Custom forms',
                                            'API access'
                                        ],
                                        'Enterprise' => [
                                            'Up to 310 clients',
                                            'Enterprise scheduling',
                                            '24/7 support',
                                            'Enterprise reporting',
                                            'Custom integrations',
                                            'API access',
                                            'Dedicated account manager'
                                        ]
                                    ];
                                    $currentFeatures = $planFeatures[$subscriptionData['plan']] ?? [];
                                @endphp
                                
                                @if(!empty($currentFeatures))
                                    <ul class="space-y-2">
                                        @foreach($currentFeatures as $feature)
                                            <li class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">No active subscription plan.</p>
                                @endif
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Billing Management</h3>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Secure Billing Portal</h4>
                                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                            Update your payment method, view invoices, download receipts, or modify your subscription through our secure Stripe portal.
                                        </p>
                                        
                                        @if($portalUrl)
                                            <div class="mt-4">
                                                <a href="{{ $portalUrl }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-150 ease-in-out">
                                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                    Manage Billing & Subscription
                                                </a>
                                            </div>
                                        @else
                                            <div class="mt-4">
                                                <p class="text-sm text-red-600 dark:text-red-400">
                                                    Unable to generate billing portal link. Please contact support.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- END: ORIGINAL CODE FOR STRIPE USERS --}}
                    @endif

                    <div class="border-t border-gray-200 dark:border-gray-600 pt-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Need Help?</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Upgrade Your Plan</h4>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Need more clients or features? Use the billing portal to upgrade your subscription.
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Contact Support</h4>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Have questions about your subscription? Contact our support team for assistance.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>