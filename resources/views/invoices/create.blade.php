<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Generate Invoice') }}
            </h2>
            <a href="{{ route('invoices.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Back to Invoices
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Billing Policy Notice -->
                    <div class="mb-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Billing Policy:</strong> Any visit under 1 hour will be billed as 1 full hour (minimum billing requirement).
                            </p>
                        </div>
                    </div>
                    
                    <form action="{{ route('invoices.generate') }}" method="POST" x-data="invoiceGenerator()">
                        @csrf
                        
                        <!-- Client Selection -->
                        <div class="mb-6">
                            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Client *
                            </label>
                            <select name="client_id" id="client_id" required 
                                    x-model="selectedClient"
                                    @change="loadUnbilledVisits()"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Choose a client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date Range Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="period_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Period Start Date *
                                </label>
                                <input type="date" name="period_start" id="period_start" required
                                       x-model="periodStart"
                                       @change="loadUnbilledVisits()"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('period_start')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="period_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Period End Date *
                                </label>
                                <input type="date" name="period_end" id="period_end" required
                                       x-model="periodEnd"
                                       @change="loadUnbilledVisits()"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('period_end')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Invoice Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="invoice_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Invoice Date *
                                </label>
                                <input type="date" name="invoice_date" id="invoice_date" required
                                       value="{{ date('Y-m-d') }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('invoice_date')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Due Date *
                                </label>
                                <input type="date" name="due_date" id="due_date" required
                                       value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('due_date')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Tax Rate -->
                        <div class="mb-6">
                            <label for="tax_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tax Rate (%)
                            </label>
                            <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100"
                                   value="0" x-model="taxRate"
                                   class="w-full md:w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('tax_rate')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unbilled Visits Section -->
                        <div class="mb-6" x-show="unbilledVisits.length > 0" style="display: none;">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Unbilled Visits for Selected Period
                            </h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        <span x-text="selectedVisits.length"></span> visits selected
                                    </span>
                                    <div class="space-x-2">
                                        <button type="button" @click="selectAllVisits()" 
                                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            Select All
                                        </button>
                                        <button type="button" @click="deselectAllVisits()" 
                                                class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left">
                                                <input type="checkbox" @change="toggleAllVisits($event.target.checked)"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Date
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Time
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Caregiver
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Service Type
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Hours Worked
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Billable Hours
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Rate
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        <template x-for="visit in unbilledVisits" :key="visit.id">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-3">
                                                    <input type="checkbox" :value="visit.id" 
                                                           x-model="selectedVisits"
                                                           name="visit_ids[]"
                                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="visit.date"></td>
                                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300" x-text="visit.time_range"></td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="visit.caregiver_name"></td>
                                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300" x-text="visit.service_type"></td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="visit.actual_hours"></td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <span x-text="visit.hours"></span>
                                                    <span x-show="visit.is_minimum_billing" class="text-xs text-orange-600 dark:text-orange-400 ml-1">
                                                        (min. 1hr)
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="'$' + visit.hourly_rate"></td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100" x-text="'$' + visit.total"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Invoice Total Preview -->
                            <div class="mt-6 bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2" x-text="'$' + subtotal.toFixed(2)"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-300">Tax:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2" x-text="'$' + taxAmount.toFixed(2)"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-700 dark:text-gray-200 font-medium">Total:</span>
                                        <span class="font-bold text-lg text-gray-900 dark:text-gray-100 ml-2" x-text="'$' + totalAmount.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Visits Message -->
                        <div x-show="showNoVisitsMessage" style="display: none;" 
                             class="mb-6 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                    No unbilled visits found for the selected client and date range. Please adjust your selection.
                                </p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Invoice Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      placeholder="Additional notes or payment instructions..."
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('invoices.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    :disabled="selectedVisits.length === 0"
                                    :class="selectedVisits.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                    class="text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Generate Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function invoiceGenerator() {
            return {
                selectedClient: '',
                periodStart: '',
                periodEnd: '',
                taxRate: 0,
                unbilledVisits: [],
                selectedVisits: [],
                showNoVisitsMessage: false,
                
                get subtotal() {
                    return this.selectedVisits.reduce((total, visitId) => {
                        const visit = this.unbilledVisits.find(v => v.id == visitId);
                        return total + (visit ? parseFloat(visit.total.replace(',', '')) : 0);
                    }, 0);
                },
                
                get taxAmount() {
                    return this.subtotal * (this.taxRate / 100);
                },
                
                get totalAmount() {
                    return this.subtotal + this.taxAmount;
                },
                
                async loadUnbilledVisits() {
                    if (!this.selectedClient || !this.periodStart || !this.periodEnd) {
                        this.unbilledVisits = [];
                        this.selectedVisits = [];
                        this.showNoVisitsMessage = false;
                        return;
                    }
                    
                    try {
                        const response = await fetch('/api/unbilled-visits', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                client_id: this.selectedClient,
                                period_start: this.periodStart,
                                period_end: this.periodEnd
                            })
                        });
                        
                        const data = await response.json();
                        this.unbilledVisits = data.visits || [];
                        this.selectedVisits = [];
                        this.showNoVisitsMessage = this.unbilledVisits.length === 0;
                    } catch (error) {
                        console.error('Error loading unbilled visits:', error);
                        this.unbilledVisits = [];
                        this.selectedVisits = [];
                        this.showNoVisitsMessage = true;
                    }
                },
                
                selectAllVisits() {
                    this.selectedVisits = this.unbilledVisits.map(visit => visit.id);
                },
                
                deselectAllVisits() {
                    this.selectedVisits = [];
                },
                
                toggleAllVisits(checked) {
                    if (checked) {
                        this.selectAllVisits();
                    } else {
                        this.deselectAllVisits();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>