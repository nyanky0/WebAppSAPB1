<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Approval Decisions</h1>
            <p class="mt-1 text-sm text-gray-500">List of document approvals waiting for your review and vote.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Document</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Template</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Current Stage</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Originator</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Submitted At</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($requests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 pl-6 pr-3 text-sm font-bold text-indigo-600 font-mono">
                                {{ $req->document_type }} #{{ $req->document_id }}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900 font-medium">{{ $req->template->name ?? '-' }}</td>
                            <td class="px-3 py-4 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                                    Step {{ $req->current_stage_order }}: {{ $req->currentStage->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900 font-medium">{{ $req->originator->name ?? 'User' }}</td>
                            <td class="px-3 py-4 text-sm text-center text-gray-500">{{ $req->created_at ? $req->created_at->format('M d, Y H:i') : '-' }}</td>
                            <td class="px-3 py-4 text-sm text-center font-medium">
                                <a href="{{ route('approvals.decisions.show', $req->id) }}" class="inline-flex items-center text-xs font-semibold text-white bg-indigo-600 px-3 py-1.5 rounded hover:bg-indigo-700">
                                    Inspect & Vote
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-sm text-gray-500">
                                No pending approval requests requiring your decision at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
