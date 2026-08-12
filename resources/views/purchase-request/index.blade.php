<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Purchase Requests</h1>
                <p class="mt-1 text-sm text-gray-500">A list of all purchase requests and their synchronization queue status.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('purchase-request.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    Create Purchase Request
                </a>
            </div>
        </div>

        <!-- Filter Checklist -->
        <div class="mt-8 bg-white p-4 shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            <form method="GET" action="{{ route('purchase-request.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-4">
                <span class="text-sm font-semibold text-gray-900">Filter by Sync Status:</span>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="sync_statuses[]" value="Draft" id="filter_draft" {{ in_array('Draft', $filters) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="filter_draft" class="ml-2 block text-sm text-gray-900">Draft</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="sync_statuses[]" value="Synced" id="filter_synced" {{ in_array('Synced', $filters) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="filter_synced" class="ml-2 block text-sm text-gray-900">Synced</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="sync_statuses[]" value="Failed" id="filter_failed" {{ in_array('Failed', $filters) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="filter_failed" class="ml-2 block text-sm text-gray-900">Failed</label>
                    </div>
                </div>
                <div class="sm:ml-auto">
                    <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-4 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ID</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Doc Type</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sync Status</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">SAP Status</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Posting Date</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vendor</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Requester</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($requests as $pr)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $pr->id }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if(($pr->doc_type ?? 'dssItem') === 'dssService')
                                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-700 ring-1 ring-inset ring-purple-600/20">Service</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">Item</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($pr->sync_status === 'Synced')
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Synced</span>
                                            @elseif($pr->sync_status === 'Failed')
                                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Failed</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($pr->sap_status)
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">{{ $pr->sap_status }}</span>
                                            @else
                                                <span class="text-gray-400 italic">Pending</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($pr->posting_date)->format('Y-m-d') }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $pr->vendor }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $pr->requester }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 text-center text-sm text-gray-500">No purchase requests match the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
