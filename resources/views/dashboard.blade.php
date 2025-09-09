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
                    {{-- This message is now dynamic based on user role --}}
                    @if (Auth::user()->role === 'caregiver' && Auth::user()->caregiver)
                        <h3 class="text-2xl font-bold">Welcome, {{ Auth::user()->caregiver->first_name }}!</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Here are your scheduled shifts.
                        </p>
                    @else
                        <h3 class="text-2xl font-bold">Welcome, {{ $agency->name ?? 'Admin' }}!</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Here's a snapshot of your agency's activity
                            today.
                        </p>
                    @endif
                </div>
            </div>


            {{-- ============================================= --}}
            {{-- ======== AGENCY ADMIN / SUPER ADMIN VIEW ======== --}}
            {{-- This entire section is hidden from caregivers --}}
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
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $todaysShifts->count() }}
                        </p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('clients.create') }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            + Add New Client
                        </a>
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
            {{-- This entire section is new and only for caregivers --}}
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
                                            {{-- This link takes the caregiver to the EVV page --}}
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
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Completed Shift
                            History</h3>
                        <div class="space-y-4">
                            @forelse ($all_past_shifts as $shift)
                                <div
                                    class="bg-white dark:bg-gray-900/80 p-4 rounded-lg border dark:border-gray-700 opacity-80">
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
                                    <p class="text-gray-500 dark:text-gray-400">You have no completed shifts in your
                                        history yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>