@extends('layouts.feature')

@section('title', 'Intelligent Scheduling')

@section('content')

    {{-- Hero Section --}}
    <section class="bg-indigo-700 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <img src="{{ asset('images/icon-schedule.png') }}" alt="Scheduling Icon" class="h-20 w-20 mx-auto mb-4 bg-white p-3 rounded-full shadow-lg">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Intelligent Scheduling</h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-indigo-100">The right caregiver, for the right client, at the right time. Every time.</p>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="py-24 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                
                {{-- Left Column: Image --}}
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <img src="{{ asset('images/feature-scheduling.png') }}" alt="A screenshot of the VitaLink scheduling calendar" class="w-full h-full object-cover">
                </div>

                {{-- Right Column: Text Content --}}
                <div class="text-gray-800 dark:text-gray-200">
                    <h2 class="text-3xl font-bold mb-4">Simplify Your Operations</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        Stop juggling spreadsheets and whiteboards. VitaLink's intuitive drag-and-drop calendar makes building and managing schedules effortless. Our smart-matching system helps you instantly find the most qualified and available caregiver for each client's specific needs.
                    </p>
                    
                    <ul class="space-y-4 text-lg">
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-indigo-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Intuitive Calendar:</strong> Drag-and-drop to create, update, and assign shifts in seconds.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-indigo-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Smart Matching:</strong> Automatically filter caregivers by availability, skills, and client preferences.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-indigo-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Real-time Updates:</strong> Prevent double-booking and instantly notify caregivers of new shifts or changes.</span>
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