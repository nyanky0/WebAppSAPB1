<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Scheduler - Document Sync</h1>
                <p class="mt-1 text-sm text-gray-500">Monitor and manage the synchronization of local Transaction Documents to SAP.</p>
            </div>
            <div class="shrink-0" x-data="{ isSyncing: false }">
                <form action="{{ route('scheduler.sync-all-documents') }}" method="POST" @submit="if(confirm('Are you sure you want to sync all pending documents to SAP?')) { isSyncing = true; return true; } else { return false; }">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-x-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync All Documents
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
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Syncing All Documents...</h3>
                            <p class="text-sm text-gray-500">Please wait while we push all pending documents to SAP Service Layer.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 mb-6">
            <form method="GET" action="{{ route('scheduler.document') }}" class="flex flex-1 flex-wrap gap-4 items-end bg-white p-4 rounded-lg shadow-sm ring-1 ring-inset ring-gray-300">
                
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

        @if($purchaseOrders->isEmpty())
            <div class="rounded-lg bg-white p-12 text-center shadow ring-1 ring-black ring-opacity-5">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No documents found</h3>
                <p class="mt-1 text-sm text-gray-500">There are no Purchase Orders matching your selected status filters.</p>
            </div>
        @else
            <div class="space-y-8">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Purchase Orders ({{ $purchaseOrders->count() }})</h2>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">PO # / SAP #</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vendor</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Posting Date</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Items</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sync Status</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($purchaseOrders as $row)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900 font-mono">
                                        #{{ $row->doc_num ?? $row->id }} <span class="text-gray-400">/</span> {{ $row->sap_number ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">{{ $row->card_name }} ({{ $row->card_code }})</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($row->posting_date)->format('Y-m-d') }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">{{ $row->lines->count() }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <x-sync-status :status="$row->sync_status" :error="$row->sync_error" />
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        @if($row->sync_status !== 'Synced')
                                        <form action="{{ route('scheduler.sync-now') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="PurchaseOrder">
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
            </div>
        @endif
    </div>
</x-app-layout>
