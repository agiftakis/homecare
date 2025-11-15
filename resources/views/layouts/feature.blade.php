<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- The title will be set by the content page --}}
    <title>@yield('title') - VitaLink</title> 
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">

    {{-- Simplified Header for feature pages --}}
    <header class="bg-white dark:bg-gray-900 shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo" class="h-16 md:h-16 w-auto">
                <span class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">VitaLink</span>
            </a>
            <div class="flex items-center space-x-6">
                <a href="{{ route('welcome') }}"
                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition duration-300">
                    &larr; Back to Home
                </a>
            </div>
        </nav>
    </header>

    {{-- Page Content Slot --}}
    <main>
        @yield('content')
    </main>

    {{-- Standard Footer --}}
    <footer class="bg-gray-800 dark:bg-black text-white py-12">
        <div class="container mx-auto px-2 sm:px-6 text-center">
            <div class="flex items-center justify-center space-x-[5px]">
                <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo"
                    class="h-8 w-auto max-w-full sm:h-10">
                <p class="text-sm sm:text-base whitespace-nowT">&copy; {{ date('Y') }} VitaLink, Inc. All rights
                    reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>