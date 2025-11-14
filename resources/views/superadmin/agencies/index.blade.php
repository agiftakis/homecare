<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Agency Management
            </h2>
            <a href="{{ route('superadmin.agencies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                Create New Agency
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Agency Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Contact Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Users
                                    </th>
                                    {{-- ✅ NEW: Date Created Column --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date Created
                                    </th>
                                    {{-- ✅ NEW: Suspend Account Column --}}
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Suspend Account
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($agencies as $agency)
                                    {{-- ✅ NEW: Add visual indicator for suspended agencies --}}
                                    <tr class="{{ $agency->suspended ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $agency->name }}
                                            {{-- ✅ NEW: Show suspended badge --}}
                                            @if($agency->suspended)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    SUSPENDED
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $agency->contact_email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($agency->is_lifetime_free)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    Lifetime Free
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $agency->subscription_status === 'active' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100' }}">
                                                    {{ ucfirst($agency->subscription_status ?? 'N/A') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $agency->users_count }}
                                        </td>
                                        {{-- ✅ NEW: Date Created Cell --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $agency->created_at->format('M j, Y') }}
                                        </td>
                                        {{-- ✅ NEW: Suspend Account Checkbox Cell --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                    class="suspension-toggle w-5 h-5 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 dark:focus:ring-red-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 cursor-pointer"
                                                    data-agency-id="{{ $agency->id }}"
                                                    {{ $agency->suspended ? 'checked' : '' }}>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('superadmin.agencies.edit', $agency) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- ✅ UPDATED: Changed colspan from 5 to 7 to account for new columns --}}
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                            No agencies found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $agencies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ NEW: Add required scripts for suspension toggle functionality --}}
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    
    <script>
        $(document).ready(function() {
            toastr.options = {
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            $('.suspension-toggle').on('change', function() {
                const checkbox = $(this);
                const agencyId = checkbox.data('agency-id');
                const isChecked = checkbox.is(':checked');

                // Show confirmation dialog
                const action = isChecked ? 'suspend' : 'activate';
                const message = isChecked 
                    ? 'Are you sure you want to suspend this agency? Users will not be able to access their account.'
                    : 'Are you sure you want to activate this agency?';
                
                if (!confirm(message)) {
                    // Revert checkbox if user cancels
                    checkbox.prop('checked', !isChecked);
                    return;
                }

                // Send AJAX request
                $.ajax({
                    url: `/superadmin/agencies/${agencyId}/toggle-suspension`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            // Reload page to update UI
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            toastr.error(response.message);
                            checkbox.prop('checked', !isChecked);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to update agency status. Please try again.');
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>