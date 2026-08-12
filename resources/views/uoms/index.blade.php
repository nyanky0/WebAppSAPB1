<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Units of Measurement (UoM)</h1>
                <p class="mt-1 text-sm text-gray-500">Manage UoMs and UoM Groups synced with SAP Business One.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0" x-data="{ isSyncing: false }">
                <a href="{{ route('uoms.create') }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create UoM
                </a>
                <form action="{{ route('uoms.sync') }}" method="POST" @submit="isSyncing = true">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync with SAP
                    </button>
                </form>
            </div>
        </div>

        <div x-data="{ tab: 'uoms' }" class="mt-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'uoms'" :class="tab === 'uoms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Units of Measure ({{ $uoms->total() }})
                    </button>
                    <button @click="tab = 'groups'" :class="tab === 'groups' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        UoM Groups ({{ $uomGroups->count() }})
                    </button>
                </nav>
            </div>

            <!-- Tab 1: Units of Measure -->
            <div x-show="tab === 'uoms'" class="mt-6">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Code</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sync Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($uoms as $uom)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono font-semibold text-gray-900 sm:pl-6">{{ $uom->code }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $uom->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <x-sync-status :status="$uom->sync_status" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-sm text-gray-500">No UoMs found. Click "Sync with SAP" to download.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $uoms->links() }}
                </div>
            </div>

            <!-- Tab 2: UoM Groups & Conversions -->
            <div x-show="tab === 'groups'" class="mt-6" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($uomGroups as $group)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                            <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $group->group_name }}</h3>
                                    <p class="text-xs font-mono text-indigo-600 mt-0.5">Code: {{ $group->group_code }} | Base UoM: {{ $group->base_uom ?: 'N/A' }}</p>
                                </div>
                                <x-sync-status :status="$group->sync_status" />
                            </div>

                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Conversion Settings / Rules</h4>
                            @if(!empty($group->conversions) && is_array($group->conversions))
                                <div class="bg-gray-50 rounded-md p-3 space-y-2">
                                    @foreach($group->conversions as $conv)
                                        <div class="flex justify-between text-xs text-gray-700 border-b border-gray-200/60 pb-1.5 last:border-0 last:pb-0">
                                            <span class="font-medium">1 {{ $conv['alt_uom'] ?? 'Alt UoM' }}</span>
                                            <span class="font-mono font-semibold text-indigo-600">= {{ $conv['base_qty'] ?? 1 }} {{ $group->base_uom }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic">No custom conversion rules configured.</p>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-2 py-12 text-center text-sm text-gray-500 bg-white rounded-lg border border-gray-200">
                            No UoM Groups found. Click "Sync with SAP" to download.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
