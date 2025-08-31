<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing - {{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="antialiased bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-900 shadow-sm sticky top-0 z-50">
        <nav x-data="{ open: false }" class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo" class="h-16 w-auto">
                <span class="text-xl font-bold text-gray-800 dark:text-white">VitaLink</span>
            </a>
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="{{ route('login') }}"
                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 px-4 transition duration-300">Log
                    In</a>
                <a href="{{ route('register') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                    Get Started
                </a>
            </div>
            <!-- Hamburger Menu Button -->
            <div class="md:hidden">
                <button @click="open = !open" class="text-gray-800 dark:text-gray-200 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
            <!-- Mobile Menu -->
            <div x-show="open" @click.away="open = false"
                class="md:hidden fixed top-0 left-0 w-full h-screen bg-gray-900 bg-opacity-95 z-50 flex flex-col items-center justify-center"
                style="display: none;">
                <button @click="open = false" class="absolute top-6 right-6 text-white text-3xl">&times;</button>
                <a href="{{ route('login') }}" class="text-2xl text-white py-4">Log In</a>
                <a href="{{ route('register') }}"
                    class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-lg text-xl transition duration-300">Get
                    Started</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="bg-gray-50 dark:bg-gray-800 py-20">
            <div class="container mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
                <div class="text-center md:text-left">
                    <span class="text-indigo-500 font-semibold text-6xl">Pricing Plans</span>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mt-2 mb-4">Home Care
                        Software Plans That Meet Your Agency's Needs</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-xl mx-auto md:mx-0">Choose the right plan
                        for your agency. We understand that every agency is unique and has its own set of challenges and
                        needs. Find the perfect fit based on the features you need and the number of clients you serve.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('images/pricing-hero.png') }}" alt="VitaLink Software Mockup"
                        class="mx-auto rounded-lg shadow-2xl max-w-md w-full">
                </div>
            </div>
        </section>

        <!-- Plans Section -->
        <section class="py-20">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Small Agency Plan -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 border border-gray-200 dark:border-gray-700 text-center flex flex-col">
                        <img src="{{ asset('images/icon-small-agency.png') }}" alt="Small Agency Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-xl font-bold mb-2">Start Up Agencies</h3>
                        <p class="italic font-bold text-[#42C9A7] text-[13px]">under 10 clients</p>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 flex-grow">Focus on stability and growth while
                            we handle the rest.</p>
                        <a href="{{ route('register') }}?plan=basic"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                            Get a Quote
                        </a>
                    </div>
                    <!-- Medium Agency Plan -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 border border-gray-200 dark:border-gray-700 text-center flex flex-col">
                        <img src="{{ asset('images/icon-medium-agency.png') }}" alt="Medium Agency Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-xl font-bold mb-2">Growing Agencies</h3>
                        <p class="italic font-bold text-[#27D6F5] text-[13px]">between 20-100 clients</p>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 flex-grow">Improve caregiver retention and
                            streamline operations.</p>
                        <a href="{{ route('register') }}?plan=professional"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                            Get a Quote
                        </a>
                    </div>
                    <!-- Large Agency Plan -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 border border-gray-200 dark:border-gray-700 text-center flex flex-col">
                        <img src="{{ asset('images/icon-large-agency.png') }}" alt="Large Agency Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-xl font-bold mb-2">Established Agencies</h3>
                        <p class="italic font-bold text-[#917dc7] text-[13px]">over 150 clients</p>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 flex-grow">Advanced tools for your agency's
                            specific requirements.</p>
                        <a href="{{ route('register') }}?plan=enterprise"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                            Get a Quote
                        </a>
                    </div>
                    <!-- Enterprise Agency Plan -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 border border-gray-200 dark:border-gray-700 text-center flex flex-col">
                        <img src="{{ asset('images/icon-enterprise-agency.png') }}" alt="Enterprise Agency Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-xl font-bold mb-2">Enterprise</h3>
                        <p class="italic font-bold text-[#e3e36d] text-[13px]">an industry leader</p>
                        <p class="text-gray-500 dark:text-gray-400 mb-6 flex-grow">A scalable solution as you continue
                            your partner growth.</p>
                        <a href="{{ route('register') }}?plan=enterprise"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                            Book an Appointment
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-gray-50 dark:bg-gray-800 py-20">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-12">Explore Our Features</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-10">
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-admin.png') }}" alt="Admin" class="h-12 w-12 mb-3">
                        <span class="font-semibold">Admin App</span>
                    </div>
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-billing.png') }}" alt="Billing" class="h-12 w-12 mb-3">
                        <span class="font-semibold">Billing</span>
                    </div>
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-caregiver.png') }}" alt="Caregiver"
                            class="h-12 w-12 mb-3">
                        <span class="font-semibold">Caregiver App</span>
                    </div>
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-family.png') }}" alt="Family" class="h-12 w-12 mb-3">
                        <span class="font-semibold">Family Portals</span>
                    </div>
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-reports.png') }}" alt="Reports" class="h-12 w-12 mb-3">
                        <span class="font-semibold">Reporting</span>
                    </div>
                    <div class="flex flex-col items-center p-4 rounded-lg">
                        <img src="{{ asset('images/feature-evv.png') }}" alt="EVV" class="h-12 w-12 mb-3">
                        <span class="font-semibold">Scheduling</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20" x-data="{ open: 1 }">
            <div class="container mx-auto px-6 max-w-4xl">
                <h2 class="text-3xl font-bold text-center mb-12">Frequently Asked Questions</h2>
                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                        <button @click="open = (open === 1 ? 0 : 1)"
                            class="w-full flex justify-between items-center text-left p-6 font-semibold">
                            <span>What features are included in the free trial?</span>
                            <span x-show="open !== 1" class="text-2xl">&plus;</span>
                            <span x-show="open === 1" class="text-2xl">&minus;</span>
                        </button>
                        <div x-show="open === 1" x-collapse.duration.500ms
                            class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>Our 14-day free trial gives you access to all the features of our Professional plan,
                                allowing you to fully explore the capabilities of VitaLink for your agency.</p>
                        </div>
                    </div>
                    <!-- FAQ Item 2 -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                        <button @click="open = (open === 2 ? 0 : 2)"
                            class="w-full flex justify-between items-center text-left p-6 font-semibold">
                            <span>Does VitaLink provide technical support and updates?</span>
                            <span x-show="open !== 2" class="text-2xl">&plus;</span>
                            <span x-show="open === 2" class="text-2xl">&minus;</span>
                        </button>
                        <div x-show="open === 2" x-collapse.duration.500ms
                            class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>Yes, all of our plans include ongoing technical support and regular software updates to
                                ensure you always have the latest features and security enhancements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-black text-white py-12">
        <div class="container mx-auto px-6 text-center">
            <a href="{{ url('/') }}" class="flex items-center justify-center space-x-2 mb-4">
                <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo" class="h-10 w-auto">
                <span class="text-xl font-bold">VitaLink</span>
            </a>
            <p class="text-gray-400">&copy; {{ date('Y') }} VitaLink, Inc. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
