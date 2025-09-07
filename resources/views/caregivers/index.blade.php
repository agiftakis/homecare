<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 sm:mb-0">
                {{ __('Caregivers') }}
            </h2>
            <a href="{{ route('caregivers.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Add New Caregiver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('setup_link'))
                <div x-data="{
                    open: true,
                    link: '{{ session('setup_link') }}',
                    copyText: 'Copy',
                    copyLink() {
                        navigator.clipboard.writeText(this.link);
                        this.copyText = 'Copied!';
                        setTimeout(() => { this.copyText = 'Copy' }, 2000);
                    }
                }"
                    class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" x-show="open"
                    x-cloak>
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-lg mx-4"
                        @click.away="open = false">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Caregiver Onboarding Link
                            </h3>
                            <button @click="open = false"
                                class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">&times;</button>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            The caregiver has been created successfully. Please copy the secure link below and send it
                            to them so they can set up their password and log in.
                        </p>
                        <p class="text-sm text-red-600 dark:text-red-400 mb-4">
                            <strong>This is a one-time use link that will expire in 48 hours.</strong>
                        </p>
                        <div class="flex items-center space-x-2">
                            <input type="text" :value="link" readonly
                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <x-primary-button @click="copyLink()" x-text="copyText"></x-primary-button>
                        </div>
                    </div>
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="caregiverSearch" placeholder="Search caregivers by name..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div id="mobileView" class="space-y-4 md:hidden">
                        @forelse ($caregivers as $caregiver)
                            <div class="caregiver-card bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700"
                                data-name="{{ strtolower($caregiver->first_name . ' ' . $caregiver->last_name) }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if ($caregiver->profile_picture_url)
                                                <img class="h-12 w-12 rounded-full object-cover"
                                                    src="{{ $caregiver->profile_picture_url }}"
                                                    alt="Caregiver profile picture">
                                            @else
                                                <div
                                                    class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-8 w-8 text-gray-400" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-base font-bold text-gray-900 dark:text-gray-100">
                                                {{ $caregiver->first_name }} {{ $caregiver->last_name }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Email:</span>
                                        {{ $caregiver->email }}</p>
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Phone:</span>
                                        {{ $caregiver->phone_number }}</p>
                                </div>
                                <div class="mt-4 text-right">
                                    <a href="{{ route('caregivers.edit', $caregiver) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No caregivers have been added
                                yet.</p>
                        @endforelse
                    </div>

                    <div id="desktopView" class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Name</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Phone</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($caregivers as $caregiver)
                                    <tr class="caregiver-row"
                                        data-name="{{ strtolower($caregiver->first_name . ' ' . $caregiver->last_name) }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if ($caregiver->profile_picture_url)
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $caregiver->profile_picture_url }}"
                                                            alt="Caregiver profile picture">
                                                    @else
                                                        <div
                                                            class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-gray-400" fill="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path
                                                                    d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $caregiver->first_name }} {{ $caregiver->last_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $caregiver->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $caregiver->phone_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('caregivers.edit', $caregiver) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit/View
                                                Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No caregivers have been added yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div id="noResults" class="hidden text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No caregivers found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('caregiverSearch');
            const caregiverCards = document.querySelectorAll('.caregiver-card');
            const caregiverRows = document.querySelectorAll('.caregiver-row');
            const noResults = document.getElementById('noResults');
            const mobileView = document.getElementById('mobileView');
            const desktopView = document.getElementById('desktopView');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

                // Filter mobile cards
                caregiverCards.forEach(card => {
                    const caregiverName = card.getAttribute('data-name');
                    if (caregiverName.includes(searchTerm)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Filter desktop rows
                caregiverRows.forEach(row => {
                    const caregiverName = row.getAttribute('data-name');
                    if (caregiverName.includes(searchTerm)) {
                        row.style.display = 'table-row';
                        // For desktop, we count rows visible in the current view
                        if (window.getComputedStyle(desktopView).display !== 'none') {
                            visibleCount++;
                        }
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide no results message
                const totalItems = caregiverCards.length > 0 ? caregiverCards.length : caregiverRows.length;
                if (visibleCount === 0 && totalItems > 0 && searchTerm !== '') {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
