<x-app-layout>
    <x-slot name="header">
        {{-- ✅ MOBILE OPTIMIZED: Responsive header with stacking buttons --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Invoice {{ $invoice->invoice_number }}
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <a href="{{ route('invoices.pdf', $invoice) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                    Download PDF
                </a>
                @if ($invoice->status !== 'paid' && $invoice->status !== 'void')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                        Edit Invoice
                    </a>
                @endif
                <a href="{{ route('invoices.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                    Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- ✅ MOBILE OPTIMIZED: Bright RED Error Message Banner --}}
            @if(session('error'))
                <div class="mb-4 sm:mb-6 bg-red-100 dark:bg-red-900 border-2 border-red-600 dark:border-red-500 rounded-lg p-4 sm:p-5 shadow-lg">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-red-600 dark:text-red-400 mr-2 sm:mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-base sm:text-lg font-bold text-red-800 dark:text-red-200">
                                ⚠️ ACTION BLOCKED
                            </p>
                            <p class="text-sm sm:text-base text-red-700 dark:text-red-300 mt-1 font-medium">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ✅ MOBILE OPTIMIZED: Void Status Banner --}}
            @if($invoice->status === 'void')
                <div class="mb-4 sm:mb-6 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-3 sm:p-4">
                    <div class="flex items-start sm:items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-xs sm:text-sm text-red-800 dark:text-red-200">
                                <strong>VOID</strong> - This invoice was voided on {{ $invoice->voided_at->format('M d, Y') }}
                                @if($invoice->voidedByUser)
                                    by {{ $invoice->voidedByUser->name }}
                                @endif
                            </p>
                            @if($invoice->replacementInvoice)
                                <p class="text-xs sm:text-sm text-red-800 dark:text-red-200 mt-1">
                                    Replacement Invoice: 
                                    <a href="{{ route('invoices.show', $invoice->replacementInvoice) }}" 
                                       class="underline font-semibold hover:text-red-600">
                                        {{ $invoice->replacementInvoice->invoice_number }}
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ✅ MOBILE OPTIMIZED: Reissued Invoice Information Banner --}}
            @if($invoice->isReissued() && $invoice->voidedInvoice)
                <div class="mb-4 sm:mb-6 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3 sm:p-4">
                    <div class="flex items-start sm:items-center">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>Reissued Invoice</strong> - This invoice replaces voided invoice 
                            <a href="{{ route('invoices.show', $invoice->voidedInvoice) }}" 
                               class="underline font-semibold hover:text-yellow-600">
                                {{ $invoice->voidedInvoice->invoice_number }}
                            </a>
                        </p>
                    </div>
                </div>
            @endif

            {{-- ✅ MOBILE OPTIMIZED: Status Banner --}}
            <div class="mb-4 sm:mb-6">
                @switch($invoice->status)
                    @case('paid')
                        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-3 sm:p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-xs sm:text-sm text-green-800 dark:text-green-200">
                                    <strong>Paid</strong> - This invoice was paid on {{ $invoice->paid_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    @break

                    @case('sent')
                        @if ($invoice->due_date < now())
                            <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-3 sm:p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-xs sm:text-sm text-red-800 dark:text-red-200">
                                        <strong>Overdue</strong> - This invoice was due on
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3 sm:p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-xs sm:text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Sent</strong> - This invoice was sent on
                                        {{ $invoice->sent_at->format('M d, Y') }} and is due
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @break

                    @case('void')
                        {{-- Void status already shown above, skip here --}}
                    @break

                    @default
                        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3 sm:p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                    <strong>Draft</strong> - This invoice has not been sent yet
                                </p>
                            </div>
                        </div>
                @endswitch
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">

                    {{-- ✅ MOBILE OPTIMIZED: From/Bill To section --}}
                    <div class="border-b border-gray-200 dark:border-gray-600 pb-4 sm:pb-6 mb-4 sm:mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 sm:mb-3">From:</h3>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->agency->name }}
                                    </p>
                                    @if ($invoice->agency->address)
                                        <p>{{ $invoice->agency->address }}</p>
                                    @endif
                                    <p>{{ $invoice->agency->contact_email }}</p>
                                    @if ($invoice->agency->phone)
                                        <p>{{ $invoice->agency->phone }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 sm:mb-3">Bill To:</h3>
                                <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->client_name }}
                                    </p>
                                    @if ($invoice->client_address)
                                        <p>{{ $invoice->client_address }}</p>
                                    @endif
                                    <p>{{ $invoice->client_email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ MOBILE OPTIMIZED: Invoice details --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
                        <div>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number:
                                    </dt>
                                    <dd class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Date:</dt>
                                    <dd class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->invoice_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Due Date:</dt>
                                    <dd class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->due_date->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Service Period:
                                    </dt>
                                    <dd class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->period_start->format('M d') }} -
                                        {{ $invoice->period_end->format('M d, Y') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                    <dd class="text-xs sm:text-sm">
                                        @switch($invoice->status)
                                            @case('paid')
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    Paid
                                                </span>
                                            @break

                                            @case('sent')
                                                @if ($invoice->due_date < now())
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                        Overdue
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                        Sent
                                                    </span>
                                                @endif
                                            @break

                                            @case('void')
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                    VOID
                                                </span>
                                            @break

                                            @default
                                                <span
                                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                                    Draft
                                                </span>
                                        @endswitch
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- ✅ MOBILE OPTIMIZED: Billing policy banner --}}
                    <div
                        class="mb-6 sm:mb-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-3 sm:p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-xs sm:text-sm text-blue-800 dark:text-blue-200">
                                <strong>Billing Policy:</strong> Visits under 1 hour are billed at the minimum 1-hour
                                rate.
                            </p>
                        </div>
                    </div>

                    {{-- ✅ MOBILE OPTIMIZED: Line items table with horizontal scroll --}}
                    <div class="overflow-x-auto -mx-4 sm:mx-0 mb-6 sm:mb-8">
                        <div class="inline-block min-w-full align-middle">
                            <div class="overflow-hidden sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Date
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Service
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Caregiver
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Time
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Actual Hrs
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Billable Hrs
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Rate
                                            </th>
                                            <th
                                                class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        @foreach ($invoice->items as $item)
                                            @php
                                                $visit = $item->visit;
                                                $shift = $visit ? $visit->shift : null;
                                                $actualHours = $shift ? abs($shift->getActualHours()) : 0;
                                                $isMinimumBilling = $shift ? $shift->isMinimumBilling() : false;
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $item->service_date->format('M d, Y') }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $item->service_type }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $item->caregiver_name }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-300">
                                                    {{ $item->start_time->format('H:i') }} -
                                                    {{ $item->end_time->format('H:i') }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100 text-right">
                                                    {{ number_format($actualHours, 2) }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100 text-right">
                                                    {{ number_format($item->hours_worked, 2) }}
                                                    @if ($isMinimumBilling)
                                                        <span class="text-xs text-orange-600 dark:text-orange-400 ml-1">(min.
                                                            1hr)</span>
                                                    @endif
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-gray-100 text-right">
                                                    ${{ number_format($item->hourly_rate, 2) }}
                                                </td>
                                                <td
                                                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                                                    ${{ number_format($item->line_total, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ MOBILE OPTIMIZED: Totals section --}}
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4 sm:pt-6">
                        <div class="flex justify-end">
                            <div class="w-full sm:max-w-sm space-y-2">
                                <div class="flex justify-between text-xs sm:text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span
                                        class="text-gray-900 dark:text-gray-100">${{ number_format($invoice->subtotal, 2) }}</span>
                                </div>
                                @if ($invoice->tax_rate > 0)
                                    <div class="flex justify-between text-xs sm:text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Tax
                                            ({{ number_format($invoice->tax_rate * 100, 2) }}%):</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100">${{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                                    <div class="flex justify-between">
                                        <span
                                            class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                        <span
                                            class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">${{ number_format($invoice->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($invoice->notes)
                        <div class="mt-6 sm:mt-8 border-t border-gray-200 dark:border-gray-600 pt-4 sm:pt-6">
                            <h4 class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Notes:</h4>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">{{ $invoice->notes }}</p>
                        </div>
                    @endif

                    {{-- ✅ MOBILE OPTIMIZED: Status update buttons --}}
                    @if ($invoice->status !== 'paid' && $invoice->status !== 'void')
                        <div class="mt-6 sm:mt-8 border-t border-gray-200 dark:border-gray-600 pt-4 sm:pt-6">
                            <h4 class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Update Status:</h4>
                            <div class="flex flex-col sm:flex-row gap-3">
                                @if ($invoice->status === 'draft')
                                    <form action="{{ route('invoices.markAsSent', $invoice) }}" method="POST"
                                        class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit"
                                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-sm sm:text-base">
                                            Mark as Sent
                                        </button>
                                    </form>
                                @endif

                                @if (in_array($invoice->status, ['draft', 'sent']))
                                    <form action="{{ route('invoices.markAsPaid', $invoice) }}" method="POST"
                                        class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit"
                                            class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-sm sm:text-base">
                                            Mark as Paid
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ✅ MOBILE OPTIMIZED: Void & Reissue Section --}}
                    @if($invoice->canBeVoided())
                        <div class="mt-6 sm:mt-8 border-t border-gray-200 dark:border-gray-600 pt-4 sm:pt-6">
                            <h4 class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Void & Reissue:</h4>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3 sm:mb-4">
                                Need to make corrections? Void this invoice and create a new corrected version as a draft.
                            </p>
                            <form action="{{ route('invoices.reissue', $invoice) }}" method="POST" class="w-full sm:w-auto inline-block">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Are you sure you want to void this invoice and create a new corrected version? This action cannot be undone. The original invoice will be marked as VOID and a new draft invoice will be created with the same data for you to edit.')"
                                        class="w-full sm:w-auto bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 text-sm sm:text-base">
                                    Void & Reissue Invoice
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>