<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Universal Welcome Message --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Dynamic welcome message based on user role --}}
                    @if (Auth::user()->role === 'client' && Auth::user()->client)
                        <h3 class="text-2xl font-bold">Welcome, {{ Auth::user()->client->first_name }}!</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Here's an overview of your care appointments and recent visits.</p>
                    @elseif (Auth::user()->role === 'caregiver' && Auth::user()->caregiver)
                        <h3 class="text-2xl font-bold">Welcome, {{ Auth::user()->caregiver->first_name }}!</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Here are your scheduled shifts.</p>
                    @else
                        <h3 class="text-2xl font-bold">Welcome, {{ $agency->name ?? 'Admin' }}!</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Here's a snapshot of your agency's activity today.</p>
                    @endif
                </div>
            </div>

            {{-- ============================================= --}}
            {{-- ================ CLIENT VIEW =============== --}}
            {{-- This entire section is new and only for clients --}}
            {{-- ============================================= --}}
            @if (Auth::user()->role === 'client')
                <div class="space-y-8">
                    {{-- Upcoming Appointments Section --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Upcoming Care Appointments</h3>
                        <div class="space-y-4">
                            @forelse ($upcomingShifts as $shift)
                                <div class="bg-white dark:bg-gray-900/80 p-4 rounded-lg border dark:border-gray-700 shadow-sm">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                        <div>
                                            <p class="font-bold text-base text-gray-900 dark:text-gray-100">
                                                @if($shift->caregiver)
                                                    Care Visit with {{ $shift->caregiver->first_name }} {{ $shift->caregiver->last_name }}
                                                @else
                                                    Care Visit (Caregiver to be assigned)
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('D, M j, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                                            </p>
                                            @if($shift->notes)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                    <span class="font-medium">Notes:</span> {{ $shift->notes }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="mt-4 sm:mt-0">
                                            <div class="flex items-center space-x-2">
                                                @if($shift->status === 'in_progress')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        In Progress
                                                    </span>
                                                @elseif($shift->status === 'completed')
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        Completed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Scheduled
                                                    </span>
                                                @endif
                                                <a href="{{ route('schedule.client') }}"
                                                    class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                    View Full Schedule
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <p class="text-gray-500 dark:text-gray-400">You have no upcoming appointments scheduled.</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Your care coordinator will schedule your appointments and you'll see them here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Recent Completed Visits Section --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Recent Care Visits</h3>
                        <div class="space-y-4">
                            @forelse ($recentShifts as $shift)
                                <div class="bg-white dark:bg-gray-900/80 p-4 rounded-lg border dark:border-gray-700 opacity-80">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-bold text-base text-gray-800 dark:text-gray-200">
                                                @if($shift->caregiver)
                                                    Care Visit with {{ $shift->caregiver->first_name }} {{ $shift->caregiver->last_name }}
                                                @else
                                                    Care Visit
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('D, M j, Y') }}
                                            </p>
                                            @if($shift->visit)
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Started: {{ \Carbon\Carbon::parse($shift->visit->clock_in_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                                                    @if($shift->visit->clock_out_time)
                                                        | Completed: {{ \Carbon\Carbon::parse($shift->visit->clock_out_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Completed
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <p class="text-gray-500 dark:text-gray-400">You have no completed visits yet.</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Your completed care visits will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Actions for Clients --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h3>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('schedule.client') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                View Full Schedule
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================= --}}
            {{-- ======== AGENCY ADMIN / SUPER ADMIN VIEW ======== --}}
            {{-- This entire section is hidden from caregivers and clients --}}
            {{-- ============================================= --}}
            @if (in_array(Auth::user()->role, ['agency_admin', 'super_admin']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-gray-500 dark:text-gray-400 font-medium">Active Clients</h4>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $clientCount }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-gray-500 dark:text-gray-400 font-medium">Active Caregivers</h4>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $caregiverCount }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                        <h4 class="text-gray-500 dark:text-gray-400 font-medium">Shifts Scheduled Today</h4>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $todaysShifts->count() }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        {{-- ✅ NEW: Client limit check for Add New Client button --}}
                        @if (Auth::user()->role === 'agency_admin')
                            <button id="addClientBtn"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                + Add New Client
                            </button>
                        @else
                            <a href="{{ route('clients.create') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                + Add New Client
                            </a>
                        @endif

                        <a href="{{ route('caregivers.create') }}"
                            class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            + Add New Caregiver
                        </a>
                        <a href="{{ route('schedule.index') }}"
                            class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg transition">
                            View Full Schedule
                        </a>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Today's Schedule</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Time</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Client</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Caregiver</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($todaysShifts as $shift)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $shift->client->first_name ?? 'N/A' }}
                                                {{ $shift->client->last_name ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $shift->caregiver->first_name ?? 'N/A' }}
                                                {{ $shift->caregiver->last_name ?? '' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    {{ ucfirst($shift->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No shifts scheduled for today.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================================= --}}
            {{-- ============== CAREGIVER VIEW =============== --}}
            {{-- This entire section is only for caregivers --}}
            {{-- ============================================= --}}
            @if (Auth::user()->role === 'caregiver')
                <div class="space-y-8">
                    {{-- Upcoming Shifts Section --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Upcoming Shifts</h3>
                        <div class="space-y-4">
                            @forelse ($upcoming_shifts as $shift)
                                <div class="bg-white dark:bg-gray-900/80 p-4 rounded-lg border dark:border-gray-700 shadow-sm">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                        <div>
                                            <p class="font-bold text-base text-gray-900 dark:text-gray-100">
                                                {{ $shift->client->first_name }} {{ $shift->client->last_name }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('D, M j, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }} -
                                                {{ \Carbon\Carbon::parse($shift->end_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('g:i A') }}
                                            </p>
                                        </div>
                                        <div class="mt-4 sm:mt-0">
                                            <a href="{{ route('visits.show', $shift) }}"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                Verify Visit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <p class="text-gray-500 dark:text-gray-400">You have no upcoming shifts.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Completed Shifts History Section --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Completed Shift History</h3>
                        <div class="space-y-4">
                            @forelse ($all_past_shifts as $shift)
                                <div class="bg-white dark:bg-gray-900/80 p-4 rounded-lg border dark:border-gray-700 opacity-80">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-bold text-base text-gray-800 dark:text-gray-200">
                                                {{ $shift->client->first_name }} {{ $shift->client->last_name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($shift->start_time)->setTimezone(Auth::user()->agency?->timezone ?? 'UTC')->format('D, M j, Y') }}
                                            </p>
                                        </div>
                                        <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Completed
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <p class="text-gray-500 dark:text-gray-400">You have no completed shifts in your history yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ✅ NEW: Client Limit Modal (shared with clients/index) --}}
    <div id="clientLimitModal" x-data="{ show: false }" x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.854-.833-2.624 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Client Limit Reached
                        </h3>
                        <div class="mt-2">
                            <p id="limitMessage" class="text-sm text-gray-500 dark:text-gray-400">
                                <!-- Dynamic message will be inserted here -->
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <a href="{{ route('subscription.manage') }}"
                       class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Upgrade Subscription
                    </a>
                    <button @click="show = false" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addClientBtn = document.getElementById('addClientBtn');

            // ✅ NEW: Client Limit Check Function (same as clients/index)
            function checkClientLimit() {
                fetch('{{ route("clients.checkLimit") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.allowed) {
                        // Show the modal with the limit message
                        document.getElementById('limitMessage').textContent = data.message;
                        document.querySelector('#clientLimitModal [x-data]').__x.$data.show = true;
                    } else {
                        // Redirect to create page if limit not reached
                        window.location.href = '{{ route("clients.create") }}';
                    }
                })
                .catch(error => {
                    console.error('Error checking client limit:', error);
                    // Fallback - redirect to create page if check fails
                    window.location.href = '{{ route("clients.create") }}';
                });
            }

            // ✅ NEW: Add click event listener to Add Client button for agency admins
            if (addClientBtn) {
                addClientBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    checkClientLimit();
                });
            }
        });
    </script>
</x-app-layout>