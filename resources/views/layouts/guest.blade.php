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
            <div class="w-full max-w-4xl bg-white dark:bg-gray-800 shadow-2xl rounded-lg grid grid-cols-1 md:grid-cols-2 overflow-hidden">
    
                <!-- Left Panel (Image) -->
                <div class="hidden md:block">
                    @php
                        $imageName = 'login-bg.jpg'; // Default
                        if (request()->routeIs('agency.register')) {
                            $imageName = 'register-bg.jpg';
                        } elseif (request()->routeIs('subscription.create')) {
                            $imageName = 'payment-bg.jpg';
                        }
                    @endphp
                    <img src="{{ asset('images/' . $imageName) }}" alt="VitaLink" class="w-full h-full object-cover">
                </div>
    
                <!-- Right Panel (Form) -->
                <div class="p-8 flex flex-col justify-center">
                    <div class="flex justify-center mb-4">
                        <a href="/">
                            <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo" class="h-16 w-auto">
                        </a>
                    </div>
                    {{ $slot }}
                </div>
    
            </div>
        </div>
    </body>
</html>

