<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">System Logs</h1>
                <p class="mt-2 text-sm text-gray-700">A complete audit trail of user activity, SAP synchronization events, and automated scheduler actions.</p>
            </div>
        </div>

        <!-- Filter / Search -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4">
            <form method="GET" action="{{ route('logs.index') }}" class="flex flex-1 gap-4">
                <div class="w-48">
                    <select name="category" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm" onchange="this.form.submit()">
                        <option value="All" {{ request('category') == 'All' ? 'selected' : '' }}>All Categories</option>
                        <option value="admin" {{ request('category') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="sap" {{ request('category') == 'sap' ? 'selected' : '' }}>SAP Function</option>
                        <option value="login" {{ request('category') == 'login' ? 'selected' : '' }}>Login/Logout</option>
                        <option value="scheduler" {{ request('category') == 'scheduler' ? 'selected' : '' }}>Scheduler</option>
                    </select>
                </div>
                <div class="flex-1 max-w-sm">
                    <div class="relative rounded-md shadow-sm mt-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Search logs, user, action...">
                    </div>
                </div>
                <button type="submit" class="mt-1 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Search</button>
                @if(request()->has('search') || (request()->has('category') && request()->category != 'All'))
                    <a href="{{ route('logs.index') }}" class="mt-1 rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </div>

        <div class="mt-4 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Time</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Action</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Details</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">PC / IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            @if($log->category === 'admin')
                                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">Administrator</span>
                                            @elseif($log->category === 'sap')
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">SAP Function</span>
                                            @elseif($log->category === 'login')
                                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Login</span>
                                            @elseif($log->category === 'scheduler')
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Scheduler</span>
                                            @else
                                                {{ ucfirst($log->category) }}
                                            @endif
                                            
                                            @if($log->instant_sync)
                                                <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20 ml-2">Instant Sync</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                            {{ $log->user ? $log->user->name : 'System / Scheduler' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                            {{ $log->action }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div class="max-w-xs truncate" title="{{ $log->details }}">{{ $log->details }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <div>{{ $log->pc_name }}</div>
                                            <div class="text-xs text-gray-400">{{ $log->ip_address }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-gray-500">
                                            No system logs found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
