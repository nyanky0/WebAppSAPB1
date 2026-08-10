@php
    $permissions = auth()->user()?->role?->permissions ?? [];
@endphp

<div :class="sidebarOpen ? 'w-64' : 'w-20'" class="flex flex-col h-screen transition-all duration-300 bg-gray-900 border-r border-gray-800">
    <div class="flex items-center justify-center h-16 border-b border-gray-800">
        <div class="text-xl font-bold text-white truncate" x-show="sidebarOpen">SAP B1 AddOn</div>
        <div class="text-xl font-bold text-white" x-show="!sidebarOpen">B1</div>
    </div>
    
    <div class="flex flex-col flex-1 overflow-y-auto">
        <nav class="flex-1 px-2 py-4 space-y-1">
            
            <!-- Dashboard (Always visible) -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group">
                <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>

            <!-- Folder: Administrator -->
            @if(in_array('Administrator.Config', $permissions) || in_array('Administrator.Roles', $permissions) || in_array('Administrator.Users', $permissions) || in_array('Administrator.Items', $permissions))
            <div x-data="{ open: false }" class="space-y-1">
                <button @click="open = !open" type="button" class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="flex-1 text-left">Administrator</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-90 text-gray-400' : 'text-gray-400'" class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open && sidebarOpen" class="space-y-1">
                    @if(in_array('Administrator.Config', $permissions))
                        <a href="{{ route('config.index') }}" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Config</a>
                    @endif
                    @if(in_array('Administrator.Roles', $permissions))
                        <a href="{{ route('roles.index') }}" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Roles</a>
                    @endif
                    @if(in_array('Administrator.Users', $permissions))
                        <a href="{{ route('users.index') }}" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Users</a>
                    @endif
                    @if(in_array('Administrator.Config', $permissions) || in_array('Administrator.Items', $permissions))
                        <a href="{{ route('items.index') }}" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Items</a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Folder: Purchase -->
            @if(in_array('Purchase.PurchaseRequest', $permissions))
            <div x-data="{ open: false }" class="space-y-1">
                <button @click="open = !open" type="button" class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="flex-1 text-left">Purchase</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-90 text-gray-400' : 'text-gray-400'" class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open && sidebarOpen" class="space-y-1">
                    @if(in_array('Purchase.PurchaseRequest', $permissions))
                        <a href="{{ route('purchase-request.create') }}" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800">Purchase Request</a>
                    @endif
                </div>
            </div>
            @endif

        </nav>
    </div>
</div>
