<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- **THE FIX: Agency Selection for SuperAdmin** -->
                                @if (Auth::user()->role === 'super_admin' && isset($agencies) && $agencies->count() > 0)
                                <div class="md:col-span-2">
                                    <x-input-label for="agency_id" :value="__('Agency')" />
                                    <select id="agency_id" name="agency_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">Select an Agency...</option>
                                        @foreach ($agencies as $agency)
                                            <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('agency_id')" class="mt-2" />
                                </div>
                                @endif

                                <!-- First Name -->
                                <div>
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <x-input-label for="last_name" :value="__('Last Name')" />
                                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>

                                <!-- Email Address -->
                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <!-- Phone Number -->
                                <div>
                                    <x-input-label for="phone_number" :value="__('Phone Number')" />
                                    <x-text-input id="phone_number" class="block mt-1 w-full" type="text" name="phone_number" :value="old('phone_number')" required autocomplete="tel" />
                                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                                </div>
                                
                                <!-- Date of Birth -->
                                <div>
                                    <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                    <x-text-input id="date_of_birth" class="block mt-1 w-full dark:[color-scheme:dark]" type="date" name="date_of_birth" :value="old('date_of_birth')" required autocomplete="bday" />
                                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                                </div>

                                <!-- Profile Picture -->
                                <div>
                                    <x-input-label for="profile_picture" :value="__('Profile Picture (Optional)')" />
                                    <input id="profile_picture" name="profile_picture" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:[color-scheme:dark] mt-1">
                                    <x-input-error :messages="$errors->get('profile_picture')" class="mt-2" />
                                </div>

                                <!-- Address (Spans full width) -->
                                <div class="md:col-span-2">
                                    <x-input-label for="address" :value="__('Address')" />
                                    <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required autocomplete="street-address">{{ old('address') }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <!-- Care Plan (Spans full width) -->
                                <div class="md:col-span-2">
                                    <x-input-label for="care_plan" :value="__('Care Plan (Optional)')" />
                                    <textarea id="care_plan" name="care_plan" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('care_plan') }}</textarea>
                                    <x-input-error :messages="$errors->get('care_plan')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information Section -->
                        <div class="mb-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Medical Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Current Medications -->
                                <div class="md:col-span-2">
                                    <x-input-label for="current_medications" :value="__('Current Medications (Optional)')" />
                                    <textarea id="current_medications" name="current_medications" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="List current medications...">{{ old('current_medications') }}</textarea>
                                    <x-input-error :messages="$errors->get('current_medications')" class="mt-2" />
                                </div>

                                <!-- Discontinued Medications -->
                                <div class="md:col-span-2">
                                    <x-input-label for="discontinued_medications" :value="__('Discontinued Medications (Optional)')" />
                                    <textarea id="discontinued_medications" name="discontinued_medications" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="List previously used medications...">{{ old('discontinued_medications') }}</textarea>
                                    <x-input-error :messages="$errors->get('discontinued_medications')" class="mt-2" />
                                </div>

                                <!-- Recent Hospitalizations -->
                                <div class="md:col-span-2">
                                    <x-input-label for="recent_hospitalizations" :value="__('Recent Hospitalizations (Optional)')" />
                                    <textarea id="recent_hospitalizations" name="recent_hospitalizations" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="List recent hospital visits...">{{ old('recent_hospitalizations') }}</textarea>
                                    <x-input-error :messages="$errors->get('recent_hospitalizations')" class="mt-2" />
                                </div>

                                <!-- Current and Concurrent Dx -->
                                <div class="md:col-span-2">
                                    <x-input-label for="current_concurrent_dx" :value="__('Current and Concurrent Diagnoses (Optional)')" />
                                    <textarea id="current_concurrent_dx" name="current_concurrent_dx" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="List current diagnoses...">{{ old('current_concurrent_dx') }}</textarea>
                                    <x-input-error :messages="$errors->get('current_concurrent_dx')" class="mt-2" />
                                </div>

                                <!-- Designated POA -->
                                <div>
                                    <x-input-label for="designated_poa" :value="__('Designated Power of Attorney (Optional)')" />
                                    <x-text-input id="designated_poa" class="block mt-1 w-full" type="text" name="designated_poa" :value="old('designated_poa')" placeholder="Name of POA" />
                                    <x-input-error :messages="$errors->get('designated_poa')" class="mt-2" />
                                </div>

                                <!-- Fall Risk -->
                                <div>
                                    <x-input-label for="fall_risk" :value="__('Fall Risk (Optional)')" />
                                    <select id="fall_risk" name="fall_risk" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select...</option>
                                        <option value="yes" {{ old('fall_risk') == 'yes' ? 'selected' : '' }}>Yes</option>
                                        <option value="no" {{ old('fall_risk') == 'no' ? 'selected' : '' }}>No</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('fall_risk')" class="mt-2" />
                                </div>

                                <!-- Current Routines AM/PM -->
                                <div class="md:col-span-2">
                                    <x-input-label for="current_routines_am_pm" :value="__('Current Routines (AM/PM) (Optional)')" />
                                    <textarea id="current_routines_am_pm" name="current_routines_am_pm" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Describe daily routines...">{{ old('current_routines_am_pm') }}</textarea>
                                    <x-input-error :messages="$errors->get('current_routines_am_pm')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Add Client') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

