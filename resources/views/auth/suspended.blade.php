<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full bg-white dark:bg-gray-800 shadow-2xl rounded-xl overflow-hidden">
            
            {{-- Header with Warning Icon --}}
            <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-10 text-center">
                <div class="flex justify-center mb-4">
                    <div class="bg-white/20 rounded-full p-4">
                        <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-white">Account Suspended</h1>
                <p class="mt-2 text-red-100 text-sm sm:text-base">Access to VitaLink services has been temporarily disabled</p>
            </div>

            {{-- Main Content --}}
            <div class="px-6 sm:px-10 py-10">
                <div class="text-center mb-8">
                    <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
                        Your account has been suspended and access to VitaLink services has been temporarily disabled.
                    </p>
                </div>
                
                {{-- Contact Information Card --}}
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 rounded-xl p-6 sm:p-8 mb-8 border border-blue-100 dark:border-gray-600">
                    <div class="flex items-start mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                Need Help?
                            </h3>
                            <p class="text-base text-gray-600 dark:text-gray-300 mb-4">
                                If you believe this is an error or would like to resolve this issue, please contact our support team:
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-3 mb-4">
                        <div class="flex items-center justify-center space-x-2 bg-white dark:bg-gray-800 px-4 py-3 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:vitalink.notifications1@gmail.com" 
                                class="text-base sm:text-lg font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition duration-150">
                                vitalink.notifications1@gmail.com
                            </a>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Our team typically responds within 24-48 hours</span>
                    </div>
                </div>

                {{-- Sign Out Button --}}
                <div class="text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline-block">
                        @csrf
                        <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-gray-800 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition duration-200 ease-in-out transform hover:-translate-y-0.5">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 sm:px-10 py-6 text-center border-t border-gray-200 dark:border-gray-600">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    Thank you for your understanding
                </p>
                <p class="text-base font-semibold text-gray-800 dark:text-gray-200">
                    The VitaLink Team
                </p>
            </div>
        </div>

        {{-- Bottom Branding --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                © {{ date('Y') }} VitaLink Healthcare Management System
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                All rights reserved
            </p>
        </div>
    </div>
</body>
</html>