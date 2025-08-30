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
</head>
<body class="antialiased bg-gray-900 text-gray-100">
    <div class="container mx-auto px-4 py-16">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-4">Choose the plan that's right for you</h1>
            <p class="text-lg text-gray-400">Simple, transparent pricing for agencies of all sizes.</p>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
                <div class="bg-gray-800 rounded-lg shadow-lg p-8 flex flex-col">
                    <h3 class="text-2xl font-semibold mb-2">{{ $plan['name'] }}</h3>
                    <p class="text-4xl font-bold mb-6">${{ $plan['price'] }}<span class="text-lg font-normal text-gray-400">/mo</span></p>
                    
                    <ul class="space-y-4 mb-8 flex-grow">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    
                    <a href="#" class="mt-auto block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300">
                        Get Started
                    </a>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-16">
            <a href="{{ url('/') }}" class="text-indigo-400 hover:text-indigo-300">&larr; Back to Home</a>
        </div>
    </div>
</body>
</html>