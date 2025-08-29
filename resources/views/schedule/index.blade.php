<!-- File: resources/views/schedule/index.blade.php -->
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
                    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showModal = false" style="display: none;">
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl w-full max-w-2xl" @click.away="showModal = false">
                            <h3 class="text-lg font-medium mb-4">Add New Shift</h3>
                            <form @submit.prevent="submitShiftForm">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Client -->
                                    <div>
                                        <label for="client_id" class="block text-sm font-medium">Client</label>
                                        <select id="client_id" name="client_id" x-model="newShift.client_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">Select a Client</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Caregiver -->
                                    <div>
                                        <label for="caregiver_id" class="block text-sm font-medium">Caregiver</label>
                                        <select id="caregiver_id" name="caregiver_id" x-model="newShift.caregiver_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">Select a Caregiver</option>
                                            @foreach($caregivers as $caregiver)
                                                <option value="{{ $caregiver->id }}">{{ $caregiver->first_name }} {{ $caregiver->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Start Time -->
                                    <div>
                                        <label for="start_time" class="block text-sm font-medium">Start Time</label>
                                        <input type="datetime-local" id="start_time" name="start_time" x-model="newShift.start_time" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:[color-scheme:dark]">
                                    </div>
                                    <!-- End Time -->
                                    <div>
                                        <label for="end_time" class="block text-sm font-medium">End Time</label>
                                        <input type="datetime-local" id="end_time" name="end_time" x-model="newShift.end_time" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:[color-scheme:dark]">
                                    </div>
                                    <!-- Notes -->
                                    <div class="md:col-span-2">
                                        <label for="notes" class="block text-sm font-medium">Notes</label>
                                        <textarea id="notes" name="notes" x-model="newShift.notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end space-x-4">
                                    <x-secondary-button type="button" @click="showModal = false">Cancel</x-secondary-button>
                                    <x-primary-button type="submit">Save Shift</x-primary-button>
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
                showModal: false,
                calendar: null,
                shifts: @json($shifts),
                newShift: {
                    client_id: '',
                    caregiver_id: '',
                    start_time: '',
                    end_time: '',
                    notes: ''
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
                            title: `${shift.client.first_name} w/ ${shift.caregiver.first_name}`,
                            start: shift.start_time,
                            end: shift.end_time,
                            backgroundColor: '#4f46e5',
                            borderColor: '#4f46e5'
                        })),
                        dateClick: (info) => {
                            this.newShift.start_time = `${info.dateStr}T09:00`;
                            this.newShift.end_time = `${info.dateStr}T17:00`;
                            this.showModal = true;
                        }
                    });
                    this.calendar.render();
                    toastr.options.progressBar = true;
                    toastr.options.positionClass = 'toast-bottom-right';
                },
                submitShiftForm() {
                    fetch('{{ route("shifts.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newShift)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.calendar.addEvent({
                                title: data.shift.title,
                                start: data.shift.start,
                                end: data.shift.end,
                                backgroundColor: '#4f46e5',
                                borderColor: '#4f46e5'
                            });
                            this.showModal = false;
                            this.newShift = { client_id: '', caregiver_id: '', start_time: '', end_time: '', notes: '' };
                            toastr.success('New shift created successfully!');
                        } else {
                            // Handle validation errors
                            let errorMessages = Object.values(data.errors).flat().join('\n');
                            toastr.error(errorMessages || 'An error occurred.');
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
