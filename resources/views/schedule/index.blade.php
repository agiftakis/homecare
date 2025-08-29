<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"
                     x-data="schedule()"
                     x-init="initCalendar()">

                    <!-- FullCalendar Container -->
                    <div id='calendar' class="text-gray-900 dark:text-gray-100"></div>

                    <!-- Add Shift Modal -->
                    <div x-show="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showAddModal = false" style="display: none;">
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl" @click.away="showAddModal = false">
                            <h3 class="text-lg font-medium mb-4">Add New Shift</h3>
                            <form @submit.prevent="submitAddForm">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Client, Caregiver, Start/End Times, Notes -->
                                    @include('schedule.partials.shift-form-fields', ['shift' => 'newShift'])
                                </div>
                                <div class="mt-6 flex justify-end space-x-4">
                                    <x-secondary-button type="button" @click="showAddModal = false">Cancel</x-secondary-button>
                                    <x-primary-button type="submit">Save Shift</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Shift Modal -->
                    <div x-show="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showEditModal = false" style="display: none;">
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl" @click.away="showEditModal = false">
                            <h3 class="text-lg font-medium mb-4">Edit Shift</h3>
                            <form @submit.prevent="submitEditForm">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Client, Caregiver, Start/End Times, Notes -->
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

                </div>
            </div>
        </div>
    </div>

    <script>
        function schedule() {
            return {
                showAddModal: false,
                showEditModal: false,
                calendar: null,
                shifts: @json($shifts),
                newShift: { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
                editShift: { id: null, client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' },
                
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
                        dateClick: (info) => {
                            this.newShift.start_time = `${info.dateStr}T09:00`;
                            this.newShift.end_time = `${info.dateStr}T17:00`;
                            this.showAddModal = true;
                        },
                        eventClick: (info) => {
                            this.editShift.id = info.event.id;
                            this.editShift.client_id = info.event.extendedProps.client_id;
                            this.editShift.caregiver_id = info.event.extendedProps.caregiver_id;
                            this.editShift.start_time = info.event.startStr.slice(0, 16);
                            this.editShift.end_time = info.event.endStr.slice(0, 16);
                            this.editShift.notes = info.event.extendedProps.notes;
                            this.showEditModal = true;
                        }
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
                    }).catch(this.handleFormError);
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
                    }).catch(this.handleFormError);
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
                    }).catch(this.handleFormError);
                },

                handleFormError(errorData) {
                    let errorMessages = 'An unexpected error occurred.';
                    if (errorData && errorData.errors) {
                        errorMessages = Object.values(errorData.errors).flat().join('<br>');
                    }
                    toastr.error(errorMessages);
                }
            }
        }
    </script>
</x-app-layout>
