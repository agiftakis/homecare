@extends('layouts.feature')

@section('title', 'Seamless Communication')

@section('content')

    {{-- Hero Section --}}
    <section class="bg-blue-600 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <img src="{{ asset('images/icon-communication.png') }}" alt="Communication Icon" class="h-20 w-20 mx-auto mb-4 bg-white p-3 rounded-full shadow-lg">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Seamless Communication</h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-blue-100">Keep your entire team and your clients connected, informed, and in sync.</p>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="py-24 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                
                {{-- Left Column: Image --}}
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <img src="{{ asset('images/feature-communication.png') }}" alt="A screenshot of the VitaLink communication portal" class="w-full h-full object-cover">
                </div>

                {{-- Right Column: Text Content --}}
                <div class="text-gray-800 dark:text-gray-200">
                    <h2 class="text-3xl font-bold mb-4">Connect Your Care Circle</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        Effective care depends on clear communication. VitaLink bridges the gap between your office staff, your caregivers in the field, and your clients. From real-time shift alerts to secure progress notes, everyone stays on the same page.
                    </p>
                    
                    <ul class="space-y-4 text-lg">
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Real-Time Notifications:</strong> Instantly alert caregivers to new shifts, schedule changes, and important updates.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Centralized Progress Notes:</strong> Caregivers log notes directly in the app, providing a live feed of care delivery.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Client & Family Portals:</strong> Give clients and their families read-only access to schedules and care notes for total transparency.</span>
                        </li>
                    </ul>

                    <a href="{{ route('sales.contact') }}" class="mt-8 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300">
                        Request a Consultation
                    </a>
                </div>

            </div>
        </div>
    </section>

@endsection