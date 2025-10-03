<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Client: {{ $client->first_name }} {{ $client->last_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Client Information Form --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="mb-8">
                            <h3
                                class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                                Basic Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <x-text-input id="first_name" class="block mt-1 w-full" type="text"
                                        name="first_name" :value="old('first_name', $client->first_name)" required autofocus />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="last_name" :value="__('Last Name')" />
                                    <x-text-input id="last_name" class="block mt-1 w-full" type="text"
                                        name="last_name" :value="old('last_name', $client->last_name)" required />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="email" :value="__('Email Address')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                        :value="old('email', $client->user->email ?? $client->email)" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone_number" :value="__('Phone Number')" />
                                    <x-text-input id="phone_number" class="block mt-1 w-full" type="tel"
                                        name="phone_number" :value="old('phone_number', $client->phone_number)" required />
                                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                                    <x-text-input id="date_of_birth" class="block mt-1 w-full dark:[color-scheme:dark]"
                                        type="date" name="date_of_birth" :value="old(
                                            'date_of_birth',
                                            \Carbon\Carbon::parse($client->date_of_birth)->format('Y-m-d'),
                                        )" required />
                                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="address" :value="__('Address')" />
                                    <textarea id="address" name="address" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required>{{ old('address', $client->address) }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="care_plan" :value="__('Care Plan (Optional)')" />
                                    <textarea id="care_plan" name="care_plan" rows="4"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('care_plan', $client->care_plan) }}</textarea>
                                    <x-input-error :messages="$errors->get('care_plan')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="profile_picture" :value="__('Profile Picture (Optional)')" />
                                    <div class="mt-2 flex items-center gap-x-3">
                                        @if ($client->profile_picture_url)
                                            <img class="h-16 w-16 rounded-full object-cover"
                                                src="{{ $client->profile_picture_url }}" alt="Current profile picture">
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

                        <!-- Medical Information Section -->
                        <div class="mb-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3
                                class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                                Medical Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div class="md:col-span-2">
                                    <x-input-label for="current_medications" :value="__('Current Medications (Optional)')" />
                                    <textarea id="current_medications" name="current_medications" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        placeholder="List current medications...">{{ old('current_medications', $client->current_medications) }}</textarea>
                                    <x-input-error :messages="$errors->get('current_medications')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="discontinued_medications" :value="__('Discontinued Medications (Optional)')" />
                                    <textarea id="discontinued_medications" name="discontinued_medications" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        placeholder="List previously used medications...">{{ old('discontinued_medications', $client->discontinued_medications) }}</textarea>
                                    <x-input-error :messages="$errors->get('discontinued_medications')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="recent_hospitalizations" :value="__('Recent Hospitalizations (Optional)')" />
                                    <textarea id="recent_hospitalizations" name="recent_hospitalizations" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        placeholder="List recent hospital visits...">{{ old('recent_hospitalizations', $client->recent_hospitalizations) }}</textarea>
                                    <x-input-error :messages="$errors->get('recent_hospitalizations')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="current_concurrent_dx" :value="__('Current and Concurrent Diagnoses (Optional)')" />
                                    <textarea id="current_concurrent_dx" name="current_concurrent_dx" rows="3"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        placeholder="List current diagnoses...">{{ old('current_concurrent_dx', $client->current_concurrent_dx) }}</textarea>
                                    <x-input-error :messages="$errors->get('current_concurrent_dx')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="designated_poa" :value="__('Designated Power of Attorney (Optional)')" />
                                    <x-text-input id="designated_poa" class="block mt-1 w-full" type="text"
                                        name="designated_poa" :value="old('designated_poa', $client->designated_poa)" placeholder="Name of POA" />
                                    <x-input-error :messages="$errors->get('designated_poa')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="fall_risk" :value="__('Fall Risk (Optional)')" />
                                    <select id="fall_risk" name="fall_risk"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Select...</option>
                                        <option value="yes"
                                            {{ old('fall_risk', $client->fall_risk) == 'yes' ? 'selected' : '' }}>Yes
                                        </option>
                                        <option value="no"
                                            {{ old('fall_risk', $client->fall_risk) == 'no' ? 'selected' : '' }}>No
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('fall_risk')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="current_routines_am_pm" :value="__('Current Routines (AM/PM) (Optional)')" />
                                    <textarea id="current_routines_am_pm" name="current_routines_am_pm" rows="4"
                                        class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        placeholder="Describe daily routines...">{{ old('current_routines_am_pm', $client->current_routines_am_pm) }}</textarea>
                                    <x-input-error :messages="$errors->get('current_routines_am_pm')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div>
                                <x-danger-button type="button" x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-client-deletion')">
                                    {{ __('Delete Client') }}
                                </x-danger-button>
                            </div>
                            <div class="flex items-center">
                                <a href="{{ route('clients.index') }}"
                                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </a>
                                <x-primary-button class="ms-4">
                                    {{ __('Update Client') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Caregiver Progress Notes Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100" x-data="careNotesManager()"
                    data-notes="{{ $visitsWithNotes->mapWithKeys(fn($visit) => [$visit->id => $visit->progress_notes])->toJson() }}">
                    <h3
                        class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        Caregiver Progress Notes
                    </h3>

                    <div class="space-y-6 mt-4">
                        @forelse ($visitsWithNotes as $visit)
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                                            @if ($visit->shift->caregiver)
                                                {{ $visit->shift->caregiver->full_name }}
                                                @if ($visit->shift->caregiver->trashed())
                                                    <span class="text-xs font-normal text-red-500">(Deactivated on
                                                        {{ \Carbon\Carbon::parse($visit->shift->caregiver->deleted_at)->setTimezone(Auth::user()->agency->timezone)->format('M d, Y') }})</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 italic">Caregiver not found</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($visit->clock_out_time)->setTimezone(Auth::user()->agency->timezone)->format('l, F j, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="startEdit({{ $visit->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                <path fill-rule="evenodd"
                                                    d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete({{ $visit->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-3 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ $visit->progress_notes }}</p>

                                {{-- ✅ NEW: Audit Trail Section --}}
                                @if ($visit->modifications && $visit->modifications->count() > 0)
                                    <div x-data="{ showHistory: false }"
                                        class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-3">
                                        <button @click="showHistory = !showHistory"
                                            class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 mr-1 transition-transform"
                                                :class="{ 'rotate-90': showHistory }" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                            <span x-text="showHistory ? 'Hide History' : 'View History'"></span>
                                            <span class="ml-1 text-xs">({{ $visit->modifications->count() }}
                                                {{ $visit->modifications->count() === 1 ? 'change' : 'changes' }})</span>
                                        </button>

                                        <div x-show="showHistory" x-collapse class="mt-3 space-y-2">
                                            @foreach ($visit->modifications as $modification)
                                                <div
                                                    class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <p
                                                                class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $modification->action_description }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                by
                                                                {{ $modification->modifier->name ?? 'Unknown User' }}
                                                                •
                                                                {{ $modification->modified_at->setTimezone(Auth::user()->agency->timezone)->format('M d, Y g:i A') }}
                                                            </p>
                                                            @if ($modification->changes_description)
                                                                <p
                                                                    class="text-xs text-gray-600 dark:text-gray-300 mt-2">
                                                                    {{ $modification->changes_description }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <span
                                                            class="ml-2 px-2 py-1 text-xs rounded-full 
                                                            {{ $modification->action === 'created' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                            {{ $modification->action === 'clock_out' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                                            {{ $modification->action === 'note_updated' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                            {{ $modification->action === 'note_deleted' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                                            {{ ucfirst($modification->action) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">No care notes have been recorded for this
                                    client yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Edit Note Modal -->
                    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <!-- Background overlay - FIXED: Removed @click.away from here -->
                            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                                @click="cancelEdit()">
                            </div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>

                            <!-- Modal panel - FIXED: Added @click.stop to prevent event bubbling -->
                            <div x-show="showEditModal" @click.stop x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form :action="`/clients/notes/${editingVisitId}`" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                            id="modal-title">Edit Care Note</h3>
                                        <div class="mt-4">
                                            <textarea name="progress_notes" rows="8" x-model="editingText"
                                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <x-primary-button type="submit" class="ms-3">Save
                                            Changes</x-primary-button>
                                        <x-secondary-button type="button"
                                            @click="cancelEdit()">Cancel</x-secondary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Note Modal -->
                    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <!-- Background overlay - FIXED: Removed @click.away from here -->
                            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                                @click="closeDeleteModal()">
                            </div>

                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>

                            <!-- Modal panel - FIXED: Added @click.stop to prevent event bubbling -->
                            <div x-show="showDeleteModal" @click.stop x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form :action="`/clients/notes/${deletingVisitId}`" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                                    id="modal-title">Delete Care Note?</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">Are
                                                        you sure you want to delete this care note? This
                                                        action cannot be undone.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <x-danger-button type="submit" class="ms-3">Delete</x-danger-button>
                                        <x-secondary-button type="button"
                                            @click="closeDeleteModal()">Cancel</x-secondary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Standard Client Deletion Modal --}}
    <x-modal name="confirm-client-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('clients.destroy', $client) }}" class="p-6">
            @csrf
            @method('delete')
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Are you sure you want to delete this client?
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once this client is deleted, all of their data will be permanently removed. This action cannot be
                undone.
            </p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button class="ms-3">
                    {{ __('Delete Client') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        <script>
            function careNotesManager() {
                return {
                    notes: {},
                    editingVisitId: null,
                    editingText: '',
                    deletingVisitId: null,
                    showEditModal: false,
                    showDeleteModal: false,

                    init() {
                        this.notes = JSON.parse(this.$el.dataset.notes);
                    },

                    startEdit(visitId) {
                        this.editingVisitId = visitId;
                        this.editingText = this.notes[visitId] || '';
                        this.showEditModal = true;
                    },

                    cancelEdit() {
                        this.showEditModal = false;
                        this.editingVisitId = null;
                        this.editingText = '';
                    },

                    confirmDelete(visitId) {
                        this.deletingVisitId = visitId;
                        this.showDeleteModal = true;
                    },

                    closeDeleteModal() {
                        this.showDeleteModal = false;
                        this.deletingVisitId = null;
                    }
                }
            }
        </script>
    @endpush

</x-app-layout>
