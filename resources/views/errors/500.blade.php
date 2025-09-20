<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div>
                <div class="mx-auto h-24 w-24 text-red-600">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.982 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
                <h1 class="mt-6 text-6xl font-bold text-gray-900 dark:text-gray-100">500</h1>
                <h2 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $title ?? 'Server Error' }}
                </h2>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Something went wrong on our end. Our team has been automatically notified and is working to fix this issue.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="window.location.reload()" 
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Try Again
                    </button>
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('welcome') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Go Home
                        </a>
                    @endauth
                </div>

                <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                What you can do while we fix this:
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Wait a few minutes and try again</li>
                                    <li>Check if you have a stable internet connection</li>
                                    <li>Try accessing a different page to see if the issue persists</li>
                                    <li>Contact support if the problem continues</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-sm text-gray-500 dark:text-gray-400">
                <p>
                    Error ID: {{ Str::random(8) }} | 
                    Time: {{ now()->format('M j, Y g:i A T') }}
                </p>
                <p class="mt-2">
                    Persistent issues? <a href="mailto:support@vitalink.com" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Contact Support</a>
                </p>
            </div>

            @if(app()->hasDebugModeEnabled() && isset($exception))
                <details class="mt-8 text-left">
                    <summary class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                        Debug Information (Development Only)
                    </summary>
                    <div class="mt-2 p-4 bg-gray-100 dark:bg-gray-800 rounded-md text-xs">
                        <p><strong>Exception:</strong> {{ get_class($exception) }}</p>
                        <p><strong>File:</strong> {{ $exception->getFile() }}</p>
                        <p><strong>Line:</strong> {{ $exception->getLine() }}</p>
                        <p><strong>Message:</strong> {{ $exception->getMessage() }}</p>
                    </div>
                </details>
            @endif
        </div>
    </div>
</x-guest-layout>