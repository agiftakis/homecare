<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle ?? 'All Schedules (SuperAdmin View)' }}
            </h2>
            <div class="flex items-center space-x-3">
                @if (request()->has('agency'))
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

    <x-slot name="scripts">
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (request()->has('agency'))
                @php
                    $agency = \App\Models\Agency::find(request()->agency);
                @endphp
                @if ($agency)
                    <div
                        class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-200">
                                    Showing schedule for <strong>{{ $agency->name }}</strong> only.
                                    <a href="{{ route('superadmin.schedule.index') }}"
                                        class="underline hover:text-blue-800 dark:hover:text-blue-100">View all
                                        schedules</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="superAdminSchedule()" x-init="initCalendar()">

                    {{-- ✅ View Toggle --}}
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <button @click="viewMode = 'calendar'"
                                :class="viewMode === 'calendar' ? 'bg-indigo-600 text-white' :
                                    'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                Calendar View
                            </button>
                            <button @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-indigo-600 text-white' :
                                    'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                List View
                            </button>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="shifts.length"></span> total shifts across all agencies
                        </div>
                    </div>

                    {{-- CALENDAR VIEW --}}
                    <div x-show="viewMode === 'calendar'">
                        <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>
                    </div>

                    {{--  DAY LIST VIEW --}}
                    <div x-show="viewMode === 'dayList'" x-cloak>
                        <div
                            class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                            <h3 class="text-xl font-semibold" x-text="`All Shifts for ${selectedDateFormatted}`"></h3>
                            <x-secondary-button @click="viewMode = 'calendar'" class="self-start sm:self-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Calendar
                            </x-secondary-button>
                        </div>

                        {{-- Agency Filter for Day View --}}
                        <div class="mb-4">
                            <select x-model="selectedAgencyFilter"
                                class="block w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="">All Agencies</option>
                                <template x-for="agency in agencies" :key="agency.id">
                                    <option :value="agency.id" x-text="agency.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                            <template x-for="shift in filteredShiftsForSelectedDay()" :key="shift.id">
                                <div
                                    class="superadmin-shift-item flex flex-col lg:flex-row lg:items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 ease-in-out">
                                    <div class="flex-grow mb-3 lg:mb-0">
                                        <div
                                            class="flex flex-col lg:flex-row lg:items-center lg:space-x-6 space-y-2 lg:space-y-0">
                                            <div class="font-mono text-sm text-gray-600 dark:text-gray-400 lg:w-32">
                                                <span x-text="formatTimeInUserTimezone(shift.start_time)"></span> -
                                                <span x-text="formatTimeInUserTimezone(shift.end_time)"></span>
                                            </div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span x-html="getClientDisplayHtml(shift)"></span>
                                                    <span class="text-gray-500">w/</span>
                                                    <span x-html="getCaregiverDisplayHtml(shift)"></span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                        <span x-text="shift.agency ? shift.agency.name : 'N/A'"></span>
                                                    </span>
                                                    <div x-show="shift.status === 'completed'"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Completed
                                                    </div>
                                                    <div x-show="shift.status === 'in_progress'"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        In Progress
                                                    </div>
                                                    <div x-show="shift.status === 'pending'"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Pending
                                                    </div>
                                                </div>
                                                <div x-show="shift.notes" class="text-xs text-gray-500 font-normal mt-1"
                                                    x-text="`Note: ${shift.notes}`"></div>
                                                <div x-show="shift.client && shift.client.address"
                                                    class="text-xs text-gray-500 font-normal mt-1"
                                                    x-text="`Address: ${shift.client.address}`"></div>
                                            </div>
                                        </div>
                                        <div x-show="shift.visit" class="mt-2 lg:pl-38 visit-times text-sm"
                                            x-html="getVisitTimesHtml(shift.visit)">
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 mt-2 lg:mt-0">
                                        {{-- View Signatures Button --}}
                                        <div x-show="shift.status === 'completed' && shift.visit">
                                            <button @click.stop="viewSignatures(shift.id)"
                                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">
                                                View Signatures
                                            </button>
                                        </div>
                                        {{-- System Info Badge --}}
                                        <div
                                            class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 text-xs rounded-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            System View
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="filteredShiftsForSelectedDay().length === 0"
                                class="text-center p-8 text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                No shifts scheduled for this day.
                            </div>
                        </div>
                    </div>

                    {{--  LEGACY LIST VIEW (Original table view) --}}
                    <div x-show="viewMode === 'list'" x-cloak>
                        <!-- Search Bar -->
                        <div class="mb-6">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" x-model="searchTerm"
                                    placeholder="Search by client or caregiver name..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <!-- Mobile View -->
                        <div class="space-y-4 md:hidden">
                            <template x-for="shift in filteredShifts()" :key="shift.id">
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                x-text="formatDateForDisplay(shift.start_time)">
                                            </span>
                                        </div>
                                        <span
                                            class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300"
                                            x-text="shift.agency ? shift.agency.name : 'N/A'">
                                        </span>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Time:</span>
                                            <span class="font-medium"
                                                x-text="`${formatTimeInUserTimezone(shift.start_time)} - ${formatTimeInUserTimezone(shift.end_time)}`">
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Client:</span>
                                            <span class="font-medium" x-html="getClientDisplayHtml(shift)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Caregiver:</span>
                                            <span class="font-medium" x-html="getCaregiverDisplayHtml(shift)"></span>
                                        </div>
                                        <div x-show="shift.notes"
                                            class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                            <span class="text-gray-600 dark:text-gray-400 text-xs">Notes:</span>
                                            <p class="text-sm mt-1"
                                                x-text="shift.notes.length > 100 ? shift.notes.substring(0, 100) + '...' : shift.notes">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="filteredShifts().length === 0"
                                class="text-center text-gray-500 dark:text-gray-400 py-8">
                                No shifts found matching your search.
                            </div>
                        </div>

                        <!-- Desktop View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date & Time</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Client</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Caregiver</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Agency</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="shift in filteredShifts()" :key="shift.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="formatDateForDisplay(shift.start_time)">
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400"
                                                    x-text="`${formatTimeInUserTimezone(shift.start_time)} - ${formatTimeInUserTimezone(shift.end_time)}`">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    x-html="getClientDisplayHtml(shift)">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    x-html="getCaregiverDisplayHtml(shift)">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400"
                                                    x-text="shift.agency ? shift.agency.name : 'N/A'"></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400"
                                                    x-text="shift.notes ? (shift.notes.length > 50 ? shift.notes.substring(0, 50) + '...' : shift.notes) : 'No notes'">
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filteredShifts().length === 0">
                                        <td colspan="5"
                                            class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No shifts found matching your search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{--  View Signatures Modal --}}
                    <div x-show="showSignaturesModal"
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                        @click.self="showSignaturesModal = false" style="display: none;">
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
                            @click.away="showSignaturesModal = false">
                            <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-gray-100">Visit Verification
                                Details</h3>
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Visit Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Client:</span>
                                        <span x-text="selectedVisit.client_name"
                                            class="text-gray-900 dark:text-gray-100"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Caregiver:</span>
                                        <span x-text="selectedVisit.caregiver_name"
                                            class="text-gray-900 dark:text-gray-100"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Agency:</span>
                                        <span x-text="selectedVisit.agency_name"
                                            class="text-gray-900 dark:text-gray-100"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Clock-in
                                            Time:</span>
                                        <span x-text="selectedVisit.clock_in_display"
                                            class="text-green-600 dark:text-green-400 font-medium"></span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600 dark:text-gray-400">Clock-out
                                            Time:</span>
                                        <span x-text="selectedVisit.clock_out_display"
                                            class="text-green-600 dark:text-green-400 font-medium"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="text-center">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-in Signature
                                    </h4>
                                    <div
                                        class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                        <img x-show="selectedVisit.clock_in_signature_url"
                                            :src="selectedVisit.clock_in_signature_url" alt="Clock-in Signature"
                                            class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded"
                                            style="max-height: 200px; margin: 0 auto;">
                                        <div x-show="!selectedVisit.clock_in_signature_url"
                                            class="text-gray-500 dark:text-gray-400 py-8">No signature available</div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-out Signature
                                    </h4>
                                    <div
                                        class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                        <img x-show="selectedVisit.clock_out_signature_url"
                                            :src="selectedVisit.clock_out_signature_url" alt="Clock-out Signature"
                                            class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded"
                                            style="max-height: 200px; margin: 0 auto;">
                                        <div x-show="!selectedVisit.clock_out_signature_url"
                                            class="text-gray-500 dark:text-gray-400 py-8">No signature available</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end">
                                <x-secondary-button type="button"
                                    @click="showSignaturesModal = false">Close</x-secondary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media screen and (max-width: 420px) {
            .fc-toolbar-title {
                font-size: 1.1em !important;
            }
        }

        .visit-times {
            font-size: 0.8em;
            color: #ef4444 !important;
            margin-top: 2px;
            font-weight: bold !important;
        }

        .shift-completed {
            background-color: #10b981 !important;
            border-color: #059669 !important;
        }

        .shift-in-progress {
            background-color: #f59e0b !important;
            border-color: #d97706 !important;
        }

        /* Enhanced hover effects for super admin list items */
        .superadmin-shift-item:hover {
            background-color: rgba(107, 114, 128, 0.1);
        }

        [data-theme="dark"] .superadmin-shift-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /*  Mobile optimizations for super admin view */
        @media screen and (max-width: 1024px) {
            .superadmin-shift-item {
                padding: 16px 12px;
            }

            .superadmin-shift-item .visit-times {
                padding-left: 0 !important;
                margin-top: 8px;
            }
        }
    </style>

    <script>
        function superAdminSchedule() {
            return {
                viewMode: 'calendar', // 'calendar', 'dayList', or 'list'
                selectedDate: null,
                selectedDateFormatted: '',
                selectedAgencyFilter: '',
                searchTerm: '',
                showSignaturesModal: false,
                calendar: null,
                shifts: @json($shifts),
                agencies: [],
                selectedVisit: {},

                //  BUG FIX: Safe client display helper
                getClientDisplayHtml(shift) {
                    if (!shift || !shift.client) {
                        return '<span class="text-gray-500 dark:text-gray-400">N/A</span>';
                    }
                    const clientName = `${shift.client.first_name || 'Unknown'} ${shift.client.last_name || ''}`.trim();
                    return `<span class="text-gray-900 dark:text-gray-100">${clientName}</span>`;
                },

                //  BUG FIX: Safe caregiver display helper
                getCaregiverDisplayHtml(shift) {
                    if (!shift || !shift.caregiver) {
                        return '<span class="text-blue-500 dark:text-blue-400">Unassigned</span>';
                    }
                    const caregiverName = `${shift.caregiver.first_name || 'Unknown'} ${shift.caregiver.last_name || ''}`
                        .trim();
                    return `<span class="text-gray-900 dark:text-gray-100">${caregiverName}</span>`;
                },

                formatDateTimeLocal(date) {
                    if (!date) return '';
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const hours = date.getHours().toString().padStart(2, '0');
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },
                formatTimeInUserTimezone(utcDateTime) {
                    if (!utcDateTime) return '';
                    const date = new Date(utcDateTime);
                    return date.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                    });
                },
                formatDateForDisplay(utcDateTime) {
                    if (!utcDateTime) return '';
                    const date = new Date(utcDateTime);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                // Extract unique agencies from shifts
                extractAgencies() {
                    const agencyMap = new Map();
                    this.shifts.forEach(shift => {
                        if (shift.agency && !agencyMap.has(shift.agency.id)) {
                            agencyMap.set(shift.agency.id, shift.agency);
                        }
                    });
                    this.agencies = Array.from(agencyMap.values());
                },

                // Filter shifts for selected day
                shiftsForSelectedDay() {
                    if (!this.selectedDate) return [];

                    return this.shifts.filter(shift => {
                        const shiftDate = new Date(shift.start_time).toLocaleDateString('en-CA', {
                            timeZone: 'UTC'
                        });
                        return shiftDate === this.selectedDate;
                    }).sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
                },

                // Filter shifts for selected day with agency filter
                filteredShiftsForSelectedDay() {
                    const dayShifts = this.shiftsForSelectedDay();
                    if (!this.selectedAgencyFilter) return dayShifts;

                    return dayShifts.filter(shift =>
                        shift.agency && shift.agency.id == this.selectedAgencyFilter
                    );
                },

                // BUG FIX: Updated filteredShifts with null-safe searching
                filteredShifts() {
                    let filtered = this.shifts;

                    // Apply search filter with null safety
                    if (this.searchTerm.trim()) {
                        const searchLower = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(shift => {
                            // Safe client name extraction
                            const clientName = shift.client ?
                                `${shift.client.first_name || ''} ${shift.client.last_name || ''}`.toLowerCase() :
                                'n/a unknown';

                            // Safe caregiver name extraction
                            const caregiverName = shift.caregiver ?
                                `${shift.caregiver.first_name || ''} ${shift.caregiver.last_name || ''}`
                                .toLowerCase() :
                                'unassigned';

                            const agencyName = (shift.agency?.name || '').toLowerCase();

                            return clientName.includes(searchLower) ||
                                caregiverName.includes(searchLower) ||
                                agencyName.includes(searchLower);
                        });
                    }

                    return filtered.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
                },

                // Date click handler for calendar
                dateClick(info) {
                    this.selectedDate = info.dateStr; // YYYY-MM-DD
                    const dateObj = new Date(this.selectedDate + 'T00:00:00');
                    this.selectedDateFormatted = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        timeZone: 'UTC'
                    });
                    this.viewMode = 'dayList';
                },

                getVisitTimesHtml(visit) {
                    let html = '';
                    if (visit.clock_in_time) {
                        html += `ACTUAL: In ${this.formatTimeInUserTimezone(visit.clock_in_time)}`;
                    }
                    if (visit.clock_out_time) {
                        if (html) html += ' | ';
                        html += `Out ${this.formatTimeInUserTimezone(visit.clock_out_time)}`;
                    }
                    return html;
                },

                //  BUG FIX: Updated viewSignatures with null safety
                viewSignatures(shiftId) {
                    const shift = this.shifts.find(s => s.id == shiftId);
                    if (shift && shift.visit) {
                        // Safe client name handling
                        const clientName = shift.client ?
                            `${shift.client.first_name || 'Unknown'} ${shift.client.last_name || ''}`.trim() :
                            'N/A';

                        // Safe caregiver name handling
                        const caregiverName = shift.caregiver ?
                            `${shift.caregiver.first_name || 'Unknown'} ${shift.caregiver.last_name || ''}`.trim() :
                            'Unassigned';

                        this.selectedVisit = {
                            client_name: clientName,
                            caregiver_name: caregiverName,
                            agency_name: shift.agency?.name || 'N/A',
                            clock_in_display: shift.visit.clock_in_time ? this.formatTimeInUserTimezone(shift.visit
                                .clock_in_time) : 'N/A',
                            clock_out_display: shift.visit.clock_out_time ? this.formatTimeInUserTimezone(shift.visit
                                .clock_out_time) : 'N/A',
                            clock_in_signature_url: shift.visit.clock_in_signature_url || '',
                            clock_out_signature_url: shift.visit.clock_out_signature_url || ''
                        };
                        this.showSignaturesModal = true;
                    }
                },

                // Initialize calendar
                initCalendar() {
                    this.extractAgencies();

                    this.$nextTick(() => {
                        const calendarEl = document.getElementById('calendar');

                        const calendarConfig = {
                            initialView: 'dayGridMonth',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth'
                            },
                            events: [], // Clean calendar - no events shown, just for date selection
                            dateClick: (info) => this.dateClick(info),
                            dayMaxEvents: false,
                            height: 'auto',
                            dayHeaderClassNames: ['text-sm', 'font-medium'],
                            dayCellClassNames: ['hover:bg-indigo-50', 'dark:hover:bg-indigo-900/20',
                                'cursor-pointer'
                            ]
                        };

                        this.calendar = new FullCalendar.Calendar(calendarEl, calendarConfig);
                        this.calendar.render();
                    });
                }
            }
        }

        // Duplicated functions to be accessible in the outer scope
        function formatDateTimeLocal(date) {
            if (!date) return '';
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        function formatTimeInUserTimezone(utcDateTime) {
            if (!utcDateTime) return '';
            const date = new Date(utcDateTime);
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            });
        }
    </script>
</x-app-layout>
