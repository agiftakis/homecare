<x-guest-layout>
    <form method="POST" action="{{ route('agency.store') }}">
        @csrf

        <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-200 mb-6">Create Your Agency Account</h2>

        {{-- ✅ REMOVED: No longer need plan parameter or display --}}
        {{-- <input type="hidden" name="plan" value="{{ $plan }}"> --}}
        {{-- <div class="mb-4 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                You've selected the <span class="font-bold mx-1">{{ ucfirst($plan) }}</span> Plan
            </span>
        </div> --}}

        <div>
            <x-input-label for="agency_name" :value="__('Agency Name')" />
            <x-text-input id="agency_name" class="block mt-1 w-full" type="text" name="agency_name" :value="old('agency_name')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('agency_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="timezone" :value="__('Timezone')" />

            <p class="text-sm text-red-600 dark:text-red-400 mb-2">
                <strong>Important:</strong> Please select the primary timezone for your agency's area of operation. This is critical for accurate visit verification.
            </p>

            <select id="timezone" name="timezone" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="" disabled selected>Select your timezone</option>

                @foreach ($northAmericaTimezones as $timezone)
                    <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>
                        {{ $timezone }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
        </div>
        
        <div class="mt-4">
            <x-input-label for="name" :value="__('Your Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already have an account?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>