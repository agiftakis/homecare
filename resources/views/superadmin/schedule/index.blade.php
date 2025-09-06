<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle ?? 'All Schedules (SuperAdmin View)' }}
            </h2>
            <div class="flex items-center space-x-3">
                @if(request()->has('agency'))
                    <a href="{{ route('superadmin.schedule.index') }}" 
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        &larr; View All Agencies
                    </a>
                @endif
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(request()->has('agency'))
                @php
                    $agency = \App\Models\Agency::find(request()->agency);
                @endphp
                @if($agency)
                    <div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-200">
                                    Showing schedule for <strong>{{ $agency->name }}</strong> only. 
                                    <a href="{{ route('superadmin.schedule.index') }}" class="underline hover:text-blue-800 dark:hover:text-blue-100">View all schedules</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="scheduleSearch" 
                                   placeholder="Search by client or caregiver name..." 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <!-- Mobile View -->
                    <div id="mobileView" class="space-y-4 md:hidden">
                        @forelse ($shifts as $shift)
                            <div class="shift-card bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700" 
                                 data-search="{{ strtolower(($shift->client->first_name ?? '') . ' ' . ($shift->client->last_name ?? '') . ' ' . ($shift->caregiver->first_name ?? '') . ' ' . ($shift->caregiver->last_name ?? '')) }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('M j, Y') }}
                                        </span>
                                    </div>
                                    @if(!request()->has('agency'))
                                        <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300">
                                            {{ $shift->agency->name ?? 'N/A' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Time:</span>
                                        <span class="font-medium">
                                            {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - 
                                            {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Client:</span>
                                        <span class="font-medium">{{ $shift->client->first_name ?? 'N/A' }} {{ $shift->client->last_name ?? '' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Caregiver:</span>
                                        <span class="font-medium">{{ $shift->caregiver->first_name ?? 'N/A' }} {{ $shift->caregiver->last_name ?? '' }}</span>
                                    </div>
                                    @if($shift->notes)
                                        <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <span class="text-gray-600 dark:text-gray-400 text-xs">Notes:</span>
                                            <p class="text-sm mt-1">{{ Str::limit($shift->notes, 100) }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                @if(request()->has('agency'))
                                    No scheduled shifts found for this agency.
                                @else
                                    No scheduled shifts found across all agencies.
                                @endif
                            </p>
                        @endforelse
                    </div>

                    <!-- Desktop View -->
                    <div id="desktopView" class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Caregiver</th>
                                    @if(!request()->has('agency'))
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agency</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($shifts as $shift)
                                    <tr class="shift-row" data-search="{{ strtolower(($shift->client->first_name ?? '') . ' ' . ($shift->client->last_name ?? '') . ' ' . ($shift->caregiver->first_name ?? '') . ' ' . ($shift->caregiver->last_name ?? '')) }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('M j, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $shift->client->first_name ?? 'N/A' }} {{ $shift->client->last_name ?? '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $shift->caregiver->first_name ?? 'N/A' }} {{ $shift->caregiver->last_name ?? '' }}
                                            </div>
                                        </td>
                                        @if(!request()->has('agency'))
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $shift->agency->name ?? 'N/A' }}</div>
                                            </td>
                                        @endif
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $shift->notes ? Str::limit($shift->notes, 50) : 'No notes' }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ request()->has('agency') ? '4' : '5' }}" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                            @if(request()->has('agency'))
                                                No scheduled shifts found for this agency.
                                            @else
                                                No scheduled shifts found across all agencies.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div id="noResults" class="hidden text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No shifts found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('scheduleSearch');
            const shiftCards = document.querySelectorAll('.shift-card');
            const shiftRows = document.querySelectorAll('.shift-row');
            const noResults = document.getElementById('noResults');
            const mobileView = document.getElementById('mobileView');
            const desktopView = document.getElementById('desktopView');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleCount = 0;

                // Filter mobile cards
                shiftCards.forEach(card => {
                    const searchData = card.getAttribute('data-search');
                    if (searchData.includes(searchTerm)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Filter desktop rows
                shiftRows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    if (searchData.includes(searchTerm)) {
                        row.style.display = 'table-row';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0 && searchTerm !== '') {
                    noResults.classList.remove('hidden');
                    mobileView.classList.add('hidden');
                    desktopView.classList.add('hidden');
                } else {
                    noResults.classList.add('hidden');
                    mobileView.classList.remove('hidden');
                    desktopView.classList.remove('hidden');
                }
            });
        });
    </script>
</x-app-layout>