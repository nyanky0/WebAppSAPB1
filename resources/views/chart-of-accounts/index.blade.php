<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Chart of Accounts (COA)</h1>
                <p class="mt-1 text-sm text-gray-500">Synchronized G/L Accounts, types, control accounts, and financial head categories.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0" x-data="{ isSyncing: false }">
                <form action="{{ route('chart-of-accounts.sync') }}" method="POST" @submit="isSyncing = true">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync COA from SAP
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
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Syncing Chart of Accounts...</h3>
                            <p class="text-sm text-gray-500">Please wait while we fetch G/L accounts from SAP Service Layer. Page chunks are processed safely with auto-retry.</p>
                            <p class="text-xs text-indigo-600 mt-2 font-medium">Auto-retry enabled (up to {{ \App\Models\Config::first()?->max_retries ?? 3 }} attempts on network/server errors)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter / Search Bar -->
        <div class="mt-8 bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-200">
            <form method="GET" action="{{ route('chart-of-accounts.index') }}" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-[240px] max-w-sm">
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Search G/L Code, Name, or Format Code...">
                    </div>
                </div>

                <div>
                    <select name="category" onchange="this.form.submit()" class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">All Categories (Assets, Liabilities, etc.)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="account_type" onchange="this.form.submit()" class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="Postable" {{ request('account_type') === 'Postable' ? 'selected' : '' }}>Postable Account</option>
                        <option value="Title" {{ request('account_type') === 'Title' ? 'selected' : '' }}>Title Account</option>
                    </select>
                </div>

                <div>
                    <select name="per_page" onchange="this.form.submit()" class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>

                <button type="submit" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">Search</button>
                @if(request()->filled('search') || request()->filled('category') || request()->filled('account_type') || request()->has('per_page'))
                    <a href="{{ route('chart-of-accounts.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">Clear Filter</a>
                @endif
            </form>
        </div>

        <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">G/L Account Code</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Ext Code</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type & Level</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Currency</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Flags</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($accounts as $acct)
                        <tr class="{{ $acct->account_type === 'Title' ? 'bg-gray-50/80 font-bold' : '' }}">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-900 sm:pl-6">{{ $acct->code }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ $acct->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono">{{ $acct->external_code ?: '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">{{ $acct->category }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span class="inline-flex items-center rounded-md {{ $acct->account_type === 'Title' ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-blue-50 text-blue-700 ring-blue-600/20' }} px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    {{ $acct->account_type }} (L{{ $acct->levels }})
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600 font-mono">{{ $acct->currency ?: 'All' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm space-x-1">
                                @if($acct->is_control_account)
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Control</span>
                                @endif
                                @if($acct->is_cash_account)
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">Cash</span>
                                @endif
                                @if($acct->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-sm text-gray-500">No G/L accounts found. Click "Sync COA from SAP" to download.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $accounts->links() }}
        </div>
    </div>
</x-app-layout>
