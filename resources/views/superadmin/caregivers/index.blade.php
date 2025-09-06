<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $pageTitle ?? 'All Caregivers' }}
            </h2>
            <div class="flex items-center space-x-3">
                @if(request()->has('agency'))
                    <a href="{{ route('superadmin.caregivers.index') }}" 
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        &larr; View All Agencies
                    </a>
                @endif
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(request()->has('agency'))
                @php
                    $agency = \App\Models\Agency::find(request()->agency);
                @endphp
                @if($agency)
                    <div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-200">
                                    Showing caregivers for <strong>{{ $agency->name }}</strong> only. 
                                    <a href="{{ route('superadmin.caregivers.index') }}" class="underline hover:text-blue-800 dark:hover:text-blue-100">View all caregivers</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <!-- Mobile View -->
                    <div class="space-y-4 md:hidden">
                        @forelse ($caregivers as $caregiver)
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if ($caregiver->profile_picture_url)
                                                <img class="h-12 w-12 rounded-full object-cover" src="{{ $caregiver->profile_picture_url }}" alt="Caregiver profile picture">
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $caregiver->first_name }} {{ $caregiver->last_name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $caregiver->agency->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Email:</span> {{ $caregiver->email }}</p>
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Phone:</span> {{ $caregiver->phone_number }}</p>
                                </div>
                                <div class="mt-4 text-right">
                                     <a href="{{ route('superadmin.caregivers.show', $caregiver) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        View/Edit Profile
                                     </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                                @if(request()->has('agency'))
                                    No caregivers found for this agency.
                                @else
                                    No caregivers found across all agencies.
                                @endif
                            </p>
                        @endforelse
                    </div>

                    <!-- Desktop View -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    @if(!request()->has('agency'))
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agency</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($caregivers as $caregiver)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if ($caregiver->profile_picture_url)
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $caregiver->profile_picture_url }}" alt="Caregiver profile picture">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $caregiver->first_name }} {{ $caregiver->last_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @if(!request()->has('agency'))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $caregiver->agency->name ?? 'N/A' }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $caregiver->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $caregiver->phone_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('superadmin.caregivers.show', $caregiver) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">View/Edit Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ request()->has('agency') ? '4' : '5' }}" class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                            @if(request()->has('agency'))
                                                No caregivers found for this agency.
                                            @else
                                                No caregivers found across all agencies.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>