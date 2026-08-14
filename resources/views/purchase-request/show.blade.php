<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Purchase Requisition #{{ $purchaseRequest->doc_num ?? $purchaseRequest->id }}</h1>
                    @if($purchaseRequest->approval_status === 'approved')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                    @elseif($purchaseRequest->approval_status === 'rejected')
                        <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/10">Rejected</span>
                    @elseif($purchaseRequest->approval_status === 'pending')
                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending Approval</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">Draft</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Created by {{ $purchaseRequest->creator->name ?? 'User' }} on {{ $purchaseRequest->created_at ? $purchaseRequest->created_at->format('M d, Y H:i') : '-' }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('purchase-request.duplicate', $purchaseRequest->id) }}" class="rounded-md bg-indigo-50 px-3.5 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                    Duplicate Document
                </a>
                <a href="{{ route('purchase-request.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
            </div>
        </div>

        <!-- Details Card -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Header Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="text-gray-500 font-medium block">Requisition Type</span>
                    <span class="text-gray-900 font-semibold">{{ $purchaseRequest->doc_type === 'dssService' ? 'Service Requisition' : 'Item Requisition' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Urgency Level</span>
                    <span class="font-bold uppercase text-indigo-600">{{ $purchaseRequest->urgency_level ?? 'Normal' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Document Status</span>
                    <span class="text-gray-900 font-bold uppercase">{{ $purchaseRequest->status ?? 'Draft' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Posting Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->posting_date)->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Required / Delivery Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->delivery_date)->format('Y-m-d') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-medium block">Document Date</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($purchaseRequest->document_date)->format('Y-m-d') }}</span>
                </div>
                @if($purchaseRequest->comments)
                    <div class="md:col-span-3">
                        <span class="text-gray-500 font-medium block">Comments</span>
                        <span class="text-gray-800">{{ $purchaseRequest->comments }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Line Details Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Line Requisition Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Item / Account</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Quantity</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Price</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Warehouse</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">On-Hand Stock</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cost Center</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($purchaseRequest->lines as $idx => $line)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-6 pr-3 text-sm font-mono text-gray-500">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3 text-sm font-mono text-indigo-600 font-semibold">{{ $line->item_code ?? $line->account_code }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900 font-medium">{{ $line->item_description ?? $line->account_name }}</td>
                                <td class="px-3 py-3 text-sm text-right font-mono font-semibold text-gray-900">{{ number_format($line->quantity, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-right font-mono text-gray-700">{{ number_format($line->price, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 font-mono">{{ $line->whs_code ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm text-center font-mono font-bold text-indigo-700 bg-gray-50">{{ number_format($line->on_hand_qty ?? 0, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 font-mono">{{ $line->costing_code ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
