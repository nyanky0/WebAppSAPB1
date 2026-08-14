<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Approval Templates</h1>
                <p class="mt-1 text-sm text-gray-500">Configure target transaction templates, originators, ordered stage flows, and terms.</p>
            </div>
            <div>
                <a href="{{ route('approvals.templates.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + Add Approval Template
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Template Name</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Target Document</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Stages Flow</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Terms Type</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($templates as $tmpl)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 pl-6 pr-3 text-sm font-bold text-gray-900">{{ $tmpl->name }}</td>
                            <td class="px-3 py-4 text-sm font-semibold text-indigo-600">{{ $tmpl->target_document }}</td>
                            <td class="px-3 py-4 text-sm text-center">
                                @if($tmpl->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @foreach($tmpl->stages as $ts)
                                        <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">
                                            {{ $ts->stage_order }}. {{ $ts->stage->name ?? 'Stage' }}
                                        </span>
                                        @if(!$loop->last)
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-600 capitalize">
                                <span class="font-medium text-gray-800">{{ $tmpl->terms_type }}</span>
                                @if($tmpl->terms_type === 'conditional')
                                    <span class="text-xs text-indigo-600 block">({{ $tmpl->terms->count() }} condition(s))</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-center font-medium space-x-2">
                                <a href="{{ route('approvals.templates.edit', $tmpl->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('approvals.templates.destroy', $tmpl->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-500">No approval templates configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
