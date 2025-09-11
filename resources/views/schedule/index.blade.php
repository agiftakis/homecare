<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Schedule') }}
        </h2>
    </x-slot>

    <x-slot name="scripts">
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="schedule({{ $is_admin ? 'true' : 'false' }})"
                    x-init="initCalendar();
                    setupSignatureButtonHandlers()">

                    {{-- MAIN VIEW: Conditionally show Calendar or Daily List View --}}
                    <div x-show="viewMode === 'calendar'">
                        <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>
                    </div>

                    {{-- ADMIN: Daily Shift List View --}}
                    <div x-show="isAdmin && viewMode === 'dayList'" x-cloak>
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-4">
                            <h3 class="text-xl font-semibold" x-text="`Shifts for ${selectedDateFormatted}`"></h3>
                            <x-secondary-button @click="viewMode = 'calendar'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Calendar
                            </x-secondary-button>
                        </div>
                        
                        {{-- SEARCH BAR ADDED --}}
                        <div class="mb-4">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       x-model="searchTerm"
                                       placeholder="Search by client or caregiver name..." 
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                            {{-- UPDATED to use filteredShiftsForSelectedDay() --}}
                            <template x-for="shift in filteredShiftsForSelectedDay()" :key="shift.id">
                                <div
                                    class="daily-shift-item flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 ease-in-out">
                                    <div class="flex-grow">
                                        <div class="flex items-center space-x-4">
                                            <div class="font-mono text-sm text-gray-600 dark:text-gray-400 w-32">
                                                <span x-text="formatTimeInUserTimezone(shift.start_time)"></span> -
                                                <span x-text="formatTimeInUserTimezone(shift.end_time)"></span>
                                            </div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{-- ✅ UPDATED: Smart caregiver display logic --}}
                                                <span x-text="shift.client.first_name"></span> w/ 
                                                <span x-html="getCaregiverDisplayHtml(shift)"></span>
                                                <div x-show="shift.notes" class="text-xs text-gray-500 font-normal"
                                                    x-text="`Note: ${shift.notes}`"></div>
                                            </div>
                                        </div>
                                        <div x-show="shift.visit" class="pl-36 visit-times text-sm"
                                            x-html="getVisitTimesHtml(shift.visit)">
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div x-show="shift.status === 'completed' && shift.visit">
                                            <button @click.stop="viewSignatures(shift.id)"
                                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">
                                                View Signatures
                                            </button>
                                        </div>
                                        <button @click="editShiftFromList(shift)"
                                            class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded-md">
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            </template>
                            {{-- ✅ UPDATED to use filteredShiftsForSelectedDay() --}}
                            <div x-show="filteredShiftsForSelectedDay().length === 0"
                                class="text-center p-8 text-gray-500 dark:text-gray-400">
                                No shifts found for this day<span x-show="searchTerm" x-text="` matching your search`"></span>.
                            </div>
                        </div>
                    </div>

                    {{-- ✅ NEW: CAREGIVER Daily Shift List View (Read-Only) --}}
                    <div x-show="!isAdmin && viewMode === 'dayList'" x-cloak>
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                            <h3 class="text-xl font-semibold" x-text="`My Shifts for ${selectedDateFormatted}`"></h3>
                            <x-secondary-button @click="viewMode = 'calendar'" class="self-start sm:self-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Calendar
                            </x-secondary-button>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                            <template x-for="shift in allShiftsForSelectedDay()" :key="shift.id">
                                <div
                                    class="caregiver-shift-item flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 ease-in-out">
                                    <div class="flex-grow mb-3 sm:mb-0">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                                            <div class="font-mono text-sm text-gray-600 dark:text-gray-400 sm:w-32">
                                                <span x-text="formatTimeInUserTimezone(shift.start_time)"></span> -
                                                <span x-text="formatTimeInUserTimezone(shift.end_time)"></span>
                                            </div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                <div class="flex items-center space-x-2">
                                                    <span x-text="`Client: ${shift.client.first_name} ${shift.client.last_name}`"></span>
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
                                                <div x-show="shift.client.address" class="text-xs text-gray-500 font-normal mt-1"
                                                    x-text="`Address: ${shift.client.address}`"></div>
                                            </div>
                                        </div>
                                        <div x-show="shift.visit" class="mt-2 sm:pl-36 visit-times text-sm"
                                            x-html="getVisitTimesHtml(shift.visit)">
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 mt-2 sm:mt-0">
                                        {{-- Clock In/Out Button --}}
                                        <div x-show="shift.status === 'pending' || shift.status === 'in_progress'">
                                            <a :href="`/shifts/${shift.id}/verify`"
                                               class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-150 ease-in-out">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="shift.status === 'pending' ? 'Clock In' : 'Clock Out'"></span>
                                            </a>
                                        </div>
                                        {{-- Completed Badge --}}
                                        <div x-show="shift.status === 'completed'" 
                                             class="inline-flex items-center px-3 py-2 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-sm font-medium rounded-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Completed
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="allShiftsForSelectedDay().length === 0"
                                class="text-center p-8 text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                No shifts scheduled for this day.
                            </div>
                        </div>
                    </div>

                    {{-- Modals for Admins --}}
                    @if ($is_admin)
                        <div x-show="showAddModal"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                            @click.self="showAddModal = false" style="display: none;">
                            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl"
                                @click.away="showAddModal = false">
                                <h3 class="text-lg font-medium mb-4">Add New Shift</h3>
                                <form @submit.prevent="submitAddForm">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @include('schedule.partials.shift-form-fields', [
                                            'shift' => 'newShift',
                                        ])
                                    </div>
                                    <div class="mt-6 flex justify-between items-center">
                                        <x-primary-button type="button" @click="viewShiftsForDay()">View All Shifts for this Day</x-primary-button>
                                        <div class="space-x-4">
                                            <x-secondary-button type="button"
                                                @click="showAddModal = false">Cancel</x-secondary-button>
                                            <x-primary-button type="submit">Save Shift</x-primary-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div x-show="showEditModal"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                            @click.self="showEditModal = false" style="display: none;">
                            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl"
                                @click.away="showEditModal = false">
                                <h3 class="text-lg font-medium mb-4">Edit Shift</h3>
                                <form @submit.prevent="submitEditForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @include('schedule.partials.shift-form-fields', [
                                            'shift' => 'editShift',
                                        ])
                                    </div>
                                    <div class="mt-6 flex justify-between">
                                        <x-danger-button type="button" @click="deleteShift()">Delete</x-danger-button>
                                        <div class="space-x-4">
                                            <x-secondary-button type="button"
                                                @click="showEditModal = false">Cancel</x-secondary-button>
                                            <x-primary-button type="submit">Update Shift</x-primary-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

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
                                            <span class="font-medium text-gray-600 dark:text-gray-400">Clock-in Time:</span>
                                            <span x-text="selectedVisit.clock_in_display"
                                                class="text-green-600 dark:text-green-400 font-medium"></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">Clock-out Time:</span>
                                            <span x-text="selectedVisit.clock_out_display"
                                                class="text-green-600 dark:text-green-400 font-medium"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="text-center">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-in Signature</h4>
                                        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                            <img x-show="selectedVisit.clock_in_signature_url" :src="selectedVisit.clock_in_signature_url" alt="Clock-in Signature" class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded" style="max-height: 200px; margin: 0 auto;">
                                            <div x-show="!selectedVisit.clock_in_signature_url" class="text-gray-500 dark:text-gray-400 py-8">No signature available</div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-out Signature</h4>
                                        <div class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                            <img x-show="selectedVisit.clock_out_signature_url" :src="selectedVisit.clock_out_signature_url" alt="Clock-out Signature" class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded" style="max-height: 200px; margin: 0 auto;">
                                            <div x-show="!selectedVisit.clock_out_signature_url" class="text-gray-500 dark:text-gray-400 py-8">No signature available</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end">
                                    <x-secondary-button type="button" @click="showSignaturesModal = false">Close</x-secondary-button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        @media screen and (max-width: 420px) { .fc-toolbar-title { font-size: 1.1em !important; } }
        .shift-notes { font-size: 0.8em; color: #d1d5db; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .visit-times { font-size: 0.8em; color: #ef4444 !important; margin-top: 2px; font-weight: bold !important; }
        .shift-completed { background-color: #10b981 !important; border-color: #059669 !important; }
        .shift-in-progress { background-color: #f59e0b !important; border-color: #d97706 !important; }
        .fc-timegrid-event-harness-inset .fc-timegrid-event,
        .fc-timegrid-event.fc-event-mirror,
        .fc-timegrid-future-event-harness-inset .fc-timegrid-event { padding: 2px 3px !important; font-size: 0.75em !important; }
        .fc-timegrid-event .fc-event-main { padding: 2px !important; }

        /* Enhanced hover effects for both admin and caregiver list items */
        .daily-shift-item:hover,
        .caregiver-shift-item:hover {
            background-color: rgba(107, 114, 128, 0.1);
        }
        [data-theme="dark"] .daily-shift-item:hover,
        [data-theme="dark"] .caregiver-shift-item:hover {
             background-color: rgba(255, 255, 255, 0.05);
        }

        /*  Mobile optimizations for caregiver view */
        @media screen and (max-width: 640px) {
            .caregiver-shift-item {
                padding: 16px 12px;
            }
            
            .caregiver-shift-item .visit-times {
                padding-left: 0 !important;
                margin-top: 8px;
            }
        }
    </style>

    <script>
        function schedule(isAdmin) {
            return {
                viewMode: 'calendar', // 'calendar' or 'dayList'
                selectedDate: null,
                selectedDateFormatted: '',
                searchTerm: '', // SEARCH TERM ADDED
                showAddModal: false,
                showEditModal: false,
                showSignaturesModal: false,
                calendar: null,
                shifts: @json($shifts),
                selectedVisit: {},
                newShift: { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
                editShift: { id: null, client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
                isAdmin: isAdmin,
                
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

                // ✅ NEW: Smart caregiver display logic
                getCaregiverDisplayHtml(shift) {
                    // If shift has active caregiver, show normal name
                    if (shift.caregiver) {
                        return shift.caregiver.first_name;
                    }
                    
                    // If shift has completed visit with preserved caregiver info
                    if (shift.visit && shift.visit.caregiver_first_name) {
                        const caregiverName = `${shift.visit.caregiver_first_name} ${shift.visit.caregiver_last_name || ''}`.trim();
                        // For now using placeholder date - you can replace with actual deletion timestamp
                        const deletionDate = 'Sept 11 2025 @ 1:29 PM'; 
                        return `<span class="text-orange-600 dark:text-orange-400">${caregiverName}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 font-normal">
                                    (Caregiver deleted on ${deletionDate})
                                </span>`;
                    }
                    
                    // If no caregiver and no visit (future/pending shift)
                    return `<span class="text-red-500 dark:text-red-400">N/A</span>
                            <span class="text-xs text-red-500 dark:text-red-400 ml-2 font-normal">
                                (Caregiver deleted - needs reassignment)
                            </span>`;
                },

                // ✅ UPDATED: viewSignatures to use visit caregiver info
                viewSignatures(shiftId) { 
                    const shift = this.shifts.find(s => s.id == shiftId);
                    if (shift && shift.visit) {
                        // Use preserved caregiver info from visit if available
                        let caregiverName = 'N/A (Caregiver Deleted)';
                        if (shift.visit.caregiver_first_name) {
                            caregiverName = `${shift.visit.caregiver_first_name} ${shift.visit.caregiver_last_name || ''}`.trim();
                            caregiverName += ' (Caregiver deleted)';
                        } else if (shift.caregiver) {
                            caregiverName = `${shift.caregiver.first_name} ${shift.caregiver.last_name}`;
                        }

                        this.selectedVisit = {
                            client_name: `${shift.client.first_name} ${shift.client.last_name}`,
                            caregiver_name: caregiverName,
                            clock_in_display: shift.visit.clock_in_time ? this.formatTimeInUserTimezone(shift.visit.clock_in_time) : 'N/A',
                            clock_out_display: shift.visit.clock_out_time ? this.formatTimeInUserTimezone(shift.visit.clock_out_time) : 'N/A',
                            clock_in_signature_url: shift.visit.clock_in_signature_url || '',
                            clock_out_signature_url: shift.visit.clock_out_signature_url || ''
                        };
                        this.showSignaturesModal = true;
                    }
                },
                setupSignatureButtonHandlers() { 
                    document.addEventListener('click', (e) => {
                        if (e.target.hasAttribute('data-view-signatures')) {
                            e.preventDefault();
                            e.stopPropagation();
                            const shiftId = e.target.getAttribute('data-view-signatures');
                            this.viewSignatures(shiftId);
                        }
                    });
                },

                // Renamed function
                allShiftsForSelectedDay() {
                    if (!this.selectedDate) return [];
                    const userTimezone = '{{ Auth::user()->agency?->timezone ?? 'UTC' }}';
                    
                    return this.shifts.filter(shift => {
                        const shiftDate = new Date(shift.start_time).toLocaleDateString('en-CA', { timeZone: userTimezone });
                        return shiftDate === this.selectedDate;
                    }).sort((a,b) => new Date(a.start_time) - new Date(b.start_time));
                },
                
                // ✅ UPDATED: search filter to handle visit caregiver info
                filteredShiftsForSelectedDay() {
                    let dayShifts = this.allShiftsForSelectedDay();

                    if (!this.searchTerm.trim()) {
                        return dayShifts;
                    }

                    const searchLower = this.searchTerm.toLowerCase();
                    return dayShifts.filter(shift => {
                        const clientName = `${shift.client.first_name || ''} ${shift.client.last_name || ''}`.toLowerCase();
                        
                        // Check current caregiver name
                        let caregiverName = '';
                        if (shift.caregiver) {
                            caregiverName = `${shift.caregiver.first_name || ''} ${shift.caregiver.last_name || ''}`.toLowerCase();
                        } else if (shift.visit && shift.visit.caregiver_first_name) {
                            // Check preserved caregiver name from visit
                            caregiverName = `${shift.visit.caregiver_first_name || ''} ${shift.visit.caregiver_last_name || ''}`.toLowerCase();
                        } else {
                            caregiverName = 'n/a';
                        }
                        
                        return clientName.includes(searchLower) || caregiverName.includes(searchLower);
                    });
                },

                caregiverDateClick(info) {
                    this.selectedDate = info.dateStr; // YYYY-MM-DD
                    const dateObj = new Date(this.selectedDate + 'T00:00:00');
                    this.selectedDateFormatted = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC'
                    });
                    this.viewMode = 'dayList';
                },

                viewShiftsForDay() {
                    this.selectedDate = this.newShift.start_time.split('T')[0]; // YYYY-MM-DD
                    const dateObj = new Date(this.selectedDate + 'T00:00:00');
                    this.selectedDateFormatted = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC'
                    });
                    this.viewMode = 'dayList';
                    this.showAddModal = false;
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

                // ✅ UPDATED: editShiftFromList to handle null caregiver
                editShiftFromList(shift) {
                       this.editShift = {
                           id: shift.id,
                           client_id: shift.client_id,
                           caregiver_id: shift.caregiver_id || '', // ✅ Handle null caregiver_id
                           start_time: this.formatDateTimeLocal(new Date(shift.start_time)),
                           end_time: this.formatDateTimeLocal(new Date(shift.end_time)),
                           notes: shift.notes
                       };
                       this.showEditModal = true;
                },

                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    let calendarConfig;

                    if (this.isAdmin) {
                        calendarConfig = {
                            initialView: 'dayGridMonth',
                            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth' },
                            events: [],
                            dateClick: (info) => {
                                const startTime = new Date(info.dateStr + 'T09:00:00');
                                const endTime = new Date(startTime.getTime() + 60 * 60 * 1000);
                                this.newShift = {
                                    client_id: '', caregiver_id: '', notes: '',
                                    start_time: this.formatDateTimeLocal(startTime),
                                    end_time: this.formatDateTimeLocal(endTime),
                                };
                                this.showAddModal = true;
                            }
                        };
                    } else {
                        calendarConfig = {
                            initialView: 'dayGridMonth',
                            headerToolbar: { 
                                left: 'prev,next today', 
                                center: 'title', 
                                right: 'dayGridMonth' 
                            },
                            events: [],
                            dateClick: (info) => this.caregiverDateClick(info),
                            dayMaxEvents: false,
                            height: 'auto',
                            dayHeaderClassNames: ['text-sm', 'font-medium'],
                            dayCellClassNames: ['hover:bg-blue-50', 'dark:hover:bg-blue-900/20', 'cursor-pointer']
                        };
                    }
                    
                    this.calendar = new FullCalendar.Calendar(calendarEl, calendarConfig);
                    this.calendar.render();
                    toastr.options.progressBar = true;
                    toastr.options.positionClass = 'toast-bottom-right';
                },

                // Form submission methods for admins
                submitAddForm() { 
                    fetch('{{ route('shifts.store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(this.newShift)
                        })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok) {
                                this.shifts.push(data.shift);
                                this.showAddModal = false;
                                this.newShift = { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' };
                                toastr.success('New shift created successfully!');
                            } else { throw data; }
                        }).catch(error => this.handleFormError(error));
                },
                submitEditForm() { 
                    fetch(`/shifts/${this.editShift.id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(this.editShift)
                        })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok) {
                                const index = this.shifts.findIndex(s => s.id == this.editShift.id);
                                if (index !== -1) this.shifts[index] = data.shift;
                                this.showEditModal = false;
                                toastr.success('Shift updated successfully!');
                            } else { throw data; }
                        }).catch(error => this.handleFormError(error));
                },
                deleteShift() { 
                    if (!confirm('Are you sure you want to delete this shift?')) return;
                    fetch(`/shifts/${this.editShift.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                        })
                        .then(res => res.json().then(data => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok) {
                                this.shifts = this.shifts.filter(s => s.id != this.editShift.id);
                                this.showEditModal = false;
                                toastr.info('Shift has been deleted.');
                            } else { throw data; }
                        }).catch(error => this.handleFormError(error));
                },
                handleFormError(error) { 
                    let errorMessages = 'An unexpected error occurred.';
                    if (error && error.errors) {
                        errorMessages = Object.values(error.errors).flat().join('<br>');
                    } else if (error && error.message) {
                        errorMessages = error.message;
                    }
                    toastr.error(errorMessages);
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