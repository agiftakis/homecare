<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Agency: {{ $agency->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    {{-- Update Form --}}
                    <form action="{{ route('superadmin.agencies.update', $agency) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Agency Information --}}
                        <div>
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Agency Information</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update the agency's details.</p>

                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="name" value="Agency Name *" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $agency->name)" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="contact_email" value="Agency Contact Email *" />
                                    <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $agency->contact_email)" required />
                                    <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Phone" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $agency->phone)" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="address" value="Address" />
                                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $agency->address)" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-6">
                                <label for="is_lifetime_free" class="flex items-center">
                                    <input id="is_lifetime_free" name="is_lifetime_free" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old('is_lifetime_free', $agency->is_lifetime_free) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Grant Lifetime Free Access</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">⚠️ Checking this box will give the agency unlimited and permanent access to all of Vitalink's features!</p>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('superadmin.agencies.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-primary-button>
                                Save Changes
                            </x-primary-button>
                        </div>
                    </form>

                    {{-- ✅ NEW: Danger Zone - Delete Agency Section --}}
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                            <h3 class="text-lg font-semibold leading-6 text-red-900 dark:text-red-200">Danger Zone</h3>
                            <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                                Deleting this agency is <strong>permanent and irreversible</strong>. This will delete:
                            </p>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside ml-2">
                                <li>{{ $agency->clients_count ?? 0 }} client(s) and all their data</li>
                                <li>{{ $agency->caregivers_count ?? 0 }} caregiver(s) and all their documents</li>
                                <li>All scheduled shifts for this agency</li>
                                <li>The agency record and owner account</li>
                                <li>All associated files from Firebase storage</li>
                            </ul>
                            
                            <div class="mt-6">
                                <button 
                                    type="button"
                                    onclick="document.getElementById('delete-agency-modal').style.display='block'"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Delete This Agency
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ NEW: Confirmation Modal --}}
    <div id="delete-agency-modal" style="display: none;" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 text-center mt-4">
                    Delete Agency: {{ $agency->name }}?
                </h3>
                <div class="mt-2 px-4 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        This action <strong>cannot be undone</strong>. All data associated with this agency will be permanently deleted.
                    </p>
                </div>
                <div class="mt-4 flex justify-center space-x-3">
                    <button 
                        type="button"
                        onclick="document.getElementById('delete-agency-modal').style.display='none'"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                        Cancel
                    </button>
                    <form action="{{ route('superadmin.agencies.destroy', $agency) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Yes, Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>