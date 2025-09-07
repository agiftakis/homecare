<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Caregiver: {{ $caregiver->first_name }} {{ $caregiver->last_name }} (Agency:
                {{ $caregiver->agency->name }})
            </h2>
            <a href="{{ route('superadmin.caregivers.index') }}"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                &larr; Back to All Caregivers
            </a>
        </div>
    </x-slot>
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg relative mt-6"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
    </div>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('superadmin.caregivers.update', $caregiver) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Basic Information
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <x-text-input id="first_name" class="block mt-1 w-full" type="text"
                                        name="first_name" :value="old('first_name', $caregiver->first_name)" required autofocus />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="last_name" :value="__('Last Name')" />
                                    <x-text-input id="last_name" class="block mt-1 w-full" type="text"
                                        name="last_name" :value="old('last_name', $caregiver->last_name)" required />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="email" :value="__('Email Address')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email', $caregiver->email)" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="phone_number" :value="__('Phone Number')" />
                                    <x-text-input id="phone_number" class="block mt-1 w-full" type="tel"
                                        name="phone_number" :value="old('phone_number', $caregiver->phone_number)" required />
                                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                    <x-text-input id="date_of_birth" class="block mt-1 w-full dark:[color-scheme:dark]"
                                        type="date" name="date_of_birth" :value="old(
                                            'date_of_birth',
                                            \Carbon\Carbon::parse($caregiver->date_of_birth)->format('Y-m-d'),
                                        )" required />
                                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="certifications" :value="__('Certifications (Optional)')" />
                                    <textarea id="certifications" name="certifications" rows="5"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('certifications', $caregiver->certifications) }}</textarea>
                                    <x-input-error :messages="$errors->get('certifications')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="profile_picture" :value="__('Profile Picture (Optional)')" />
                                    <div class="mt-2 flex items-center gap-x-3">
                                        @if ($caregiver->profile_picture_url)
                                            <img class="h-16 w-16 rounded-full object-cover"
                                                src="{{ $caregiver->profile_picture_url }}"
                                                alt="Current profile picture">
                                        @else
                                            <div
                                                class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <svg class="h-10 w-10 text-gray-400" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <input id="profile_picture" name="profile_picture" type="file"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:[color-scheme:dark]">
                                    </div>
                                    <x-input-error :messages="$errors->get('profile_picture')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="mb-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Documents (All
                                Optional)</h3>

                            <div class="grid grid-cols-1 gap-6">
                                <!-- Certifications Document -->
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700">
                                    <x-input-label for="certifications_document" :value="__('Certifications Document')" />
                                    @if ($caregiver->hasCertifications())
                                        <div class="mt-2 mb-3 flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-green-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $caregiver->certifications_display }}</p>
                                                <a href="{{ $caregiver->certifications_url }}" target="_blank"
                                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Download
                                                    Document →</a>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-3">No document
                                            uploaded yet.</p>
                                    @endif
                                    <input id="certifications_document" name="certifications_document" type="file"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 dark:file:bg-green-900 file:text-green-700 dark:file:text-green-300 hover:file:bg-green-100 dark:[color-scheme:dark]"
                                        accept=".pdf,.docx,.jpeg,.jpg,.png,.gif">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Accepted formats: PDF,
                                        DOCX, JPG, PNG, GIF (Max: 10MB)</p>
                                    <x-input-error :messages="$errors->get('certifications_document')" class="mt-2" />
                                </div>

                                <!-- Professional Licenses Document -->
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700">
                                    <x-input-label for="professional_licenses_document" :value="__('Professional Licenses Document')" />
                                    @if ($caregiver->hasProfessionalLicenses())
                                        <div class="mt-2 mb-3 flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-blue-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $caregiver->professional_licenses_display }}</p>
                                                <a href="{{ $caregiver->professional_licenses_url }}" target="_blank"
                                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Download
                                                    Document →</a>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-3">No document
                                            uploaded yet.</p>
                                    @endif
                                    <input id="professional_licenses_document" name="professional_licenses_document"
                                        type="file"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:[color-scheme:dark]"
                                        accept=".pdf,.docx,.jpeg,.jpg,.png,.gif">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Accepted formats: PDF,
                                        DOCX, JPG, PNG, GIF (Max: 10MB)</p>
                                    <x-input-error :messages="$errors->get('professional_licenses_document')" class="mt-2" />
                                </div>

                                <!-- State/Province ID Document -->
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700">
                                    <x-input-label for="state_province_id_document" :value="__('State/Province ID Document')" />
                                    @if ($caregiver->hasStateProvinceId())
                                        <div class="mt-2 mb-3 flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <svg class="h-8 w-8 text-purple-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $caregiver->state_province_id_display }}</p>
                                                <a href="{{ $caregiver->state_province_id_url }}" target="_blank"
                                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Download
                                                    Document →</a>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-3">No document
                                            uploaded yet.</p>
                                    @endif
                                    <input id="state_province_id_document" name="state_province_id_document"
                                        type="file"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 dark:file:bg-purple-900 file:text-purple-700 dark:file:text-purple-300 hover:file:bg-purple-100 dark:[color-scheme:dark]"
                                        accept=".pdf,.docx,.jpeg,.jpg,.png,.gif">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Accepted formats: PDF,
                                        DOCX, JPG, PNG, GIF (Max: 10MB)</p>
                                    <x-input-error :messages="$errors->get('state_province_id_document')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <x-danger-button type="button" x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-caregiver-deletion')">
                                    {{ __('Delete Caregiver') }}
                                </x-danger-button>
                            </div>

                            <div class="flex items-center">
                                <a href="{{ route('superadmin.caregivers.index') }}"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </a>
                                <x-primary-button class="ms-4">
                                    {{ __('Update Caregiver') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="confirm-caregiver-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('superadmin.caregivers.destroy', $caregiver) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Are you sure you want to delete this caregiver?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once this caregiver is deleted, all of their data will be permanently removed. This action cannot be
                undone.
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Caregiver') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
