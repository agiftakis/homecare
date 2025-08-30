<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VitaLink - Home Care Management Software</title>
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
            <a href="{{ url('/') }}" class="flex items-center space-x-3">
                <img src="{{ asset('images/vitalink-logo.png') }}" alt="VitaLink Logo" class="h-20 w-auto">
                <span class="text-2xl font-bold text-gray-800 dark:text-white">VitaLink</span>
            </a>
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('pricing') }}"
                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition duration-300">Pricing</a>
                <a href="{{ route('login') }}"
                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition duration-300">Log
                    In</a>
                <a href="{{ route('register') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-lg transition duration-300 shadow-md">
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
                class="md:hidden absolute top-0 left-0 w-full h-screen bg-gray-900 bg-opacity-95 z-50 flex flex-col items-center justify-center"
                style="display: none;">
                <button @click="open = false" class="absolute top-6 right-6 text-white text-3xl">&times;</button>
                <a href="{{ route('pricing') }}" class="text-2xl text-white py-4">Pricing</a>
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
        <section class="relative h-[60vh] md:h-[80vh] flex items-center justify-center text-center text-white">
            <div class="absolute inset-0 bg-black opacity-50"></div>
            <img src="{{ asset('images/hero-background.jpg') }}" alt="Caregiver with a senior patient"
                class="w-full h-full object-cover">
            <div class="relative z-10 p-6">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">The Future of Home Care Management</h1>
                <p class="text-lg md:text-xl max-w-3xl mx-auto mb-8">Streamline your scheduling, simplify client
                    management, and empower your caregivers with VitaLink.</p>
                <a href="{{ route('register') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300 shadow-lg">
                    Get started for free
                </a>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-gray-50 dark:bg-gray-800">
            <div class="container mx-auto px-6 text-center">
                <span class="text-indigo-500 font-semibold">All-In-One Solution</span>
                <h2 class="text-3xl md:text-4xl font-bold mt-2 mb-4 text-gray-900 dark:text-white">A Customizable
                    Software Solution</h2>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-16">VitaLink provides the tools
                    you need to operate efficiently, stay compliant, and provide the best possible care.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-lg shadow-lg">
                        <img src="{{ asset('images/icon-schedule.png') }}" alt="Scheduling Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-2xl font-bold mb-2">Intelligent Scheduling</h3>
                        <p class="text-gray-600 dark:text-gray-400">Easily create, manage, and update shifts with our
                            intuitive calendar interface. Match the right caregiver to the right client, every time.</p>
                        <a href="#"
                            class="mt-6 inline-block text-indigo-500 font-semibold hover:text-indigo-400">Learn more
                            &rarr;</a>
                    </div>
                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-lg shadow-lg">
                        <img src="{{ asset('images/icon-client.png') }}" alt="Client Management Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-2xl font-bold mb-2">Centralized Client Data</h3>
                        <p class="text-gray-600 dark:text-gray-400">Keep all client information, care plans, and contact
                            details organized and accessible in one secure location.</p>
                        <a href="#"
                            class="mt-6 inline-block text-indigo-500 font-semibold hover:text-indigo-400">Learn more
                            &rarr;</a>
                    </div>
                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-lg shadow-lg">
                        <img src="{{ asset('images/icon-communication.png') }}" alt="Communication Icon"
                            class="h-16 w-16 mx-auto mb-4">
                        <h3 class="text-2xl font-bold mb-2">Seamless Communication</h3>
                        <p class="text-gray-600 dark:text-gray-400">Our platform facilitates clear and secure
                            communication between your office, caregivers, and clients.</p>
                        <a href="#"
                            class="mt-6 inline-block text-indigo-500 font-semibold hover:text-indigo-400">Learn more
                            &rarr;</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-black text-white py-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; {{ date('Y') }} VitaLink, Inc. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
