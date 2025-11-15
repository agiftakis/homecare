@extends('layouts.feature')

@section('title', 'Exceptional Billing & Invoicing')

@section('content')

    {{-- Hero Section --}}
    <section class="bg-emerald-600 text-white py-20">
        <div class="container mx-auto px-6 text-center">
            <img src="{{ asset('images/icon-billing.png') }}" alt="Billing Icon" class="h-20 w-20 mx-auto mb-4 bg-white p-3 rounded-full shadow-lg">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Exceptional Billing & Invoicing</h1>
            <p class="text-xl md:text-2xl max-w-3xl mx-auto text-emerald-100">Get paid faster and eliminate billing errors with our automated invoicing system.</p>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="py-24 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                
                {{-- Left Column: Text Content --}}
                <div class="text-gray-800 dark:text-gray-200 mb-15">
                    <h2 class="text-3xl font-bold mb-4">Automate Your Revenue Cycle</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        Manually creating invoices is tedious and error-prone. VitaLink's billing module automatically generates professional, accurate invoices directly from your verified visit data. Track payment statuses, send reminders, and get a clear view of your agency's financial health.
                    </p>
                    
                    <ul class="space-y-4 text-lg">
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Auto-Generate Invoices:</strong> Create detailed, itemized invoices from completed shifts in just one click.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Track Payment Status:</strong> Easily manage your accounts receivable by tracking invoices as Draft, Sent, Paid, or Void.</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-6 h-6 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="dark:text-gray-300"><strong class="dark:text-white">Revenue Reporting:</strong> Run detailed reports to track revenue, outstanding balances, and billing efficiency.</span>
                        </li>
                    </ul>

                    <a href="{{ route('sales.contact') }}" class="mt-8 mb-10 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300">
                        Request a Consultation
                    </a>
                </div>

                {{-- Right Column: Image --}}
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden md:order-first">
                    <img src="{{ asset('images/feature-billing.png') }}" alt="A screenshot of the VitaLink billing and invoicing system" class="w-full h-full object-cover">
                </div>

            </div>
        </div>
    </section>

@endsection