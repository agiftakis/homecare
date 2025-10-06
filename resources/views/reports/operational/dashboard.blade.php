<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Operational Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter by Date Range</h3>
                    <form method="GET" action="{{ route('reports.operational') }}" class="flex flex-col sm:flex-row sm:items-end sm:space-x-4 space-y-4 sm:space-y-0">
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 tracking-wider uppercase">
                        Total Caregiver Hours
                    </h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $metrics['total_hours_worked'] }}
                    </p>
                </div>

                </div>

        </div>
    </div>
</x-app-layout>