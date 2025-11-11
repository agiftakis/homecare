<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Account Activation Required') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <div class->"text-center">
                        <svg class="mx-auto h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-2xl font-semibold text-center text-gray-800 dark:text-gray-200">
                        Thank You for Registering!
                    </h3>

                    <p class="mt-4 text-gray-700 dark:text-gray-300">
                        Hello, <span class="font-medium">{{ $user->name }}</span>.
                    </p>
                    
                    <p class="mt-4 text-gray-700 dark:text-gray-300">
                        Your account for <span class="font-medium">{{ $agencyName }}</span> has been successfully created but is not yet active.
                    </p>

                    <p class="mt-4 text-gray-700 dark:text-gray-300">
                        To activate your agency's access to VitaLink, please contact our administrative team directly for payment and setup instructions.
                    </p>

                    <div class="mt-6 p-4 bg-blue-50 dark:bg-gray-700 rounded-lg text-center">
                        <p class="text-lg text-gray-800 dark:text-gray-200">
                            Please email us at:
                        </p>
                        <a href="mailto:{{ $contactEmail }}" class="text-xl font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $contactEmail }}
                        </a>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            We will activate your account upon receiving your request.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>