<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Schedule') }}
        </h2>
    </x-slot>

    <x-slot name="scripts">
        {{-- This is the missing script tag that was added --}}
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="clientSchedule()" x-init="initCalendar();
                listenForUpdates();">

                    <div x-show="showUpdateNotification" x-cloak
                        class="mb-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 rounded-lg flex items-center justify-between"
                        x-transition>
                        <span class="font-bold">Your schedule has been updated. Please refresh to see the latest
                            changes.</span>
                        <button @click="window.location.reload()"
                            class="ml-4 px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            Refresh
                        </button>
                    </div>

                    <div x-show="showStatusChangeNotification" x-cloak
                        class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg flex items-center justify-between"
                        x-transition>
                        <span class="font-bold" x-text="statusChangeMessage"></span>
                        <button @click="showStatusChangeNotification = false"
                            class="ml-4 px-2 py-1 text-green-700 hover:text-green-900 focus:outline-none">
                            ×
                        </button>
                    </div>

                    <div x-show="viewMode === 'calendar'">
                        <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>
                    </div>

                    <div x-show="viewMode === 'dayList'" x-cloak>
                        <div
                            class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
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
                            <template x-for="shift in shiftsForSelectedDay()" :key="shift.id">
                                <div
                                    class="client-shift-item flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                    <div class="flex-grow mb-3 sm:mb-0">
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                                            <div class="font-mono text-sm text-gray-600 dark:text-gray-400 sm:w-32">
                                                <span x-text="formatTimeInUserTimezone(shift.start_time)"></span> -
                                                <span x-text="formatTimeInUserTimezone(shift.end_time)"></span>
                                            </div>
                                            <div class="font-semibold text-gray-800 dark:text-gray-200">
                                                <div class="flex items-center space-x-2">
                                                    <span x-html="getCaregiverDisplayHtml(shift)"></span>
                                                    <div x-show="shift.status === 'completed'"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Completed
                                                    </div>
                                                    <div x-show="shift.status === 'in_progress'"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        In Progress
                                                    </div>
                                                    <div x-show="shift.status === 'pending' && !isShiftMissed(shift)"
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Pending
                                                    </div>
                                                </div>
                                                <div x-show="shift.notes" class="text-xs text-gray-500 font-normal mt-1"
                                                    x-text="`Note: ${shift.notes}`"></div>
                                            </div>
                                        </div>
                                        <div x-show="shift.visit" class="mt-2 sm:pl-36 visit-times text-sm"
                                            x-html="getVisitTimesHtml(shift.visit)">
                                        </div>
                                        <div x-show="isShiftMissed(shift)" x-cloak
                                            class="mt-2 sm:pl-36 text-red-600 dark:text-red-500 font-bold text-sm">
                                            MISSED VISIT
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="shiftsForSelectedDay().length === 0"
                                class="text-center p-8 text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                No shifts scheduled for this day.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .visit-times {
            font-size: 0.8em;
            color: #ef4444 !important;
            margin-top: 2px;
            font-weight: bold !important;
        }

        @media screen and (max-width: 640px) {
            .client-shift-item .visit-times {
                padding-left: 0 !important;
                margin-top: 8px;
            }
        }
    </style>

    <script>
        function clientSchedule() {
            return {
                viewMode: 'calendar',
                selectedDate: null,
                selectedDateFormatted: '',
                calendar: null,
                shifts: @json($shifts),
                showUpdateNotification: false,
                showStatusChangeNotification: false,
                statusChangeMessage: '',

                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    const calendarConfig = {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth'
                        },
                        events: this.shifts.map(shift => ({
                            id: shift.id,
                            title: `Visit with ${shift.caregiver ? shift.caregiver.first_name : 'Unassigned'}`,
                            start: shift.start_time,
                            end: shift.end_time,
                            backgroundColor: this.getEventColor(shift.status),
                            borderColor: this.getEventBorderColor(shift.status),
                        })),
                        dateClick: (info) => this.viewDayList(info),
                        dayMaxEvents: true,
                        height: 'auto',
                    };

                    this.calendar = new FullCalendar.Calendar(calendarEl, calendarConfig);
                    this.calendar.render();
                },

                listenForUpdates() {
                    const clientId = {{ Auth::user()->client?->id ?? 'null' }};
                    if (clientId) {
                        window.Echo.private(`client-schedule.${clientId}`)
                            .listen('ShiftUpdated', (e) => {
                                console.log('ShiftUpdated event received!', e);
                                this.showUpdateNotification = true;
                            })
                            .listen('VisitStatusChanged', (e) => {
                                console.log('VisitStatusChanged event received!', e);
                                this.handleStatusChange(e);
                            });
                    }
                },

                handleStatusChange(eventData) {
                    // Find and update the shift in our local data
                    const shiftIndex = this.shifts.findIndex(shift => shift.id === eventData.shift_id);
                    if (shiftIndex !== -1) {
                        // Update the shift status
                        this.shifts[shiftIndex].status = eventData.new_status;
                        
                        // Update visit data if provided
                        if (eventData.visit_data) {
                            this.shifts[shiftIndex].visit = {
                                ...this.shifts[shiftIndex].visit,
                                ...eventData.visit_data
                            };
                        }

                        // Update calendar event color if calendar is visible
                        if (this.calendar) {
                            const calendarEvent = this.calendar.getEventById(eventData.shift_id);
                            if (calendarEvent) {
                                calendarEvent.setProp('backgroundColor', this.getEventColor(eventData.new_status));
                                calendarEvent.setProp('borderColor', this.getEventBorderColor(eventData.new_status));
                            }
                        }

                        // Show status change notification
                        const statusMessages = {
                            'in_progress': 'Your caregiver has arrived and clocked in!',
                            'completed': 'Your visit has been completed. Your caregiver has clocked out.',
                            'pending': 'Your visit status has been updated to pending.'
                        };
                        
                        this.statusChangeMessage = statusMessages[eventData.new_status] || 'Your visit status has been updated.';
                        this.showStatusChangeNotification = true;

                        // Auto-hide the notification after 5 seconds
                        setTimeout(() => {
                            this.showStatusChangeNotification = false;
                        }, 5000);
                    }
                },

                getEventColor(status) {
                    switch(status) {
                        case 'completed': return '#10b981';
                        case 'in_progress': return '#f59e0b';
                        default: return '#3b82f6';
                    }
                },

                getEventBorderColor(status) {
                    switch(status) {
                        case 'completed': return '#059669';
                        case 'in_progress': return '#d97706';
                        default: return '#2563eb';
                    }
                },

                viewDayList(info) {
                    this.selectedDate = info.dateStr;
                    const dateObj = new Date(this.selectedDate + 'T00:00:00');
                    this.selectedDateFormatted = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        timeZone: 'UTC'
                    });
                    this.viewMode = 'dayList';
                },

                shiftsForSelectedDay() {
                    if (!this.selectedDate) return [];
                    const userTimezone = '{{ Auth::user()->agency?->timezone ?? 'UTC' }}';

                    return this.shifts.filter(shift => {
                        const shiftDate = new Date(shift.start_time).toLocaleDateString('en-CA', {
                            timeZone: userTimezone
                        });
                        return shiftDate === this.selectedDate;
                    }).sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
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

                getCaregiverDisplayHtml(shift) {
                    if (shift.caregiver) {
                        const caregiverName = `${shift.caregiver.first_name} ${shift.caregiver.last_name || ''}`.trim();
                        if (shift.caregiver.deleted_at) {
                            return `<span>Caregiver: ${caregiverName} <span class="text-xs text-gray-500 font-normal">(No longer with agency)</span></span>`;
                        }
                        return `Caregiver: ${caregiverName}`;
                    }
                    if (shift.visit && shift.visit.caregiver_first_name) {
                        const caregiverName = `${shift.visit.caregiver_first_name} ${shift.visit.caregiver_last_name || ''}`
                            .trim();
                        return `<span>Caregiver: ${caregiverName} <span class="text-xs text-gray-500 font-normal">(No longer with agency)</span></span>`;
                    }
                    return `<span class="text-gray-500">Caregiver: Unassigned</span>`;
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

                isShiftMissed(shift) {
                    const shiftStartDate = new Date(shift.start_time);
                    const now = new Date();
                    return shiftStartDate < now && !shift.visit;
                },
            }
        }
    </script>
</x-app-layout>