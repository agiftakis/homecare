<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Create New Agency
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <form action="{{ route('superadmin.agencies.store') }}" method="POST">
                        @csrf

                        {{-- Agency Information --}}
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Agency Information</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Details about the new agency.</p>

                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="agency_name" value="Agency Name *" />
                                    <x-text-input id="agency_name" name="agency_name" type="text" class="mt-1 block w-full" :value="old('agency_name')" required autofocus />
                                    <x-input-error :messages="$errors->get('agency_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="contact_email" value="Agency Contact Email *" />
                                    <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email')" required />
                                    <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Phone" />
                                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="address" value="Address" />
                                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-6">
                                <label for="is_lifetime_free" class="flex items-center">
                                    <input id="is_lifetime_free" name="is_lifetime_free" type="checkbox" value="1" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old('is_lifetime_free') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Grant Lifetime Free Access</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">⚠️ Checking this box will give the agency unlimited, permanent access, bypassing all Stripe subscription requirements.</p>
                            </div>
                        </div>

                        {{-- Agency Admin User --}}
                        <div class="mt-8">
                             <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100">Agency Admin User</h3>
                             <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create the primary administrator account for this agency.</p>
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="admin_name" value="Admin Full Name *" />
                                    <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name')" required />
                                    <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="admin_email" value="Admin Email *" />
                                    <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email')" required />
                                    <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="admin_password" value="Password *" />
                                    <x-text-input id="admin_password" name="admin_password" type="password" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('admin_password')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="admin_password_confirmation" value="Confirm Password *" />
                                    <x-text-input id="admin_password_confirmation" name="admin_password_confirmation" type="password" class="mt-1 block w-full" required />
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('superadmin.agencies.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-primary-button>
                                Create Agency
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>