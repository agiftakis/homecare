<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 px-4 py-3 rounded-lg relative mb-6"
                    role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                {{-- START: Alpine.js Component for Live Search --}}
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="agencyFilter()">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">All Registered Agencies</h3>
                        
                        {{-- Search Input Field --}}
                        <div class="relative sm:w-1/3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" x-model.debounce.300ms="searchTerm"
                                   placeholder="Search by agency name..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Agency Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subscription</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Clients/Caregivers/Schedules</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Registered On</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- ✅ START: Alpine loop for dynamic filtering --}}
                                <template x-for="agency in filteredAgencies" :key="agency.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="agency.name"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            <span x-text="agency.owner ? agency.owner.name : 'N/A'"></span><br>
                                            <span class="text-xs text-gray-400" x-text="agency.owner ? agency.owner.email : ''"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <template x-if="agency.is_subscribed">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </template>
                                            <template x-if="!agency.is_subscribed">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            </template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            <div class="flex flex-col space-y-1">
                                                <a :href="`/superadmin/clients?agency=${agency.id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300" x-text="`${agency.clients_count} Clients`"></a>
                                                <a :href="`/superadmin/caregivers?agency=${agency.id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300" x-text="`${agency.caregivers_count} Caregivers`"></a>
                                                <a :href="`/superadmin/schedule?agency=${agency.id}`" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">View Schedules</a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300" x-text="new Date(agency.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <template x-if="!agency.is_subscribed">
                                                <button type="button" @click.prevent="$dispatch('open-modal', `confirm-agency-deletion-${agency.id}`)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                    Delete Agency
                                                </button>
                                            </template>
                                            <template x-if="agency.is_subscribed">
                                                <span class="text-gray-400 dark:text-gray-500">Active - Protected</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                                {{--  END: Alpine loop --}}

                                {{--  "No results" message for search --}}
                                <tr x-show="filteredAgencies.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No agencies found matching your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                {{--  END: Alpine.js Component --}}
            </div>
        </div>
    </div>

    @foreach ($agencies as $agency)
        @if (!$agency->subscribed('default'))
            <x-modal name="confirm-agency-deletion-{{ $agency->id }}" focusable>
                <form method="post" action="{{ route('superadmin.agencies.destroy', $agency) }}" class="p-6">
                    @csrf
                    @method('delete')
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Delete Agency: {{ $agency->name }}?
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <strong>WARNING:</strong> This will permanently delete:
                    </p>
                    <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside">
                        <li>{{ $agency->clients_count }} client(s) and all their data</li>
                        <li>{{ $agency->caregivers_count }} caregiver(s) and all their documents</li>
                        <li>All scheduled shifts for this agency</li>
                        <li>The agency record and owner account</li>
                        <li>All associated files from Firebase storage</li>
                    </ul>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">
                        This action cannot be undone.
                    </p>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <x-danger-button class="ms-3">
                            {{ __('Permanently Delete Agency') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        @endif
    @endforeach

    {{-- Alpine.js script for filtering logic --}}
    <script>
        function agencyFilter() {
            return {
                searchTerm: '',
                agencies: @json($agencies->map(function($agency) {
                    // We manually add is_subscribed because Cashier methods aren't available client-side
                    $agency->is_subscribed = $agency->subscribed('default');
                    return $agency;
                })),
                get filteredAgencies() {
                    if (this.searchTerm.trim() === '') {
                        return this.agencies;
                    }
                    const searchLower = this.searchTerm.toLowerCase();
                    return this.agencies.filter(agency => {
                        const nameLower = agency.name.toLowerCase();
                        // Match if the whole name starts with the term OR if any word in the name starts with the term
                        return nameLower.startsWith(searchLower) || 
                               nameLower.split(' ').some(word => word.startsWith(searchLower));
                    });
                }
            }
        }
    </script>
</x-app-layout>