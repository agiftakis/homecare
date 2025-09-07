<x-app-layout>
    {{-- Add the Signature Pad library via the scripts slot --}}
    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Visit Verification
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div x-data="visitVerification(
                {{ $shift->id }},
                {{ $visit->id ?? 'null' }},
                '{{ $visit && $visit->clock_out_time ? 'completed' : ($visit ? 'in_progress' : 'pending') }}'
            )">

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold">{{ \Carbon\Carbon::parse($shift->start_time)->format('l, F jS') }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($shift->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('g:i A') }}</p>
                    </div>
                    <div class="p-6">
                        <p class="font-bold text-gray-800 dark:text-gray-200">Client: {{ $shift->client->full_name }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Location: {{ $shift->client->address }}</p>
                    </div>
                </div>

                <div x-show="status === 'pending'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-2">Clock-In Signature</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please sign below to confirm you are starting your shift.</p>
                    <div class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md">
                        <canvas x-ref="signaturePadIn" class="w-full h-48"></canvas>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button @click="clearSignature('in')" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Clear</button>
                        <x-primary-button @click="submitClockIn()" :disabled="loading">
                            <span x-show="!loading">Clock In</span>
                            <span x-show="loading">Processing...</span>
                        </x-primary-button>
                    </div>
                </div>

                <div x-show="status === 'in_progress'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="mb-6 bg-green-100 dark:bg-green-900/50 border border-green-500 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                        <p class="font-bold">Shift In Progress</p>
                        <p>Clocked in at: {{ $visit ? \Carbon\Carbon::parse($visit->clock_in_time)->format('g:i A') : '' }}</p>
                    </div>

                    <h3 class="text-lg font-semibold mb-2">Clock-Out Signature</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please sign below to confirm you are ending your shift.</p>
                    <div class="bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md">
                        <canvas x-ref="signaturePadOut" class="w-full h-48"></canvas>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button @click="clearSignature('out')" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Clear</button>
                        <x-primary-button @click="submitClockOut()" :disabled="loading">
                            <span x-show="!loading">Clock Out</span>
                            <span x-show="loading">Processing...</span>
                        </x-primary-button>
                    </div>
                </div>

                <div x-show="status === 'completed'" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                     <div class="mb-4 bg-blue-100 dark:bg-blue-900/50 border border-blue-500 text-blue-800 dark:text-blue-200 px-4 py-3 rounded-lg">
                        <p class="font-bold">Visit Completed</p>
                        <p>Clocked In: {{ $visit ? \Carbon\Carbon::parse($visit->clock_in_time)->format('g:i A') : '' }} | Clocked Out: {{ $visit ? \Carbon\Carbon::parse($visit->clock_out_time)->format('g:i A') : '' }}</p>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Thank you for your work!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function visitVerification(shiftId, visitId, initialStatus) {
            return {
                shiftId: shiftId,
                visitId: visitId,
                status: initialStatus,
                loading: false,
                signaturePadIn: null,
                signaturePadOut: null,

                init() {
                    // Initialize the correct signature pad based on the status
                    this.$nextTick(() => {
                        if (this.status === 'pending') {
                            this.signaturePadIn = new SignaturePad(this.$refs.signaturePadIn);
                        } else if (this.status === 'in_progress') {
                            this.signaturePadOut = new SignaturePad(this.$refs.signaturePadOut);
                        }
                    });
                },

                clearSignature(type) {
                    if (type === 'in' && this.signaturePadIn) this.signaturePadIn.clear();
                    if (type === 'out' && this.signaturePadOut) this.signaturePadOut.clear();
                },

                async submitClockIn() {
                    if (this.signaturePadIn.isEmpty()) {
                        alert('Please provide a signature first.');
                        return;
                    }

                    this.loading = true;
                    const signatureData = this.signaturePadIn.toDataURL('image/png');

                    try {
                        const response = await fetch(`/shifts/${this.shiftId}/clock-in`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ signature: signatureData })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred.');
                        }
                        
                        // Success, reload the page to show the new state
                        window.location.reload();

                    } catch (error) {
                        alert('Error: ' + error.message);
                        this.loading = false;
                    }
                },

                async submitClockOut() {
                    if (this.signaturePadOut.isEmpty()) {
                        alert('Please provide a signature first.');
                        return;
                    }

                    this.loading = true;
                    const signatureData = this.signaturePadOut.toDataURL('image/png');

                    try {
                        const response = await fetch(`/visits/${this.visitId}/clock-out`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ signature: signatureData })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred.');
                        }
                        
                        // Success, reload the page to show the new state
                        window.location.reload();

                    } catch (error) {
                        alert('Error: ' + error.message);
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>