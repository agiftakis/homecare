<div x-data="{ showSuccess: {{ session('success') ? 'true' : 'false' }} }">

    <x-guest-layout>
        <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-4">
            Request a Consultation
        </h2>
        <p class="text-center text-md text-gray-600 dark:text-gray-300 mb-6">
            We're excited to show you how VitaLink can revolutionize your agency. Please fill out the form below to get
            in touch with our team.
        </p>

        <div
            class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg p-5 mb-8">
            <h3 class="text-xl font-semibold text-indigo-900 dark:text-indigo-200 mb-2">Simple, Lifetime Pricing</h3>
            <p class="text-gray-700 dark:text-gray-300">
                VitaLink offers a one-time payment for a lifetime subscription.
            </p>
            <p class="text-4xl font-bold text-gray-900 dark:text-white mt-3">
                $1987 <span class="text-lg font-medium text-gray-600 dark:text-gray-400">USD / one-time</span>
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                This includes full access to all features, all future updates, and dedicated support. No monthly fees,
                ever.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative"
                role="alert">
                <strong class="font-bold">Oops!</strong>
                <span class="block sm:inline">Please correct the errors below.</span>
                <ul class="mt-3 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sales.contact.submit') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <x-input-label for="agency_name" value="Agency Name *" />
                    <x-text-input id="agency_name" name="agency_name" type="text" class="mt-1 block w-full"
                        :value="old('agency_name')" required autofocus />
                </div>

                <div>
                    <x-input-label for="contact_name" value="Your Name *" />
                    <x-text-input id="contact_name" name="contact_name" type="text" class="mt-1 block w-full"
                        :value="old('contact_name')" required />
                </div>

                <div>
                    <x-input-label for="contact_email" value="Contact Email *" />
                    <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full"
                        :value="old('contact_email')" required />
                </div>

                <div>
                    <x-input-label for="location" value="Agency Location (City, State/Country) *" />
                    <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                        :value="old('location')" required />
                </div>

                <div>
                    <x-input-label for="message" value="Tell us about your agency (Optional)" />
                    <textarea id="message" name="message" rows="4"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('message') }}</textarea>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Please include any questions, your agency size, or specific features you're interested in.
                    </p>
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button class="w-full text-center py-3 text-lg">
                        Submit Request
                    </x-primary-button>
                </div>
            </div>
        </form>
    </x-guest-layout>


    <div x-show="showSuccess" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" style="display: none;">

        <div @click.away="showSuccess = false" x-show="showSuccess" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-8 m-4">

            <button @click="showSuccess = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div
                class="flex justify-center items-center h-16 w-16 mx-auto bg-green-100 rounded-full border-4 border-green-300 dark:bg-green-900/30 dark:border-green-600">
                <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h3 class="text-2xl font-semibold text-center text-gray-300 mt-6 mb-2">
                Submission Received!
            </h3>
            <p class="text-center text-gray-600 dark:text-gray-300">
                Thank you for your interest in VitaLink. A member of our team will be in touch very soon to schedule
                your consultation and get you started with a 14-day trial.
            </p>

            <div class="mt-8 text-center">


                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                    style="text-decoration: none;"> Back to Home
                </a>
            </div>
        </div>
    </div>

</div>
