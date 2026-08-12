<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Scheduler - Master Data Sync</h1>
                <p class="mt-1 text-sm text-gray-500">Monitor and manage the synchronization of local Master Data to SAP.</p>
            </div>
            <div class="shrink-0">
                <form action="{{ route('scheduler.sync-all-master-data') }}" method="POST" onsubmit="return confirm('Are you sure you want to sync all pending master data items to SAP?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-x-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync All Master Data
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 mb-6">
            <form method="GET" action="{{ route('scheduler.master-data') }}" class="flex flex-1 flex-wrap gap-4 items-end bg-white p-4 rounded-lg shadow-sm ring-1 ring-inset ring-gray-300">
                
                <div>
                    <span class="block text-sm font-medium leading-6 text-gray-900 mb-2">Sync Status</span>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="statuses[]" value="Draft" {{ in_array('Draft', $statuses) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <span class="text-sm text-gray-700">Draft</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="statuses[]" value="Failed" {{ in_array('Failed', $statuses) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-600">
                            <span class="text-sm text-gray-700">Failed</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="statuses[]" value="Synced" {{ in_array('Synced', $statuses) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 focus:ring-green-600">
                            <span class="text-sm text-gray-700">Synced</span>
                        </label>
                    </div>
                </div>

                <div class="ml-auto">
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Filter Data</button>
                </div>
            </form>
        </div>

        @php
            $hasData = $items->isNotEmpty() || $itemGroups->isNotEmpty() || $taxes->isNotEmpty() || $businessPartners->isNotEmpty();
        @endphp

        @if(!$hasData)
            <div class="rounded-lg bg-white p-12 text-center shadow ring-1 ring-black ring-opacity-5">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No data found</h3>
                <p class="mt-1 text-sm text-gray-500">There are no records matching your selected status filters.</p>
            </div>
        @else
            
            <div class="space-y-8">
                
                @if($businessPartners->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Business Partners ({{ $businessPartners->count() }})</h2>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">BP Code</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($businessPartners as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ $row->bp_code }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->type }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <x-sync-status :status="$row->sync_status" :error="$row->sync_error" />
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        @if($row->sync_status !== 'Synced')
                                        <form action="{{ route('scheduler.sync-now') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="BusinessPartner">
                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Sync Now</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($items->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Items ({{ $items->count() }})</h2>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Item Code</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Group</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($items as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ $row->item_code }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->item_name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->item_group }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <x-sync-status :status="$row->sync_status" :error="$row->sync_error" />
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        @if($row->sync_status !== 'Synced')
                                        <form action="{{ route('scheduler.sync-now') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="Item">
                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Sync Now</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($itemGroups->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Item Groups ({{ $itemGroups->count() }})</h2>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($itemGroups as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ $row->group_name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <x-sync-status :status="$row->sync_status" :error="$row->sync_error" />
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        @if($row->sync_status !== 'Synced')
                                        <form action="{{ route('scheduler.sync-now') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="ItemGroup">
                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Sync Now</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                @if($taxes->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Taxes ({{ $taxes->count() }})</h2>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Code</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Rate (%)</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($taxes as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ $row->code }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row->rate }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <x-sync-status :status="$row->sync_status" :error="$row->sync_error" />
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        @if($row->sync_status !== 'Synced')
                                        <form action="{{ route('scheduler.sync-now') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="Tax">
                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Sync Now</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
            </div>
            
        @endif
    </div>
</x-app-layout>
