<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>VitaLink - Home Care Software</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .hero-bg {
                background-image: linear-gradient(rgba(17, 24, 39, 0.85), rgba(17, 24, 39, 0.85)), url('{{ asset('images/hero-background.jpg') }}');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            }
        </style>
    </head>
    <body class="antialiased font-sans text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900">
        <div x-data="{ navOpen: false }" @keydown.window.escape="navOpen = false">
            <!-- Header -->
            <header class="fixed inset-x-0 top-0 z-50 bg-gray-900/80 backdrop-blur-md">
                <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                    <div class="flex lg:flex-1">
                        <a href="/" class="flex items-center">
                            <img class="h-50 w-auto" src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo">
                            <span class="ml-4 text-2xl font-bold text-white">VitaLink</span>
                        </a>
                    </div>
                    <div class="flex lg:hidden">
                        <button type="button" @click="navOpen = !navOpen" class="inline-flex items-center justify-center rounded-md p-2.5 text-gray-200 hover:text-white">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" x-show="!navOpen" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" x-show="navOpen" />
                            </svg>
                        </button>
                    </div>
                    <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:gap-x-6">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-full bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-200 hover:text-white transition-colors duration-200">Log in</a>
                                <a href="{{ route('register') }}" class="rounded-full bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Get Started</a>
                            @endauth
                        @endif
                    </div>
                </nav>
                <!-- Mobile menu -->
                <div x-show="navOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="lg:hidden fixed inset-y-0 left-0 z-50 w-80 bg-white dark:bg-gray-800 shadow-xl rounded-r-2xl">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <a href="/" class="flex items-center">
                            <img class="h-50 w-auto" src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo">
                            <span class="ml-4 text-xl font-bold text-gray-900 dark:text-white">VitaLink</span>
                        </a>
                        <button type="button" @click="navOpen = false" class="rounded-md p-2.5 text-gray-600 dark:text-gray-200 hover:text-gray-800 dark:hover:text-white">
                            <span class="sr-only">Close menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6 px-6 space-y-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="block rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="block rounded-full px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-white transition-colors duration-200">Log in</a>
                                <a href="{{ route('register') }}" class="block rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Get Started</a>
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            <main>
                <!-- Hero Section -->
                <div class="relative isolate overflow-hidden hero-bg pt-24 pb-96">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-3xl text-center">
                            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">The Future of Home Care Management</h1>
                            <p class="mt-6 text-lg leading-8 text-gray-200">Streamline scheduling, simplify client management, and empower caregivers with VitaLink's all-in-one platform.</p>
                            <div class="mt-10 flex items-center justify-center gap-x-6">
                                <a href="{{ route('register') }}" class="rounded-full bg-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-lg hover:bg-indigo-500 transition-all duration-200 transform hover:scale-105">Get Started for Free</a>
                                <a href="#" class="text-sm font-semibold text-gray-200 hover:text-white transition-colors duration-200">Learn More <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Section -->
                <div class="bg-white dark:bg-gray-900 py-24 sm:py-32">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">All-In-One Solution</h2>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">A Customizable Software Solution</p>
                            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Discover how VitaLink transforms home care management with powerful, intuitive tools.</p>
                        </div>
                        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:grid-cols-3">
                                <!-- Feature 1 -->
                                <div class="flex flex-col items-center text-center bg-gray-50 dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                                    <img src="{{ asset('images/icon-schedule.png') }}" class="h-16 w-16 mb-4" alt="Scheduling Icon">
                                    <dt class="text-lg font-semibold leading-7 text-gray-900 dark:text-white">Intelligent Scheduling</dt>
                                    <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-300">
                                        <ul class="list-disc list-inside space-y-2 text-left">
                                            <li>User-Friendly Interface</li>
                                            <li>Real-Time Updates</li>
                                            <li>Automated Matching</li>
                                        </ul>
                                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                            <a href="#" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Learn More <span aria-hidden="true">→</span></a>
                                        </div>
                                    </dd>
                                </div>
                                <!-- Feature 2 -->
                                <div class="flex flex-col items-center text-center bg-gray-50 dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                                    <img src="{{ asset('images/icon-client.png') }}" class="h-16 w-16 mb-4" alt="Client Management Icon">
                                    <dt class="text-lg font-semibold leading-7 text-gray-900 dark:text-white">Full Control of Operations</dt>
                                    <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-300">
                                        <ul class="list-disc list-inside space-y-2 text-left">
                                            <li>Centralized Client Data</li>
                                            <li>Custom Reports & Analytics</li>
                                            <li>Secure Document Storage</li>
                                        </ul>
                                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                            <a href="#" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Learn More <span aria-hidden="true">→</span></a>
                                        </div>
                                    </dd>
                                </div>
                                <!-- Feature 3 -->
                                <div class="flex flex-col items-center text-center bg-gray-50 dark:bg-gray-800 rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                                    <img src="{{ asset('images/icon-communication.png') }}" class="h-16 w-16 mb-4" alt="Communication Icon">
                                    <dt class="text-lg font-semibold leading-7 text-gray-900 dark:text-white">Gain a Strategic Partner</dt>
                                    <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-300">
                                        <ul class="list-disc list-inside space-y-2 text-left">
                                            <li>Increase Revenue</li>
                                            <li>Streamline Communication</li>
                                            <li>Exceptional Support</li>
                                        </ul>
                                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                            <a href="#" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors duration-200">Learn More <span aria-hidden="true">→</span></a>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-gray-800" aria-labelledby="footer-heading">
                <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                    <div class="mt-8 border-t border-gray-700 pt-8 text-center">
                        <p class="text-sm leading-5 text-gray-400">&copy; {{ date('Y') }} VitaLink, Inc. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>