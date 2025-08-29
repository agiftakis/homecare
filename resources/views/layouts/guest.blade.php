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
        <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div class="w-full sm:max-w-4xl mt-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg grid grid-cols-1 md:grid-cols-2">
                <!-- Left Panel (Image) -->
                <div class="hidden md:block">
                    <img src="{{ asset('images/login-bg.jpg') }}" alt="Background" class="w-full h-full object-cover">
                </div>

                <!-- Right Panel (Form) -->
                <div class="p-6">
                    <div class="flex justify-center mb-4">
                        <a href="/">
                            <x-application-logo class="w-29 h-45 fill-current text-gray-500" />
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
