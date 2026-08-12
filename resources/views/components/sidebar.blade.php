@php
    $permissions = auth()->user()?->role?->permissions ?? [];
@endphp

<div :class="sidebarOpen ? 'w-64' : 'w-20'"
    class="relative z-50 flex flex-col h-screen transition-all duration-300 bg-gray-900 border-r border-gray-800 shrink-0">
    <div class="flex items-center justify-center h-16 border-b border-gray-800 px-4">
        <div class="flex items-center space-x-3.5 min-w-0" x-show="sidebarOpen">
            <img src="{{ asset('images/sap-logo.svg') }}" alt="SAP Logo" class="h-6 w-auto object-contain shrink-0">
            <span class="text-base font-bold text-white tracking-wide whitespace-nowrap">SAP B1 AddOn</span>
        </div>
        <div class="flex items-center justify-center shrink-0" x-show="!sidebarOpen">
            <img src="{{ asset('images/sap-logo.svg') }}" alt="SAP Logo" class="h-6 w-auto object-contain">
        </div>
    </div>

    <div class="flex flex-col flex-1 overflow-y-auto">
        <nav x-data="{ activeFolder: null }" class="flex-1 px-2 py-4 space-y-1">

            <!-- Dashboard (Always visible) -->
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group">
                <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>

            <!-- Folder: Administrator -->
            @if (in_array('Administrator.Config', $permissions) ||
                    in_array('Administrator.Roles', $permissions) ||
                    in_array('Administrator.Users', $permissions))
                <div class="space-y-1">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'admin'; } else { activeFolder = activeFolder === 'admin' ? null : 'admin'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left">Administrator</span>
                        <svg x-show="sidebarOpen"
                            :class="activeFolder === 'admin' ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="activeFolder === 'admin' && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Administrator.Config', $permissions))
                            <a href="{{ route('config.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Config</a>
                        @endif
                        @if (in_array('Administrator.Roles', $permissions))
                            <a href="{{ route('roles.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Roles</a>
                        @endif
                        @if (in_array('Administrator.Users', $permissions))
                            <a href="{{ route('users.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Users</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: SAP Master Data -->
            @if (in_array('Administrator.Items', $permissions) || in_array('Administrator.Taxes', $permissions))
                <div class="space-y-1 mt-1">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'master_data'; } else { activeFolder = activeFolder === 'master_data' ? null : 'master_data'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left">SAP Master Data</span>
                        <svg x-show="sidebarOpen"
                            :class="activeFolder === 'master_data' ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="activeFolder === 'master_data' && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Administrator.Items', $permissions))
                            <a href="{{ route('item-groups.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Item
                                Groups</a>
                            <a href="{{ route('items.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Items</a>
                            <a href="{{ route('uoms.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Units of Measure</a>
                            <a href="{{ route('warehouses.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Warehouses & Bins</a>
                            <a href="{{ route('chart-of-accounts.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Chart of Accounts</a>
                            <a href="{{ route('dimensions.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Dimensions</a>
                            <a href="{{ route('cost-centers.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Cost Centers</a>
                        @endif
                        @if (in_array('Administrator.Taxes', $permissions))
                            <a href="{{ route('taxes.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Taxes</a>
                            <a href="{{ route('business-partners.index') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Business
                                Partners</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: Scheduler -->
            @if (in_array('Scheduler.MasterData', $permissions) || in_array('Scheduler.Document', $permissions))
                <div class="space-y-1 mt-1">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'scheduler'; } else { activeFolder = activeFolder === 'scheduler' ? null : 'scheduler'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left">Scheduler</span>
                        <svg x-show="sidebarOpen"
                            :class="activeFolder === 'scheduler' ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="activeFolder === 'scheduler' && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Scheduler.MasterData', $permissions))
                            <a href="{{ route('scheduler.master-data') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Master
                                Data</a>
                        @endif
                        @if (in_array('Scheduler.Document', $permissions))
                            <a href="{{ route('scheduler.document') }}"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Document</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: Transaction -->
            @if (in_array('Purchase.PurchaseRequest', $permissions))
                <div class="space-y-1 mt-1">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'transaction'; } else { activeFolder = activeFolder === 'transaction' ? null : 'transaction'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left">Transaction</span>
                        <svg x-show="sidebarOpen"
                            :class="activeFolder === 'transaction' ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="activeFolder === 'transaction' && sidebarOpen" x-collapse class="space-y-1">
                        <a href="{{ route('purchase-request.index') }}"
                            class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Purchase
                            Request</a>
                    </div>
                </div>
            @endif

        </nav>
    </div>

    <!-- System Logs -->
    @if (in_array('Administrator.Logs', $permissions))
        <div class="p-4 border-t border-gray-800 shrink-0">
            <a href="{{ route('logs.index') }}"
                class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group transition-colors">
                <svg class="w-6 h-6 shrink-0 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span x-show="sidebarOpen" class="truncate text-left">System Logs</span>
            </a>
        </div>
    @endif

    <!-- User Profile & Logout -->
    <div class="p-4 border-t border-gray-800 shrink-0">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group transition-colors">
                <svg class="w-6 h-6 shrink-0 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="sidebarOpen" class="truncate text-left">Logout</span>
            </button>
        </form>
    </div>
</div>
