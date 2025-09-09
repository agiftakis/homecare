<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Schedule') }}
        </h2>
    </x-slot>

    {{-- I'm assuming you have a scripts slot in your app.blade.php for this --}}
    <x-slot name="scripts">
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
        {{-- If you use toastr, its script would go here too --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="schedule({{ $is_admin ? 'true' : 'false' }})" {{-- ✅ SECURITY FIX: Pass admin status to JS --}}
                    x-init="initCalendar();
                    setupSignatureButtonHandlers()">

                    <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>

                    {{-- ✅ SECURITY FIX: These modals will ONLY be rendered for agency admins --}}
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
                                        {{-- Assuming this partial binds to 'newShift' --}}
                                        @include('schedule.partials.shift-form-fields', [
                                            'shift' => 'newShift',
                                        ])
                                    </div>
                                    <div class="mt-6 flex justify-end space-x-4">
                                        <x-secondary-button type="button"
                                            @click="showAddModal = false">Cancel</x-secondary-button>
                                        <x-primary-button type="submit">Save Shift</x-primary-button>
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
                                        {{-- Assuming this partial binds to 'editShift' --}}
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

                        {{-- ✅ NEW: View Signatures Modal for Completed Shifts --}}
                        <div x-show="showSignaturesModal"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                            @click.self="showSignaturesModal = false" style="display: none;">
                            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
                                @click.away="showSignaturesModal = false">
                                <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-gray-100">Visit Verification
                                    Details</h3>

                                {{-- Visit Information --}}
                                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Visit Information
                                    </h4>
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

                                {{-- Signatures Section --}}
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    {{-- Clock-in Signature --}}
                                    <div class="text-center">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-in
                                            Signature</h4>
                                        <div
                                            class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                            <div x-show="selectedVisit.clock_in_signature_url">
                                                <img :src="selectedVisit.clock_in_signature_url"
                                                    alt="Clock-in Signature"
                                                    class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded"
                                                    style="max-height: 200px; margin: 0 auto;">
                                            </div>
                                            <div x-show="!selectedVisit.clock_in_signature_url"
                                                class="text-gray-500 dark:text-gray-400 py-8">
                                                No signature available
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Clock-out Signature --}}
                                    <div class="text-center">
                                        <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Clock-out
                                            Signature</h4>
                                        <div
                                            class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                            <div x-show="selectedVisit.clock_out_signature_url">
                                                <img :src="selectedVisit.clock_out_signature_url"
                                                    alt="Clock-out Signature"
                                                    class="max-w-full h-auto border border-gray-300 dark:border-gray-600 rounded"
                                                    style="max-height: 200px; margin: 0 auto;">
                                            </div>
                                            <div x-show="!selectedVisit.clock_out_signature_url"
                                                class="text-gray-500 dark:text-gray-400 py-8">
                                                No signature available
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Close Button --}}
                                <div class="mt-8 flex justify-end">
                                    <x-secondary-button type="button"
                                        @click="showSignaturesModal = false">Close</x-secondary-button>
                                </div>
                            </div>
                        </div>
                    @endif
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

        /* ✅ NEW STYLE for shift notes */
        .shift-notes {
            font-size: 0.8em;
            color: #d1d5db;
            /* Lighter text for dark mode */
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ✅ FIXED STYLE for visit times - Bold Red */
        .visit-times {
            font-size: 0.8em;
            color: #ef4444 !important;
            /* Bright red color */
            margin-top: 2px;
            font-weight: bold !important;
            /* Bold font */
        }

        /* ✅ NEW STYLE for completed shifts */
        .shift-completed {
            background-color: #10b981 !important;
            /* Green background for completed shifts */
            border-color: #059669 !important;
        }

        /* ✅ NEW STYLE for in-progress shifts */
        .shift-in-progress {
            background-color: #f59e0b !important;
            /* Orange background for in-progress shifts */
            border-color: #d97706 !important;
        }
    </style>

    <script>
        // ✅ SECURITY FIX: The schedule function now accepts the user's admin status
        function schedule(isAdmin) {
            return {
                showAddModal: false,
                showEditModal: false,
                showSignaturesModal: false, // ✅ NEW: Signatures modal state
                calendar: null,
                shifts: @json($shifts),
                selectedVisit: { // ✅ NEW: Store selected visit data for signatures modal
                    client_name: '',
                    caregiver_name: '',
                    clock_in_display: '',
                    clock_out_display: '',
                    clock_in_signature_url: '',
                    clock_out_signature_url: ''
                },
                newShift: {
                    client_id: '',
                    caregiver_id: '',
                    start_time: '',
                    end_time: '',
                    notes: ''
                },
                editShift: {
                    id: null,
                    client_id: '',
                    caregiver_id: '',
                    start_time: '',
                    end_time: '',
                    notes: ''
                },
                isAdmin: isAdmin, // Store the admin status

                // ✅ NEW HELPER FUNCTION
                formatDateTimeLocal(date) {
                    if (!date) return '';
                    const year = date.getFullYear();
                    const month = (date.getMonth() + 1).toString().padStart(2, '0');
                    const day = date.getDate().toString().padStart(2, '0');
                    const hours = date.getHours().toString().padStart(2, '0');
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },

                // ✅ ACTUALLY FIXED: Format time from UTC to user timezone (REMOVED + 'Z')
                formatTimeInUserTimezone(utcDateTime) {
                    if (!utcDateTime) return '';

                    // Laravel already provides UTC timestamps with 'Z', so don't add it again
                    const utcDate = new Date(utcDateTime);
                    const userTimezone = '{{ Auth::user()->agency?->timezone ?? 'UTC' }}';

                    return utcDate.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                        timeZone: userTimezone
                    });
                },

                // ✅ NEW: Function to open signatures modal
                viewSignatures(shiftId) {
                    const shift = this.shifts.find(s => s.id == shiftId);
                    if (shift && shift.visit) {
                        // ✅ NEW DEBUG LINES - Check raw visit data
                        console.log('Raw shift.visit data:', shift.visit);
                        console.log('Raw clock_in_time:', shift.visit.clock_in_time);
                        console.log('Raw clock_out_time:', shift.visit.clock_out_time);
                        this.selectedVisit = {
                            client_name: `${shift.client.first_name} ${shift.client.last_name}`,
                            caregiver_name: `${shift.caregiver.first_name} ${shift.caregiver.last_name}`,
                            clock_in_display: shift.visit.clock_in_time ?
                                this.formatTimeInUserTimezone(shift.visit.clock_in_time) :
                                'N/A',
                            clock_out_display: shift.visit.clock_out_time ?
                                this.formatTimeInUserTimezone(shift.visit.clock_out_time) :
                                'N/A',
                            clock_in_signature_url: shift.visit.clock_in_signature_url || '',
                            clock_out_signature_url: shift.visit.clock_out_signature_url || ''
                        };
                        console.log('Selected visit data:', this.selectedVisit); // ✅ DEBUG LINE ADDED
                        this.showSignaturesModal = true;
                    }
                },

                // ✅ FIXED: Event delegation for signature buttons
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

                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    this.calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: this.shifts.map(shift => {
                            // ✅ ENHANCEMENT: Determine background color based on status
                            let backgroundColor = '#4f46e5'; // Default blue
                            let borderColor = '#4f46e5';
                            let className = '';

                            if (shift.status === 'completed') {
                                backgroundColor = '#10b981'; // Green
                                borderColor = '#059669';
                                className = 'shift-completed';
                            } else if (shift.status === 'in_progress') {
                                backgroundColor = '#f59e0b'; // Orange
                                borderColor = '#d97706';
                                className = 'shift-in-progress';
                            }

                            return {
                                id: shift.id,
                                title: `${shift.client.first_name} w/ ${shift.caregiver.first_name}`,
                                start: shift.start_time,
                                end: shift.end_time,
                                backgroundColor: backgroundColor,
                                borderColor: borderColor,
                                className: className,
                                extendedProps: {
                                    client_id: shift.client_id,
                                    caregiver_id: shift.caregiver_id,
                                    notes: shift.notes,
                                    status: shift.status,
                                    visit: shift.visit || null
                                }
                            };
                        }),

                        // ✅ FIXED: Event content with proper button delegation
                        eventContent: (arg) => {
                            let eventHtml = `<b>${arg.timeText}</b> <i>${arg.event.title}</i>`;

                            const notes = arg.event.extendedProps.notes;
                            const visit = arg.event.extendedProps.visit;

                            // ✅ NEW: Show actual clock-in/out times if they exist
                            if (visit) {
                                let visitTimesHtml = '<div class="visit-times">';

                                if (visit.clock_in_time) {
                                    const clockInTime = this.formatTimeInUserTimezone(visit.clock_in_time);
                                    visitTimesHtml += `ACTUAL: In ${clockInTime}`;
                                }

                                if (visit.clock_out_time) {
                                    const clockOutTime = this.formatTimeInUserTimezone(visit.clock_out_time);
                                    if (visit.clock_in_time) {
                                        visitTimesHtml += ` | Out ${clockOutTime}`;
                                    } else {
                                        visitTimesHtml += `ACTUAL: Out ${clockOutTime}`;
                                    }
                                }

                                visitTimesHtml += '</div>';
                                eventHtml += visitTimesHtml;

                                // ✅ FIXED: Use data attribute for button handling
                                if (this.isAdmin && arg.event.extendedProps.status === 'completed' &&
                                    (visit.clock_in_signature_url || visit.clock_out_signature_url)) {
                                    eventHtml += `<div class="mt-2">
                                        <button data-view-signatures="${arg.event.id}" 
                                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">
                                            View Signatures
                                        </button>
                                    </div>`;
                                }
                            }

                            if (notes) {
                                // If notes exist, add them to the event's HTML.
                                eventHtml += `<div class="shift-notes">Note: ${notes}</div>`;
                            }

                            return {
                                html: eventHtml
                            };
                        },

                        // ✅ SECURITY FIX: Make calendar read-only for non-admins
                        eventDidMount: (info) => {
                            if (!this.isAdmin) {
                                info.el.style.cursor = 'default'; // Change cursor to show it's not clickable
                            }
                        },

                        // ✅ SECURITY FIX: Only allow admins to click on dates to add new shifts
                        dateClick: this.isAdmin ? (info) => {
                            let startTime;
                            if (info.allDay) {
                                startTime = new Date(info.dateStr + 'T09:00:00');
                            } else {
                                startTime = info.date;
                            }
                            const endTime = new Date(startTime.getTime() + 60 * 60 * 1000);
                            this.newShift.start_time = this.formatDateTimeLocal(startTime);
                            this.newShift.end_time = this.formatDateTimeLocal(endTime);
                            this.showAddModal = true;
                        } : null, // Set to null for non-admins

                        // ✅ SECURITY FIX: Only allow admins to click on events to edit them
                        eventClick: this.isAdmin ? (info) => {
                            // ✅ FIXED: Only open edit modal if clicking on the event itself, not buttons
                            if (!info.jsEvent.target.hasAttribute('data-view-signatures')) {
                                this.editShift.id = info.event.id;
                                this.editShift.client_id = info.event.extendedProps.client_id;
                                this.editShift.caregiver_id = info.event.extendedProps.caregiver_id;
                                this.editShift.start_time = this.formatDateTimeLocal(info.event.start);
                                this.editShift.end_time = this.formatDateTimeLocal(info.event.end);
                                this.editShift.notes = info.event.extendedProps.notes;
                                this.showEditModal = true;
                            }
                        } : null // Set to null for non-admins
                    });
                    this.calendar.render();
                    toastr.options.progressBar = true;
                    toastr.options.positionClass = 'toast-bottom-right';
                },

                submitAddForm() {
                    fetch('{{ route('shifts.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.newShift)
                        })
                        .then(res => res.json().then(data => ({
                            ok: res.ok,
                            data
                        })))
                        .then(({
                            ok,
                            data
                        }) => {
                            if (ok) {
                                this.calendar.addEvent(data.shift);
                                this.showAddModal = false;
                                this.newShift = {
                                    client_id: '',
                                    caregiver_id: '',
                                    start_time: '',
                                    end_time: '',
                                    notes: ''
                                };
                                toastr.success('New shift created successfully!');
                            } else {
                                throw data;
                            }
                        }).catch(error => this.handleFormError(error));
                },

                submitEditForm() {
                    fetch(`/shifts/${this.editShift.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.editShift)
                        })
                        .then(res => res.json().then(data => ({
                            ok: res.ok,
                            data
                        })))
                        .then(({
                            ok,
                            data
                        }) => {
                            if (ok) {
                                const event = this.calendar.getEventById(this.editShift.id);
                                if (event) event.remove();
                                this.calendar.addEvent(data.shift);
                                this.showEditModal = false;
                                toastr.success('Shift updated successfully!');
                            } else {
                                throw data;
                            }
                        }).catch(error => this.handleFormError(error));
                },

                deleteShift() {
                    if (!confirm('Are you sure you want to delete this shift?')) return;
                    fetch(`/shifts/${this.editShift.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json().then(data => ({
                            ok: res.ok,
                            data
                        })))
                        .then(({
                            ok,
                            data
                        }) => {
                            if (ok) {
                                const event = this.calendar.getEventById(this.editShift.id);
                                if (event) event.remove();
                                this.showEditModal = false;
                                toastr.info('Shift has been deleted.');
                            } else {
                                throw data;
                            }
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
    </script>
</x-app-layout>
