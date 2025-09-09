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
                <div class="p-6 text-gray-900 dark:text-gray-100"
                     x-data="schedule({{ $is_admin ? 'true' : 'false' }})" {{-- ✅ SECURITY FIX: Pass admin status to JS --}}
                     x-init="initCalendar()">

                    <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>

                    {{-- ✅ SECURITY FIX: These modals will ONLY be rendered for agency admins --}}
                    @if ($is_admin)
                        <div x-show="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showAddModal = false" style="display: none;">
                            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl" @click.away="showAddModal = false">
                                <h3 class="text-lg font-medium mb-4">Add New Shift</h3>
                                <form @submit.prevent="submitAddForm">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Assuming this partial binds to 'newShift' --}}
                                        @include('schedule.partials.shift-form-fields', ['shift' => 'newShift'])
                                    </div>
                                    <div class="mt-6 flex justify-end space-x-4">
                                        <x-secondary-button type="button" @click="showAddModal = false">Cancel</x-secondary-button>
                                        <x-primary-button type="submit">Save Shift</x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div x-show="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showEditModal = false" style="display: none;">
                            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl" @click.away="showEditModal = false">
                                <h3 class="text-lg font-medium mb-4">Edit Shift</h3>
                                <form @submit.prevent="submitEditForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Assuming this partial binds to 'editShift' --}}
                                        @include('schedule.partials.shift-form-fields', ['shift' => 'editShift'])
                                    </div>
                                    <div class="mt-6 flex justify-between">
                                        <x-danger-button type="button" @click="deleteShift()">Delete</x-danger-button>
                                        <div class="space-x-4">
                                            <x-secondary-button type="button" @click="showEditModal = false">Cancel</x-secondary-button>
                                            <x-primary-button type="submit">Update Shift</x-primary-button>
                                        </div>
                                    </div>
                                </form>
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
    </style>

    <script>
        // ✅ SECURITY FIX: The schedule function now accepts the user's admin status
        function schedule(isAdmin) {
            return {
                showAddModal: false,
                showEditModal: false,
                calendar: null,
                shifts: @json($shifts),
                newShift: { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
                editShift: { id: null, client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
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

                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    this.calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: this.shifts.map(shift => ({
                            id: shift.id,
                            title: `${shift.client.first_name} w/ ${shift.caregiver.first_name}`,
                            start: shift.start_time,
                            end: shift.end_time,
                            backgroundColor: '#4f46e5',
                            borderColor: '#4f46e5',
                            extendedProps: {
                                client_id: shift.client_id,
                                caregiver_id: shift.caregiver_id,
                                notes: shift.notes
                            }
                        })),
                        
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
                            this.editShift.id = info.event.id;
                            this.editShift.client_id = info.event.extendedProps.client_id;
                            this.editShift.caregiver_id = info.event.extendedProps.caregiver_id;
                            this.editShift.start_time = this.formatDateTimeLocal(info.event.start);
                            this.editShift.end_time = this.formatDateTimeLocal(info.event.end);
                            this.editShift.notes = info.event.extendedProps.notes;
                            this.showEditModal = true;
                        } : null // Set to null for non-admins
                    });
                    this.calendar.render();
                    toastr.options.progressBar = true;
                    toastr.options.positionClass = 'toast-bottom-right';
                },

                submitAddForm() {
                    fetch('{{ route("shifts.store") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newShift)
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok) {
                            this.calendar.addEvent(data.shift);
                            this.showAddModal = false;
                            this.newShift = { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' };
                            toastr.success('New shift created successfully!');
                        } else {
                            throw data;
                        }
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
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
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
