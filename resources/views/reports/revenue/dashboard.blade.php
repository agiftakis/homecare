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

            {{-- ✅ RESPONSIVE FIX: Using a flex-wrap layout for robustness --}}
            <div class="flex flex-wrap -mx-3">
                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                    <div class="bg-green-100 dark:bg-green-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6 h-full">
                        <h3 class="text-sm font-medium text-green-700 dark:text-green-300 tracking-wider uppercase">
                            Total Revenue
                        </h3>
                        <p class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">
                            ${{ $metrics['total_revenue'] }}
                        </p>
                    </div>
                </div>

                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                    <div class="bg-yellow-100 dark:bg-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6 h-full">
                        <h3 class="text-sm font-medium text-yellow-700 dark:text-yellow-300 tracking-wider uppercase">
                            Outstanding Balance
                        </h3>
                        <p class="mt-2 text-3xl font-bold text-yellow-900 dark:text-yellow-100">
                            ${{ $metrics['outstanding_balance'] }}
                        </p>
                    </div>
                </div>
                
                <div class="w-full md:w-1/3 px-3">
                     <div class="bg-red-100 dark:bg-red-800/20 overflow-hidden shadow-sm sm:rounded-lg p-6 h-full">
                        <h3 class="text-sm font-medium text-red-700 dark:text-red-300 tracking-wider uppercase">
                            Overdue Balance
                        </h3>
                        <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">
                            ${{ $metrics['overdue_balance'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Revenue Trend
                    </h3>
                    <div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get the data prepared by the controller
            const chartData = @json($metrics['revenue_chart_data']);
    
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: chartData.data,
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>