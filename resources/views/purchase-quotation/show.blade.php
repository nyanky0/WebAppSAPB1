<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Quotation #{{ $purchaseQuotation->doc_num ?? $purchaseQuotation->id }}</h1>
                    @if($purchaseQuotation->approval_status === 'approved')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                    @elseif($purchaseQuotation->approval_status === 'rejected')
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/10">Rejected</span>
                    @elseif($purchaseQuotation->approval_status === 'pending')
                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending Approval</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Created by {{ $purchaseQuotation->creator->name ?? 'User' }} on {{ $purchaseQuotation->created_at ? $purchaseQuotation->created_at->format('M d, Y H:i') : '-' }}</p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('purchase-quotation.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
            </div>
        </div>

        @if($purchaseQuotation->baseRequisition)
            <div class="mb-6 rounded-lg bg-indigo-50 border border-indigo-200 p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-900">Copied Base Purchase Requisition</h4>
                        <a href="{{ route('purchase-request.show', $purchaseQuotation->baseRequisition->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-white px-3 py-1 rounded border border-indigo-300 hover:bg-indigo-600 hover:text-white transition-colors mt-1">
                            Purchase Requisition #{{ $purchaseQuotation->baseRequisition->doc_num ?? $purchaseQuotation->baseRequisition->id }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Header Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="text-gray-500 font-medium block">Vendor (Supplier)</span>
                    <span class="text-gray-900 font-semibold">{{ $purchaseQuotation->card_name }} ({{ $purchaseQuotation->card_code }})</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Urgency Level</span>
                    <span class="font-bold uppercase text-indigo-600">{{ $purchaseQuotation->urgency_level ?? 'Normal' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Document Status</span>
                    <span class="text-gray-900 font-bold uppercase">{{ $purchaseQuotation->status ?? 'Draft' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Document Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseQuotation->document_date)->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Due Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseQuotation->due_date)->format('Y-m-d') }}</span>
                </div>
                @if($purchaseQuotation->comments)
                    <div class="md:col-span-3">
                        <span class="text-gray-500 font-medium block">Comments</span>
                        <span class="text-gray-800">{{ $purchaseQuotation->comments }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Quotation Line Item Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Item Code</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Req. Qty</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Req. Date</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Quoted Qty</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Quoted Date</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Unit Price</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Warehouse</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($purchaseQuotation->lines as $idx => $line)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-6 pr-3 text-sm font-mono text-gray-500">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3 text-sm font-mono text-indigo-600 font-semibold">{{ $line->item_code }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 font-medium">{{ $line->item_description }}</td>
                                <td class="px-3 py-3 text-sm text-right font-mono text-gray-500 bg-gray-50">{{ number_format($line->required_qty, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-center text-gray-500 bg-gray-50">{{ $line->required_date ? \Carbon\Carbon::parse($line->required_date)->format('Y-m-d') : '-' }}</td>
                                <td class="px-3 py-3 text-sm text-right font-mono font-bold text-gray-900">{{ number_format($line->quoted_qty, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-center font-semibold text-gray-900">{{ $line->quoted_date ? \Carbon\Carbon::parse($line->quoted_date)->format('Y-m-d') : '-' }}</td>
                                <td class="px-3 py-3 text-sm text-right font-mono text-gray-900">{{ number_format($line->unit_price, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 font-mono">{{ $line->whs_code ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
