<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 p-4">
            <div class="w-full max-w-4xl bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
                
                <!-- Left Panel (Image) - This will now show the correct image based on the page -->
                <div class="hidden md:block">
                    {{-- This code checks the current route and displays the appropriate background image --}}
                    @if(request()->routeIs('agency.register') || request()->routeIs('register'))
                        {{-- Use the new, TALLER image for the registration page --}}
                        <img src="{{ asset('images/register-bg.jpg') }}" alt="Registration Background" class="w-full h-full object-cover">
                    @else
                        {{-- Use the original, WIDER image for the login page --}}
                        <img src="{{ asset('images/login-bg.jpg') }}" alt="Login Background" class="w-full h-full object-cover">
                    @endif
                </div>

                <!-- Right Panel (Form) -->
                <div class="p-8 md:p-12">
                     <div class="flex justify-center mb-4">
                        <a href="/">
                            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                        </a>
                    </div>
                    {{ $slot }}
                </div>

            </div>
        </div>
    </body>
</html>

