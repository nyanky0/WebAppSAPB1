<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ showEditModal: false }">
        <!-- Dashboard Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Welcome back, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>! Here is your personalized business overview.</p>
            </div>
            
            <!-- Customize Dashboard Button -->
            <div class="shrink-0">
                <button type="button" @click="showEditModal = true" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-indigo-600 transition-all">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Edit Dashboard Widgets
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800 flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Widget Grid Container -->
        <div class="space-y-6">

            <!-- 1. Pending Approvals For Logged-in User Widget -->
            @if(!empty($userWidgets['pending_approvals']))
                <div class="bg-white shadow rounded-xl border border-amber-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="text-base font-bold">Pending Approvals For Me</h3>
                                <p class="text-xs opacity-90">Documents requiring your vote or approval decision</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center justify-center bg-white text-orange-600 font-extrabold text-sm px-3 py-1 rounded-full shadow-sm">{{ $pendingApprovalsCount }}</span>
                    </div>

                    <div class="p-6">
                        @if($pendingApprovalsList->count() > 0)
                            <div class="divide-y divide-gray-200">
                                @foreach($pendingApprovalsList as $req)
                                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-amber-50/50 p-2 rounded-lg transition-colors">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-900 font-mono">{{ $req->document_type }}</span>
                                                <span class="text-xs text-indigo-600 font-semibold font-mono">#{{ $req->doc_instance->doc_num ?? $req->document_id }}</span>
                                                <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Stage: {{ $req->currentStage->name ?? 'Current' }}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Originator: <span class="font-medium text-gray-700">{{ $req->originator->name ?? 'User' }}</span> • Created {{ $req->created_at->diffForHumans() }}</p>
                                        </div>
                                        <a href="{{ route('approvals.decisions.show', $req->id) }}" class="inline-flex items-center text-xs font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 px-3 py-1.5 rounded-md transition-colors">
                                            Review & Vote →
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6 text-sm text-gray-500">
                                ✨ You have no pending approval decisions.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- 2. High Urgency Requisitions Widget -->
            @if(!empty($userWidgets['high_urgency_pr']))
                <div class="bg-white shadow rounded-xl border border-red-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-rose-500 flex items-center justify-between text-white">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h3 class="text-base font-bold">High Urgency Purchase Requisitions</h3>
                                <p class="text-xs opacity-90">Requisitions flagged with HIGH priority urgency</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center justify-center bg-white text-red-600 font-extrabold text-sm px-3 py-1 rounded-full shadow-sm">{{ $highUrgencyPRsCount }}</span>
                    </div>

                    <div class="p-6">
                        @if($highUrgencyPRsList->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                            <th class="py-2">PR #</th>
                                            <th class="py-2">Doc Date</th>
                                            <th class="py-2">Delivery Date</th>
                                            <th class="py-2 text-center">Status</th>
                                            <th class="py-2 text-center">Approval</th>
                                            <th class="py-2 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($highUrgencyPRsList as $pr)
                                            <tr class="hover:bg-red-50/40">
                                                <td class="py-3 font-mono font-bold text-indigo-600">#{{ $pr->doc_num ?? $pr->id }}</td>
                                                <td class="py-3 text-gray-600">{{ \Carbon\Carbon::parse($pr->document_date)->format('Y-m-d') }}</td>
                                                <td class="py-3 text-gray-600">{{ \Carbon\Carbon::parse($pr->delivery_date)->format('Y-m-d') }}</td>
                                                <td class="py-3 text-center">
                                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800 uppercase">{{ $pr->status ?? 'Draft' }}</span>
                                                </td>
                                                <td class="py-3 text-center">
                                                    @if($pr->approval_status === 'approved')
                                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700">Approved</span>
                                                    @elseif($pr->approval_status === 'pending')
                                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Draft</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-right">
                                                    <a href="{{ route('purchase-request.show', $pr->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">View →</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-6 text-sm text-gray-500">
                                No high urgency requisitions at the moment.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Grid for Document Summaries -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- 3. Purchase Requisitions Summary Widget -->
                @if(!empty($userWidgets['pr_summary']))
                    <div class="bg-white shadow rounded-xl border border-gray-200 p-5 flex flex-col justify-between hover:shadow-lg transition-shadow">
                        <div>
                            <div class="flex items-center justify-between border-b pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-bold text-gray-900">Purchase Requisitions</h3>
                                </div>
                                <a href="{{ route('purchase-request.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">View All</a>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center mb-4">
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <span class="block text-xs text-gray-500 font-medium">Total</span>
                                    <span class="text-lg font-extrabold text-gray-900">{{ $prTotal }}</span>
                                </div>
                                <div class="bg-green-50 p-2 rounded-lg">
                                    <span class="block text-xs text-green-700 font-medium">Open</span>
                                    <span class="text-lg font-extrabold text-green-800">{{ $prOpen }}</span>
                                </div>
                                <div class="bg-amber-50 p-2 rounded-lg">
                                    <span class="block text-xs text-amber-700 font-medium">Draft</span>
                                    <span class="text-lg font-extrabold text-amber-800">{{ $prDraft }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('purchase-request.create') }}" class="w-full inline-flex justify-center items-center py-2 px-3 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            + Create Requisition
                        </a>
                    </div>
                @endif

                <!-- 4. Purchase Quotations Summary Widget -->
                @if(!empty($userWidgets['pq_summary']))
                    <div class="bg-white shadow rounded-xl border border-gray-200 p-5 flex flex-col justify-between hover:shadow-lg transition-shadow">
                        <div>
                            <div class="flex items-center justify-between border-b pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-bold text-gray-900">Purchase Quotations</h3>
                                </div>
                                <a href="{{ route('purchase-quotation.index') }}" class="text-xs font-semibold text-purple-600 hover:underline">View All</a>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center mb-4">
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <span class="block text-xs text-gray-500 font-medium">Total</span>
                                    <span class="text-lg font-extrabold text-gray-900">{{ $pqTotal }}</span>
                                </div>
                                <div class="bg-green-50 p-2 rounded-lg">
                                    <span class="block text-xs text-green-700 font-medium">Open</span>
                                    <span class="text-lg font-extrabold text-green-800">{{ $pqOpen }}</span>
                                </div>
                                <div class="bg-amber-50 p-2 rounded-lg">
                                    <span class="block text-xs text-amber-700 font-medium">Draft</span>
                                    <span class="text-lg font-extrabold text-amber-800">{{ $pqDraft }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('purchase-quotation.create') }}" class="w-full inline-flex justify-center items-center py-2 px-3 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                            + Create Quotation
                        </a>
                    </div>
                @endif

                <!-- 5. Purchase Orders Summary Widget -->
                @if(!empty($userWidgets['po_summary']))
                    <div class="bg-white shadow rounded-xl border border-gray-200 p-5 flex flex-col justify-between hover:shadow-lg transition-shadow">
                        <div>
                            <div class="flex items-center justify-between border-b pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                    </span>
                                    <h3 class="text-base font-bold text-gray-900">Purchase Orders</h3>
                                </div>
                                <a href="{{ route('purchase-order.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">View All</a>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center mb-4">
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <span class="block text-xs text-gray-500 font-medium">Total</span>
                                    <span class="text-lg font-extrabold text-gray-900">{{ $poTotal }}</span>
                                </div>
                                <div class="bg-green-50 p-2 rounded-lg">
                                    <span class="block text-xs text-green-700 font-medium">Open</span>
                                    <span class="text-lg font-extrabold text-green-800">{{ $poOpen }}</span>
                                </div>
                                <div class="bg-blue-50 p-2 rounded-lg">
                                    <span class="block text-xs text-blue-700 font-medium">SAP Synced</span>
                                    <span class="text-lg font-extrabold text-blue-800">{{ $poSynced }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('purchase-order.create') }}" class="w-full inline-flex justify-center items-center py-2 px-3 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            + Create Purchase Order
                        </a>
                    </div>
                @endif

            </div>
        </div>

        <!-- Customize Dashboard Modal -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md sm:w-full p-6 z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Customize Your Dashboard</h3>
                    <p class="text-xs text-gray-500 mb-4">Check or uncheck widgets to show on your personalized dashboard layout.</p>

                    <form method="POST" action="{{ route('dashboard.widgets') }}">
                        @csrf
                        <div class="space-y-3 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Pending Approvals For Me</span>
                                <input type="checkbox" name="pending_approvals" value="1" {{ !empty($userWidgets['pending_approvals']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>High Urgency Purchase Requisitions</span>
                                <input type="checkbox" name="high_urgency_pr" value="1" {{ !empty($userWidgets['high_urgency_pr']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Requisitions Summary</span>
                                <input type="checkbox" name="pr_summary" value="1" {{ !empty($userWidgets['pr_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Quotations Summary</span>
                                <input type="checkbox" name="pq_summary" value="1" {{ !empty($userWidgets['pq_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Orders Summary</span>
                                <input type="checkbox" name="po_summary" value="1" {{ !empty($userWidgets['po_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showEditModal = false" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save Layout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
