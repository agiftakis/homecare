<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Invoice') }} {{ $invoice->invoice_number }}
            </h2>
            <a href="{{ route('invoices.show', $invoice) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Back to Invoice
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-100 dark:text-gray-100">
                    
                    <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="invoice_date" value="Invoice Date *" />
                                <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" 
                                              :value="old('invoice_date', $invoice->invoice_date->format('Y-m-d'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('invoice_date')" />
                            </div>
                            
                            <div>
                                <x-input-label for="due_date" value="Due Date *" />
                                <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" 
                                              :value="old('due_date', $invoice->due_date->format('Y-m-d'))" required />
                                <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="hourly_rate" value="Hourly Rate ($) *" />
                                <x-text-input id="hourly_rate" name="hourly_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" 
                                              :value="old('hourly_rate', $invoice->items->first()->hourly_rate ?? 0)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('hourly_rate')" />
                            </div>
                            <div>
                                <x-input-label for="tax_rate" value="Tax Rate (%)" />
                                <x-text-input id="tax_rate" name="tax_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" 
                                              :value="old('tax_rate', $invoice->tax_rate * 100)" />
                                <x-input-error class="mt-2" :messages="$errors->get('tax_rate')" />
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <x-input-label for="notes" value="Invoice Notes (Optional)" />
                            <textarea name="notes" id="notes" rows="4"
                                      placeholder="Additional notes or payment instructions..."
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $invoice->notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-600 pt-6">
                            <a href="{{ route('invoices.show', $invoice) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>