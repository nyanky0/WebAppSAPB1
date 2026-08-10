<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">SAP Items Administration</h1>
                <p class="mt-2 text-sm text-gray-700">A local synchronized copy of SAP Business One items. Use the Sync button to update from Service Layer.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none" x-data="{ isSyncing: false }">
                <form action="{{ route('items.sync') }}" method="POST" @submit="isSyncing = true">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Sync with SAP
                    </button>
                </form>

                <!-- Syncing Loading Modal -->
                <div x-show="isSyncing" class="fixed inset-0 z-[200] overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="relative inline-block align-middle bg-white/90 backdrop-blur-xl border border-white/40 rounded-xl text-left overflow-hidden shadow-2xl transform sm:my-8 sm:max-w-md sm:w-full p-8 text-center">
                            <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Syncing Items...</h3>
                            <p class="text-sm text-gray-500">Please wait while we fetch the latest items from SAP Service Layer. This might take a few moments depending on the amount of items.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter / Search -->
        <div class="mt-8">
            <form method="GET" action="{{ route('items.index') }}" class="flex gap-4">
                <div class="flex-1 max-w-sm">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Search by Code, Name, Foreign Name or Group...">
                    </div>
                </div>
                <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Search</button>
                @if(request()->has('search'))
                    <a href="{{ route('items.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </div>

        <div class="mt-4 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    @php
                                        $cols = [
                                            'item_code' => 'Item Code',
                                            'item_name' => 'Item Name',
                                            'foreign_name' => 'Foreign Name',
                                            'uom' => 'UOM',
                                            'item_group' => 'Item Group',
                                            'is_active' => 'Active'
                                        ];
                                    @endphp
                                    @foreach($cols as $field => $label)
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'direction' => ($sort === $field && $direction === 'asc') ? 'desc' : 'asc']) }}" class="group inline-flex">
                                                {{ $label }}
                                                <span class="ml-2 flex-none rounded bg-gray-200 text-gray-900 group-hover:bg-gray-300">
                                                    @if($sort === $field)
                                                        @if($direction === 'asc')
                                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 11-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z" clip-rule="evenodd" /></svg>
                                                        @else
                                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                                        @endif
                                                    @else
                                                        <!-- Invisible placeholder to keep spacing -->
                                                        <svg class="h-5 w-5 invisible group-hover:visible text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                                    @endif
                                                </span>
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($items as $item)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $item->item_code }}</td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 sm:pl-6">{{ $item->item_name }}</td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 sm:pl-6">{{ $item->foreign_name ?: '-' }}</td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 sm:pl-6">
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">{{ $item->uom ?: 'N/A' }}</span>
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 sm:pl-6">{{ $item->item_group ?: '-' }}</td>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-700 sm:pl-6">
                                            @if($item->is_active)
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Yes</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-gray-500">
                                            No items found. Click "Sync with SAP" to download items.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>
