<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Request #{{ $purchaseRequest->doc_num ?? $purchaseRequest->id }}</h1>
                    @if($purchaseRequest->sync_status === 'Synced')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Synced to SAP</span>
                    @elseif($purchaseRequest->sync_status === 'Failed')
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/10">Sync Failed</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Created by {{ $purchaseRequest->creator->name ?? ($purchaseRequest->requester ?? 'User') }} on {{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('M d, Y H:i') : '-' }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('purchase-request.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
                
                @if($purchaseRequest->sync_status === 'Synced' && $purchaseRequest->doc_entry)
                    <div class="relative inline-block text-left" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none">
                            Copy To
                            <svg class="-mr-1 ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;">
                            <div class="py-1">
                                <a href="{{ route('purchase-order.create', ['from_pr' => $purchaseRequest->id]) }}" class="block px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                                    <div class="flex items-center justify-between">
                                        <span>Purchase Order</span>
                                        <span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded">Target Doc</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <button type="button" disabled title="Only Synced documents can be copied" class="rounded-md bg-gray-300 px-4 py-2 text-sm font-medium text-gray-500 cursor-not-allowed">Copy To (Requires Sync)</button>
                @endif
            </div>
        </div>

        <!-- Target Connected Document Banner -->
        @if($targetPos->count() > 0)
            <div class="mb-6 rounded-lg bg-indigo-50 border border-indigo-200 p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-900">Connected Target Document</h4>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach($targetPos as $targetPo)
                                <a href="{{ route('purchase-order.show', $targetPo->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-white px-3 py-1 rounded-md border border-indigo-300 hover:bg-indigo-600 hover:text-white transition-colors">
                                    <span>Purchase Order #{{ $targetPo->doc_num ?? $targetPo->id }} (DocEntry: {{ $targetPo->doc_entry ?? '-' }})</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Document Details Card -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Document Header</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="text-gray-500 font-medium block">Document Type</span>
                    <span class="text-gray-900 font-semibold">{{ $purchaseRequest->doc_type === 'dssService' ? 'Service Document' : 'Item Document' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">SAP DocNum / DocEntry</span>
                    <span class="text-gray-900 font-semibold font-mono">{{ $purchaseRequest->doc_num ?? '-' }} / {{ $purchaseRequest->doc_entry ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Vendor</span>
                    <span class="text-gray-900 font-semibold">{{ $purchaseRequest->vendor }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Posting Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->posting_date)->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Required Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->required_date)->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Valid Until</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->valid_until)->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>

        <!-- Line Details Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Document Line Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase"># Line</th>
                            @if($purchaseRequest->doc_type === 'dssService')
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">G/L Account</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Account Name</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total Amount</th>
                            @else
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Item Code</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Quantity</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Unit Price</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">UOM</th>
                            @endif
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Tax</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Dimension / Cost Center</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($purchaseRequest->lines as $idx => $line)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-6 pr-3 text-sm font-mono text-gray-500">{{ $line->line_num ?? $idx }}</td>
                                @if($purchaseRequest->doc_type === 'dssService')
                                    <td class="px-3 py-3 text-sm font-mono text-indigo-600 font-semibold">{{ $line->account_code }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-900 font-medium">{{ $line->account_name }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-700">{{ $line->item_description }}</td>
                                    <td class="px-3 py-3 text-sm text-right font-mono font-semibold text-gray-900">{{ number_format($line->price, 2) }}</td>
                                @else
                                    <td class="px-3 py-3 text-sm font-mono text-indigo-600 font-semibold">{{ $line->item_code }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-900 font-medium">{{ $line->item_description }}</td>
                                    <td class="px-3 py-3 text-sm text-right font-mono font-semibold text-gray-900">{{ number_format($line->quantity, 2) }}</td>
                                    <td class="px-3 py-3 text-sm text-right font-mono text-gray-700">{{ number_format($line->price, 2) }}</td>
                                    <td class="px-3 py-3 text-sm text-center font-mono text-gray-600">{{ $line->uom_code ?? '-' }}</td>
                                @endif
                                <td class="px-3 py-3 text-sm text-center font-mono text-gray-600">{{ $line->tax_code ?? $purchaseRequest->tax_code }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 font-mono">{{ $line->costing_code ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
