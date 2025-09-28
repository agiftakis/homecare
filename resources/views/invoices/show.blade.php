<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Invoice {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('invoices.pdf', $invoice) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Download PDF
                </a>
                @if ($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        Edit Invoice
                    </a>
                @endif
                <a href="{{ route('invoices.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                @switch($invoice->status)
                    @case('paid')
                        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <strong>Paid</strong> - This invoice was paid on {{ $invoice->paid_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    @break

                    @case('sent')
                        @if ($invoice->due_date < now())
                            <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-red-800 dark:text-red-200">
                                        <strong>Overdue</strong> - This invoice was due on
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Sent</strong> - This invoice was sent on
                                        {{ $invoice->sent_at->format('M d, Y') }} and is due
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @break

                    @default
                        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    <strong>Draft</strong> - This invoice has not been sent yet
                                </p>
                            </div>
                        </div>
                @endswitch
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    <div class="border-b border-gray-200 dark:border-gray-600 pb-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">From:</h3>
                                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
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
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Bill To:</h3>
                                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number:
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $invoice->invoice_number }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Date:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->invoice_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->due_date->format('M d, Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Period:
                                    </dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $invoice->period_start->format('M d') }} -
                                        {{ $invoice->period_end->format('M d, Y') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                    <dd class="text-sm">
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

                    <div
                        class="mb-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Billing Policy:</strong> Visits under 1 hour are billed at the minimum 1-hour
                                rate.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Service
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Caregiver
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Time
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actual Hours
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Billable Hours
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Rate
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Amount
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($invoice->items as $item)
                                    @php
                                        $visit = $item->visit;
                                        $shift = $visit ? $visit->shift : null;
                                        // ✅ FIX: Use abs() to ensure actual hours are never negative.
                                        $actualHours = $shift ? abs($shift->getActualHours()) : 0;
                                        $isMinimumBilling = $shift ? $shift->isMinimumBilling() : false;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item->service_date->format('M d, Y') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item->service_type }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $item->caregiver_name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $item->start_time->format('H:i') }} -
                                            {{ $item->end_time->format('H:i') }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($actualHours, 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item->hours_worked, 2) }}
                                            @if ($isMinimumBilling)
                                                <span class="text-xs text-orange-600 dark:text-orange-400 ml-1">(min.
                                                    1hr)</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                            ${{ number_format($item->hourly_rate, 2) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                                            ${{ number_format($item->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                        <div class="flex justify-end">
                            <div class="w-full max-w-sm space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span
                                        class="text-gray-900 dark:text-gray-100">${{ number_format($invoice->subtotal, 2) }}</span>
                                </div>
                                @if ($invoice->tax_rate > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Tax
                                            ({{ number_format($invoice->tax_rate * 100, 2) }}%):</span>
                                        <span
                                            class="text-gray-900 dark:text-gray-100">${{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-2">
                                    <div class="flex justify-between">
                                        <span
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total:</span>
                                        <span
                                            class="text-lg font-bold text-gray-900 dark:text-gray-100">${{ number_format($invoice->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($invoice->notes)
                        <div class="mt-8 border-t border-gray-200 dark:border-gray-600 pt-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Notes:</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $invoice->notes }}</p>
                        </div>
                    @endif

                    @if ($invoice->status !== 'paid')
                        <div class="mt-8 border-t border-gray-200 dark:border-gray-600 pt-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Update Status:</h4>
                            <div class="flex space-x-3">
                                @if ($invoice->status === 'draft')
                                    <form action="{{ route('invoices.update', $invoice) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="sent">
                                        <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                            Mark as Sent
                                        </button>
                                    </form>
                                @endif

                                @if (in_array($invoice->status, ['draft', 'sent']))
                                    <form action="{{ route('invoices.update', $invoice) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="paid">
                                        <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                            Mark as Paid
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
