<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Approval Decision: {{ $request->document_type }} #{{ $request->document_id }}</h1>
                <p class="mt-1 text-sm text-gray-500">Submitted by {{ $request->originator->name ?? 'User' }} under template "{{ $request->template->name ?? '-' }}".</p>
            </div>
            <a href="{{ route('approvals.decisions.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to Decisions</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Document Draft Inspection -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Document Details</h3>
                    @if($document)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
                            <div>
                                <span class="text-gray-500 font-medium block">Document #</span>
                                <span class="text-gray-900 font-bold font-mono">{{ $document->doc_num ?? $document->id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 font-medium block">Urgency Level</span>
                                <span class="font-semibold capitalize text-indigo-600">{{ $document->urgency_level ?? 'Normal' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 font-medium block">Status</span>
                                <span class="inline-flex items-center rounded bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">Pending Approval</span>
                            </div>
                        </div>
                        @if($document->comments)
                            <div class="mb-4 text-sm bg-gray-50 p-3 rounded">
                                <span class="text-gray-500 font-medium block">Comments:</span>
                                <span class="text-gray-800">{{ $document->comments }}</span>
                            </div>
                        @endif

                        <!-- Document Lines -->
                        <h4 class="text-sm font-bold text-gray-900 mb-2">Line Items</h4>
                        <div class="overflow-x-auto border rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700">#</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700">Code</th>
                                        <th class="py-2 px-3 text-left font-semibold text-gray-700">Description</th>
                                        <th class="py-2 px-3 text-right font-semibold text-gray-700">Qty</th>
                                        <th class="py-2 px-3 text-right font-semibold text-gray-700">Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($document->lines as $idx => $line)
                                        <tr>
                                            <td class="py-2 px-3 text-gray-500 font-mono">{{ $idx + 1 }}</td>
                                            <td class="py-2 px-3 font-semibold text-indigo-600 font-mono">{{ $line->item_code ?? $line->account_code }}</td>
                                            <td class="py-2 px-3 text-gray-900">{{ $line->item_description ?? $line->account_name }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-semibold">{{ number_format($line->quantity ?? $line->quoted_qty ?? 1, 2) }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-semibold text-gray-900">{{ number_format($line->price ?? $line->unit_price ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Document draft information not found.</p>
                    @endif
                </div>
            </div>

            <!-- Approval Vote Form & Stage Info -->
            <div class="space-y-6">
                <div class="bg-white shadow rounded-lg p-6 border-2 border-indigo-500/20">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">Cast Approval Decision</h3>
                    <p class="text-xs text-gray-500 mb-4">Current Stage: <strong>{{ $request->currentStage->name ?? '-' }}</strong></p>

                    <form action="{{ route('approvals.decisions.vote', $request->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Decision Remarks / Comments</label>
                            <textarea name="comments" rows="3" placeholder="Optional comments regarding your approval or rejection..." class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" name="decision" value="rejected" onclick="return confirm('Reject this document request?')" class="flex-1 rounded-md bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                Reject Document
                            </button>
                            <button type="submit" name="decision" value="approved" class="flex-1 rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                                Approve Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
