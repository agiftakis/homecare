<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Revenue Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter by Date Range</h3>
                    <form method="GET" action="{{ route('reports.revenue') }}" class="flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0">
                        <div>
                            <label for="start_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="end_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        </div>
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-100 dark:bg-green-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-green-700 dark:text-green-300 tracking-wider uppercase">
                        Total Revenue
                    </h3>
                    <p class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">
                        ${{ $metrics['total_revenue'] }}
                    </p>
                </div>

                <div class="bg-yellow-100 dark:bg-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-yellow-700 dark:text-yellow-300 tracking-wider uppercase">
                        Outstanding Balance
                    </h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-900 dark:text-yellow-100">
                        ${{ $metrics['outstanding_balance'] }}
                    </p>
                </div>
                
                <div class="bg-red-100 dark:bg-red-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-red-700 dark:text-red-300 tracking-wider uppercase">
                        Overdue Balance
                    </h3>
                    <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">
                        ${{ $metrics['overdue_balance'] }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>