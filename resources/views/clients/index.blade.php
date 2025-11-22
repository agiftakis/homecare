<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4 sm:mb-0">
                {{ __('Clients') }}
            </h2>

            {{-- ✅ CLEANUP: Simplified "Add New Client" button. Removed all subscription logic. --}}
            <a href="{{ route('clients.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                + Add New Client
            </a>
        </div>
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

            {{-- ✅ CLEANUP: Removed 'error' message block for client limit --}}

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="clientSearch" placeholder="Search clients by name or agency..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div id="mobileView" class="space-y-4 md:hidden">
                        @forelse ($clients as $client)
                            <div class="client-card bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border dark:border-gray-700"
                                {{-- ✅ SUPER ADMIN UPDATE: Add agency name to the searchable data attribute --}}
                                data-name="{{ strtolower($client->first_name . ' ' . $client->last_name . ' ' . ($client->agency->name ?? '')) }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if ($client->profile_picture_url)
                                                <img class="h-12 w-12 rounded-full object-cover"
                                                    src="{{ $client->profile_picture_url }}"
                                                    alt="Client profile picture">
                                            @else
                                                <div
                                                    class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-8 w-8 text-gray-400" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-base font-bold text-gray-900 dark:text-gray-100">
                                                {{ $client->first_name }} {{ $client->last_name }}</div>
                                            {{-- ✅ SUPER ADMIN UPDATE: Conditionally display the agency name for super admins --}}
                                            @if (Auth::user()->role === 'super_admin' && $client->agency)
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                                                    {{ $client->agency->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Email:</span>
                                        {{ $client->email }}</p>
                                    <p><span class="font-semibold text-gray-600 dark:text-gray-400">Phone:</span>
                                        {{ $client->phone_number }}</p>
                                </div>

                                <div class="mt-4 flex justify-between items-center">
                                    @if ($client->user && $client->user->email_verified_at)
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active Client
                                        </span>
                                    @elseif($client->user && $client->user->password_setup_token)
                                        <form action="{{ route('clients.resendOnboarding', $client) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                Get Client Link
                                            </button>
                                        </form>
                                    @elseif($client->user && $client->user->password_setup_expires_at && now()->gt($client->user->password_setup_expires_at))
                                        <form action="{{ route('clients.resendOnboarding', $client) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-400 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                Link Expired - Regenerate
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active Client
                                        </span>
                                    @endif
                                    <a href="{{ route('clients.edit', $client) }}"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">No clients have been added yet.
                            </p>
                        @endforelse
                    </div>

                    <div id="desktopView" class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Name</th>
                                    {{-- ✅ SUPER ADMIN UPDATE: Conditionally add the Agency column header --}}
                                    @if (Auth::user()->role === 'super_admin')
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Agency</th>
                                    @endif
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Email</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Phone</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($clients as $client)
                                    <tr class="client-row" {{-- ✅ SUPER ADMIN UPDATE: Add agency name to the searchable data attribute --}}
                                        data-name="{{ strtolower($client->first_name . ' ' . $client->last_name . ' ' . ($client->agency->name ?? '')) }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if ($client->profile_picture_url)
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ $client->profile_picture_url }}"
                                                            alt="Client profile picture">
                                                    @else
                                                        <div
                                                            class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-gray-400" fill="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path
                                                                    d="M24 20.993V24H0v-2.997A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $client->first_name }} {{ $client->last_name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        {{-- ✅ SUPER ADMIN UPDATE: Conditionally add the Agency data cell --}}
                                        @if (Auth::user()->role === 'super_admin')
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $client->agency->name ?? 'N/A' }}</div>
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $client->email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $client->phone_number }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            @if ($client->user && $client->user->email_verified_at)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Active
                                                </span>
                                            @elseif($client->user && $client->user->password_setup_token)
                                                <form action="{{ route('clients.resendOnboarding', $client) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="bg-yellow-500 hover:bg-yellow-400 text-black font-bold py-1 px-3 rounded text-xs">
                                                        Get Client Onboarding Link
                                                    </button>
                                                </form>
                                            @elseif($client->user && $client->user->password_setup_expires_at && now()->gt($client->user->password_setup_expires_at))
                                                <form action="{{ route('clients.resendOnboarding', $client) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="bg-red-500 hover:bg-red-400 text-white font-bold py-1 px-3 rounded text-xs">
                                                        Link Expired - Regenerate
                                                    </button>
                                                </form>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Active
                                                </span>
                                            @endif
                                            <a href="{{ route('clients.edit', $client) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit/View
                                                Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->role === 'super_admin' ? '5' : '4' }}"
                                            class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No clients have been added yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div id="noResults" class="hidden text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No clients found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search terms.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ CLEANUP: Removed Client Limit Modal --}}

    @if (session('setup_link'))
        <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;" x-cloak>
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
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">

                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Client Onboarding Link Generated
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <strong class="text-red-600 dark:text-red-400">IMPORTANT SECURITY
                                        NOTICE:</strong><br>
                                    Please email this secure setup link directly to the client. <strong>This link
                                        expires in 48 hours</strong> and should NOT be shared with anyone else for
                                    security reasons.
                                </p>

                                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                    <label
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Setup
                                        Link:</label>
                                    <input type="text" value="{{ session('setup_link') }}" readonly
                                        class="w-full text-sm bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded px-2 py-1 text-gray-900 dark:text-gray-100"
                                        onclick="this.select(); document.execCommand('copy');">
                                </div>

                                <div
                                    class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-md border border-blue-200 dark:border-blue-800">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Instructions:</strong><br>
                                        • Click the link above to copy it to your clipboard<br>
                                        • Email the link directly to the client securely<br>
                                        • <strong>If the link expires in 48 hours</strong>, click the red <strong>"Link
                                            Expired - Regenerate"</strong> button to create a new 48-hour link<br>
                                        • Once the client successfully sets up their password, the button will
                                        automatically disappear and their status will change to Active
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button @click="show = false" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Got it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('clientSearch');
            const clientCards = document.querySelectorAll('.client-card');
            const clientRows = document.querySelectorAll('.client-row');
            const noResults = document.getElementById('noResults');
            const mobileView = document.getElementById('mobileView');
            const desktopView = document.getElementById('desktopView');

            {{-- ✅ CLEANUP: Removed addClientBtn variable and all related 'checkClientLimit' logic --}}

            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                // Filter mobile cards
                clientCards.forEach(card => {
                    const searchableData = card.getAttribute('data-name');
                    if (searchableData.includes(searchTerm)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Filter desktop rows
                clientRows.forEach(row => {
                    const searchableData = row.getAttribute('data-name');
                    if (searchableData.includes(searchTerm)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Adjust for desktop/mobile visible count discrepancy
                const totalVisible = document.querySelectorAll('.client-card[style*="display: block"]').length ||
                    document.querySelectorAll('.client-row[style*="display: table-row"]').length;

                // Show/hide no results message
                if (totalVisible === 0 && searchTerm !== '') {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            {{-- ✅ CLEANUP: Removed 'checkClientLimit' function --}}

            {{-- ✅ CLEANUP: Removed 'addClientBtn' event listener --}}

            searchInput.addEventListener('input', performSearch);
        });
    </script>
</x-app-layout>
