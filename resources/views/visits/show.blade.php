<x-app-layout>
    {{-- ✅ CORRECTED: Use @push to send this script to the layout's 'scripts' stack --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Visit Verification
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                    {{-- ✅ CORRECTED: Added dark mode text color for better visibility --}}
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-blue-200">
                        {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('l, F jS') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                        -
                        {{ \Carbon\Carbon::parse($shift->end_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                    </p>
                </div>
                <div class="p-6">
                    <p class="font-bold text-gray-800 dark:text-gray-200">Client: {{ $shift->client->full_name }}
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">Location: {{ $shift->client->address }}</p>
                </div>
            </div>

            {{-- ✅ DATE VALIDATION: Show warning if shift date hasn't arrived yet --}}
            @if (!$isShiftDateValid)
                <div
                    class="bg-red-100 dark:bg-red-900/50 border border-red-500 text-red-800 dark:text-red-200 px-6 py-8 rounded-lg text-center">
                    <p class="text-xl font-bold">The Scheduled Shift Date Has Not Arrived Yet!</p>
                    <p class="mt-2 text-sm">You can only verify visits on or after the scheduled shift date.</p>
                </div>
            @else
                {{-- Only show the verification interface if the date is valid --}}
                <div x-data="visitVerification(
                    {{ $shift->id }},
                    {{ $visit->id ?? 'null' }},
                    '{{ $visit && $visit->clock_out_time ? 'completed' : ($visit ? 'in_progress' : 'pending') }}',
                    '{{ $visit ? \Carbon\Carbon::parse($visit->clock_in_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') : '' }}',
                    `{{ $visit && $visit->progress_notes ? addslashes($visit->progress_notes) : '' }}`
                )">

                    {{-- On-screen Error Message Display --}}
                    <div x-show="errorMessage" x-cloak
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Error:</strong>
                        <span class="block sm:inline" x-text="errorMessage"></span>
                    </div>

                    <div x-show="status === 'pending'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        {{-- ✅ CORRECTED: Added dark mode text color for better visibility --}}
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-blue-200">Clock-In Signature</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please have the Client or Family Member
                            sign below to confirm you are starting your shift.</p>
                        <div
                            class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md">
                            <canvas x-ref="signaturePadIn" class="w-full h-48"></canvas>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <button @click="clearSignature('in')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Clear</button>
                            <x-primary-button @click="submitClockIn()" x-bind:disabled="loading">
                                <span x-show="!loading">Clock In</span>
                                <span x-show="loading">Processing...</span>
                            </x-primary-button>
                        </div>
                    </div>

                    <div x-show="status === 'in_progress'"
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">
                        <div
                            class="bg-green-100 dark:bg-green-900/50 border border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                            <p class="font-bold">Shift In Progress</p>
                            <p>Clocked in at: <span x-text="clockInTimeDisplay"></span></p>
                        </div>
                        
                        {{-- ✅ STEP 1: ADD NOTES TEXTAREA --}}
                        <div>
                            <label for="progress_notes" class="block text-lg font-semibold mb-2 text-gray-900 dark:text-blue-200">Progress Notes</label>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Add any notes about the visit. These will be saved when you clock out.
                            </p>
                            <textarea id="progress_notes" x-model="progressNotes" rows="6"
                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                placeholder="Enter care notes here..."></textarea>
                        </div>

                        <div>
                           <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-blue-200">Clock-Out Signature</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please have the Client or Family Member
                                sign below to confirm you are ending your shift.</p>
                            <div
                                class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md">
                                <canvas x-ref="signaturePadOut" class="w-full h-48"></canvas>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <button @click="clearSignature('out')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Clear</button>
                            <x-primary-button @click="submitClockOut()" x-bind:disabled="loading">
                                <span x-show="!loading">Clock Out</span>
                                <span x-show="loading">Processing...</span>
                            </x-primary-button>
                        </div>
                    </div>

                    <div x-show="status === 'completed'"
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                        <div
                            class="mb-4 bg-blue-100 dark:bg-blue-900/50 border border-blue-500 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg">
                            <p class="font-bold">Visit Completed</p>
                            <p>Clocked In:
                                {{ $visit? \Carbon\Carbon::parse($visit->clock_in_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A'): '' }}
                                | Clocked Out:
                                {{ $visit? \Carbon\Carbon::parse($visit->clock_out_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A'): '' }}
                            </p>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400">Thank you for your work!</p>
                         <a href="{{ route('schedule.index') }}" class="mt-4 inline-block text-blue-500 hover:underline">Return to Schedule</a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($isShiftDateValid)
        <script>
            function visitVerification(shiftId, visitId, initialStatus, initialClockInTime, initialNotes) {
                return {
                    shiftId: shiftId,
                    visitId: visitId,
                    status: initialStatus,
                    loading: false,
                    errorMessage: '',
                    signaturePadIn: null,
                    signaturePadOut: null,
                    clockInTimeDisplay: initialClockInTime,
                    // ✅ STEP 2: Add Alpine property for notes
                    progressNotes: initialNotes,

                    init() {
                        this.$watch('status', (newStatus) => {
                             this.$nextTick(() => {
                                if (newStatus === 'pending' && !this.signaturePadIn) {
                                    this.signaturePadIn = new SignaturePad(this.$refs.signaturePadIn, { penColor: '#60A5FA' });
                                } else if (newStatus === 'in_progress' && !this.signaturePadOut) {
                                    this.signaturePadOut = new SignaturePad(this.$refs.signaturePadOut, { penColor: '#60A5FA' });
                                }
                            });
                        });
                        // Fire the watcher manually on init to set up the correct pad
                        this.$nextTick(() => {
                            if (this.status === 'pending') {
                                this.signaturePadIn = new SignaturePad(this.$refs.signaturePadIn, { penColor: '#60A5FA' });
                            } else if (this.status === 'in_progress') {
                                this.signaturePadOut = new SignaturePad(this.$refs.signaturePadOut, { penColor: '#60A5FA' });
                            }
                        });
                    },
                    clearSignature(type) {
                        if (type === 'in' && this.signaturePadIn) this.signaturePadIn.clear();
                        if (type === 'out' && this.signaturePadOut) this.signaturePadOut.clear();
                    },

                    async submitClockIn() {
                        this.errorMessage = '';
                        if (this.signaturePadIn.isEmpty()) {
                            this.errorMessage = 'Please provide a signature first.';
                            return;
                        }
                        this.loading = true;
                        const signatureData = this.signaturePadIn.toDataURL('image/png');
                        try {
                            const response = await fetch(`/shifts/${this.shiftId}/clock-in`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    signature: signatureData
                                })
                            });
                           
                            if (!response.ok) {
                                const data = await response.json();
                                throw new Error(data.message || 'An error occurred.');
                            }
                            
                            const data = await response.json();
                           
                            // ✅ NO MORE PAGE RELOAD
                            this.visitId = data.visit_id; // Get new visit ID from the response
                            // Set the time for display
                            this.clockInTimeDisplay = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
                            this.status = 'in_progress'; // Switch to clock-out view

                        } catch (error) {
                            this.errorMessage = error.message;
                        } finally {
                           this.loading = false;
                        }
                    },

                    async submitClockOut() {
                        this.errorMessage = '';
                        if (this.signaturePadOut.isEmpty()) {
                            this.errorMessage = 'Please provide a signature first.';
                            return;
                        }
                        this.loading = true;
                        const signatureData = this.signaturePadOut.toDataURL('image/png');
                        try {
                            const response = await fetch(`/visits/${this.visitId}/clock-out`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                // ✅ STEP 3: Send notes along with the signature
                                body: JSON.stringify({
                                    signature: signatureData,
                                    progress_notes: this.progressNotes 
                                })
                            });
                             if (!response.ok) {
                                const data = await response.json();
                                throw new Error(data.message || 'An error occurred.');
                            }
                            const data = await response.json();
                            // ✅ NO MORE PAGE RELOAD
                            this.status = 'completed'; // Switch to completed view
                        } catch (error) {
                            this.errorMessage = error.message;
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endif
</x-app-layout>
