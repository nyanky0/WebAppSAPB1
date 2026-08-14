<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="{ showEditModal: false }">
        <!-- Dashboard Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">SAP B1 Cockpit Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Welcome back, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>! Modular box-by-box widget layout.</p>
            </div>
            
            <!-- Customize Dashboard Button -->
            <div class="shrink-0">
                <button type="button" @click="showEditModal = true" class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-indigo-600 transition-all">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Customize Cockpit Widgets
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800 flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Responsive SAP HANA Box-by-Box Grid Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Box 1: Pending Approvals For Logged-in User -->
            @if(!empty($userWidgets['pending_approvals']))
                <div class="bg-white shadow-md rounded-xl border border-amber-200 overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all">
                    <div>
                        <div class="px-5 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 flex items-center justify-between text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-bold tracking-wide">Pending Approvals</h3>
                            </div>
                            <span class="inline-flex items-center justify-center bg-white text-orange-600 font-extrabold text-xs px-2.5 py-0.5 rounded-full shadow-sm">{{ $pendingApprovalsCount }}</span>
                        </div>

                        <div class="p-4">
                            @if($pendingApprovalsList->count() > 0)
                                <div class="divide-y divide-gray-100">
                                    @foreach($pendingApprovalsList->take(3) as $req)
                                        <div class="py-2.5 flex items-center justify-between gap-2 hover:bg-amber-50/50 p-1.5 rounded transition-colors">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-xs text-gray-900 truncate font-mono">{{ $req->document_type }}</span>
                                                    <span class="text-xs text-indigo-600 font-semibold font-mono">#{{ $req->doc_instance->doc_num ?? $req->document_id }}</span>
                                                </div>
                                                <p class="text-[11px] text-gray-500 truncate">From: {{ $req->originator->name ?? 'User' }}</p>
                                            </div>
                                            <a href="{{ route('approvals.decisions.show', $req->id) }}" class="shrink-0 text-[11px] font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 px-2 py-1 rounded transition-colors">
                                                Vote →
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-xs text-gray-400">
                                    ✨ No pending approvals
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border-t border-gray-100 text-right">
                        <a href="{{ route('approvals.decisions.index') }}" class="text-xs font-semibold text-amber-700 hover:underline">View All Decisions →</a>
                    </div>
                </div>
            @endif

            <!-- Box 2: High Urgency Purchase Requisitions -->
            @if(!empty($userWidgets['high_urgency_pr']))
                <div class="bg-white shadow-md rounded-xl border border-red-200 overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all">
                    <div>
                        <div class="px-5 py-3.5 bg-gradient-to-r from-red-600 to-rose-500 flex items-center justify-between text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <h3 class="text-sm font-bold tracking-wide">High Urgency Requisitions</h3>
                            </div>
                            <span class="inline-flex items-center justify-center bg-white text-red-600 font-extrabold text-xs px-2.5 py-0.5 rounded-full shadow-sm">{{ $highUrgencyPRsCount }}</span>
                        </div>

                        <div class="p-4">
                            @if($highUrgencyPRsList->count() > 0)
                                <div class="divide-y divide-gray-100">
                                    @foreach($highUrgencyPRsList->take(3) as $pr)
                                        <div class="py-2 flex items-center justify-between gap-2 hover:bg-red-50/40 p-1.5 rounded transition-colors">
                                            <div>
                                                <span class="font-mono font-bold text-xs text-indigo-600">PR #{{ $pr->doc_num ?? $pr->id }}</span>
                                                <span class="text-[11px] text-gray-500 block">Req Date: {{ \Carbon\Carbon::parse($pr->delivery_date)->format('Y-m-d') }}</span>
                                            </div>
                                            <a href="{{ route('purchase-request.show', $pr->id) }}" class="text-[11px] font-semibold text-indigo-600 hover:underline">View →</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-xs text-gray-400">
                                    No high urgency requisitions
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border-t border-gray-100 text-right">
                        <a href="{{ route('purchase-request.index') }}" class="text-xs font-semibold text-red-600 hover:underline">View Requisitions →</a>
                    </div>
                </div>
            @endif

            <!-- Box 3: Purchase Requisitions Summary Box -->
            @if(!empty($userWidgets['pr_summary']))
                <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all">
                    <div>
                        <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 flex items-center justify-between text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-sm font-bold tracking-wide">Purchase Requisitions</h3>
                            </div>
                            <span class="text-xs font-bold bg-white/20 px-2 py-0.5 rounded text-white font-mono">{{ $prTotal }} Total</span>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 text-center mb-4">
                                <div class="bg-indigo-50 p-2.5 rounded-lg border border-indigo-100">
                                    <span class="block text-[11px] text-indigo-700 font-semibold uppercase">Open</span>
                                    <span class="text-xl font-black text-indigo-900">{{ $prOpen }}</span>
                                </div>
                                <div class="bg-amber-50 p-2.5 rounded-lg border border-amber-100">
                                    <span class="block text-[11px] text-amber-700 font-semibold uppercase">Draft</span>
                                    <span class="text-xl font-black text-amber-900">{{ $prDraft }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('purchase-request.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">List All</a>
                        <a href="{{ route('purchase-request.create') }}" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1 rounded transition-colors">+ New PR</a>
                    </div>
                </div>
            @endif

            <!-- Box 4: Purchase Quotations Summary Box -->
            @if(!empty($userWidgets['pq_summary']))
                <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all">
                    <div>
                        <div class="px-5 py-3.5 bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-between text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="text-sm font-bold tracking-wide">Purchase Quotations</h3>
                            </div>
                            <span class="text-xs font-bold bg-white/20 px-2 py-0.5 rounded text-white font-mono">{{ $pqTotal }} Total</span>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 text-center mb-4">
                                <div class="bg-purple-50 p-2.5 rounded-lg border border-purple-100">
                                    <span class="block text-[11px] text-purple-700 font-semibold uppercase">Open</span>
                                    <span class="text-xl font-black text-purple-900">{{ $pqOpen }}</span>
                                </div>
                                <div class="bg-amber-50 p-2.5 rounded-lg border border-amber-100">
                                    <span class="block text-[11px] text-amber-700 font-semibold uppercase">Draft</span>
                                    <span class="text-xl font-black text-amber-900">{{ $pqDraft }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('purchase-quotation.index') }}" class="text-xs font-semibold text-purple-600 hover:underline">List All</a>
                        <a href="{{ route('purchase-quotation.create') }}" class="text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded transition-colors">+ New PQ</a>
                    </div>
                </div>
            @endif

            <!-- Box 5: Purchase Orders Summary Box -->
            @if(!empty($userWidgets['po_summary']))
                <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all">
                    <div>
                        <div class="px-5 py-3.5 bg-gradient-to-r from-teal-600 to-emerald-600 flex items-center justify-between text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <h3 class="text-sm font-bold tracking-wide">Purchase Orders</h3>
                            </div>
                            <span class="text-xs font-bold bg-white/20 px-2 py-0.5 rounded text-white font-mono">{{ $poTotal }} Total</span>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-3 text-center mb-4">
                                <div class="bg-teal-50 p-2.5 rounded-lg border border-teal-100">
                                    <span class="block text-[11px] text-teal-700 font-semibold uppercase">Synced to SAP</span>
                                    <span class="text-xl font-black text-teal-900">{{ $poSynced }}</span>
                                </div>
                                <div class="bg-emerald-50 p-2.5 rounded-lg border border-emerald-100">
                                    <span class="block text-[11px] text-emerald-700 font-semibold uppercase">Active Orders</span>
                                    <span class="text-xl font-black text-emerald-900">{{ $poOpen }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <a href="{{ route('purchase-order.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">List All</a>
                        <a href="{{ route('purchase-order.create') }}" class="text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 px-3 py-1 rounded transition-colors">+ New PO</a>
                    </div>
                </div>
            @endif

        </div>

        <!-- Customize Dashboard Modal -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md sm:w-full p-6 z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Customize SAP B1 Cockpit</h3>
                    <p class="text-xs text-gray-500 mb-4">Toggle box-by-box widgets to show or hide on your dashboard.</p>

                    <form method="POST" action="{{ route('dashboard.widgets') }}">
                        @csrf
                        <div class="space-y-3 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Pending Approvals Box</span>
                                <input type="checkbox" name="pending_approvals" value="1" {{ !empty($userWidgets['pending_approvals']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>High Urgency Requisitions Box</span>
                                <input type="checkbox" name="high_urgency_pr" value="1" {{ !empty($userWidgets['high_urgency_pr']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Requisitions Box</span>
                                <input type="checkbox" name="pr_summary" value="1" {{ !empty($userWidgets['pr_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Quotations Box</span>
                                <input type="checkbox" name="pq_summary" value="1" {{ !empty($userWidgets['pq_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between text-sm font-medium text-gray-800">
                                <span>Purchase Orders Box</span>
                                <input type="checkbox" name="po_summary" value="1" {{ !empty($userWidgets['po_summary']) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showEditModal = false" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save Cockpit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
