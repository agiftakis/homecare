<x-guest-layout>
    <div class="text-center">
        <h2 class="text-2xl font-bold text-red-600 dark:text-red-400 mb-4">Link Expired or Invalid</h2>
        <p class="text-gray-600 dark:text-gray-400">
            This password setup link is either invalid, has expired, or has already been used. Please contact your agency administrator to request a new link.
        </p>
        <div class="mt-6">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Go to Login Page
            </a>
        </div>
    </div>
</x-guest-layout>