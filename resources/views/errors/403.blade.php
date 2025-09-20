<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <div class="mx-auto h-24 w-24 text-red-600">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <h1 class="mt-6 text-6xl font-bold text-gray-900 dark:text-gray-100">403</h1>
                <h2 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $title ?? 'Access Denied' }}
                </h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    You don't have permission to access this resource. This might be because:
                </p>
                <ul class="mt-4 text-sm text-gray-500 dark:text-gray-400 space-y-1">
                    <li>• You're not logged in with the correct account</li>
                    <li>• Your account doesn't have the required permissions</li>
                    <li>• You're trying to access another agency's data</li>
                </ul>
            </div>

            <div class="space-y-4">
                @auth
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('dashboard') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go to Dashboard
                        </a>
                        <button onclick="history.back()" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Go Back
                        </button>
                    </div>
                    
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Current Account:</strong> {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                            @if(auth()->user()->agency)
                                <br><strong>Agency:</strong> {{ auth()->user()->agency->name }}
                            @endif
                        </p>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('login') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Sign In to Continue
                        </a>
                        <a href="{{ route('welcome') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go Home
                        </a>
                    </div>
                @endauth
            </div>

            <div class="mt-8 text-sm text-gray-500 dark:text-gray-400">
                <p>Need help with access? <a href="mailto:support@vitalink.com" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Contact Support</a></p>
            </div>
        </div>
    </div>
</x-guest-layout>