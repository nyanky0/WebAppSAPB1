<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ selectedWhsBins: null, isBinModalOpen: false }">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Warehouses & Bin Locations</h1>
                <p class="mt-1 text-sm text-gray-500">Manage warehouses and view associated bin location arrays synced from SAP.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0" x-data="{ isSyncing: false }">
                <a href="{{ route('warehouses.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Warehouse
                </a>
                <form action="{{ route('warehouses.sync') }}" method="POST" @submit="isSyncing = true">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync with SAP
                    </button>
                </form>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mt-8">
            <form method="GET" action="{{ route('warehouses.index') }}" class="flex gap-4">
                <div class="flex-1 max-w-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-md border-0 py-2 pl-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" placeholder="Search Warehouse Code or Name...">
                </div>
                <button type="submit" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Search</button>
                @if(request()->filled('search'))
                    <a href="{{ route('warehouses.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Warehouse Code</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Location</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Active</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Bin Locations</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sync Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($warehouses as $whs)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono font-semibold text-gray-900 sm:pl-6">{{ $whs->whs_code }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ $whs->whs_name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $whs->location ?: '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($whs->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Yes</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">No</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($whs->bin_enabled)
                                    <button type="button" @click="selectedWhsBins = {{ \Illuminate\Support\Js::from($whs) }}; isBinModalOpen = true" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 ring-1 ring-inset ring-indigo-600/20 hover:bg-indigo-100 transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Enabled ({{ $whs->bins->count() }} Bins)
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">Disabled</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <x-sync-status :status="$whs->sync_status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-500">No warehouses found. Click "Sync with SAP" to download.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $warehouses->links() }}
        </div>

        <!-- Bin Locations Modal -->
        <div x-show="isBinModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div x-show="isBinModalOpen" @click="isBinModalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                <div class="relative z-50 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-xl sm:w-full p-6 border border-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" x-text="selectedWhsBins ? 'Bin Locations - ' + selectedWhsBins.whs_name + ' (' + selectedWhsBins.whs_code + ')' : 'Bin Locations'"></h3>
                            <p class="text-xs text-gray-500">Array of bin locations registered under this warehouse.</p>
                        </div>
                        <button type="button" @click="isBinModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Bin Code</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template x-if="selectedWhsBins && selectedWhsBins.bins && selectedWhsBins.bins.length > 0">
                                    <template x-for="bin in selectedWhsBins.bins" :key="bin.id">
                                        <tr>
                                            <td class="px-4 py-2.5 text-xs font-mono font-medium text-gray-900" x-text="bin.bin_code"></td>
                                            <td class="px-4 py-2.5 text-xs">
                                                <span :class="bin.is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/10'" class="inline-flex items-center rounded-md px-2 py-0.5 font-medium ring-1 ring-inset" x-text="bin.is_active ? 'Active' : 'Inactive'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                                <template x-if="!selectedWhsBins || !selectedWhsBins.bins || selectedWhsBins.bins.length === 0">
                                    <tr>
                                        <td colspan="2" class="px-4 py-6 text-center text-xs text-gray-500">No bin locations found for this warehouse.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="isBinModalOpen = false" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-colors">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
