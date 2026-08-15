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
        <nav x-data="{
            searchQuery: '',
            activeFolder: null,
            match(text) {
                if (!this.searchQuery) return true;
                return text.toLowerCase().includes(this.searchQuery.toLowerCase());
            },
            folderHasMatch(items) {
                if (!this.searchQuery) return false;
                return items.some(item => this.match(item));
            },
            isFolderOpen(folderKey, items) {
                if (this.searchQuery) {
                    return this.folderHasMatch(items);
                }
                return this.activeFolder === folderKey;
            },
            highlight(text) {
                if (!this.searchQuery || !text) return text;
                const q = this.searchQuery.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                if (!q) return text;
                const regex = new RegExp(`(${q})`, 'gi');
                return text.replace(regex, '<mark class=&quot;bg-amber-400 text-gray-900 font-bold px-0.5 rounded&quot;>$1</mark>');
            }
        }" class="flex-1 px-2 py-4 space-y-1">

            <!-- Debounced Navigation Search Box with Spaced Right-Aligned Search Icon -->
            <div x-show="sidebarOpen" class="px-2 mb-3">
                <div class="relative flex items-center">
                    <input type="text" x-model.debounce.300ms="searchQuery" placeholder="Search menu..." class="w-full bg-gray-800 text-gray-200 text-xs rounded-md pl-3.5 pr-10 py-1.5 border border-gray-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 placeholder-gray-400 font-sans tracking-wide">
                    <div class="absolute right-3 flex items-center pointer-events-none text-gray-400 pl-2 border-l border-gray-700/60 my-0.5">
                        <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Dashboard (Always visible unless filtered) -->
            <a href="{{ route('dashboard') }}" x-show="match('Dashboard')"
                class="flex items-center px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group">
                <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                <span x-show="sidebarOpen" x-html="highlight('Dashboard')">Dashboard</span>
            </a>

            <!-- Folder: Administrator -->
            @if (in_array('Administrator.Config', $permissions) ||
                    in_array('Administrator.Roles', $permissions) ||
                    in_array('Administrator.Users', $permissions))
                <div class="space-y-1" x-show="match('Administrator') || folderHasMatch(['Config', 'Roles', 'Users'])">
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
                        <span x-show="sidebarOpen" class="flex-1 text-left" x-html="highlight('Administrator')">Administrator</span>
                        <svg x-show="sidebarOpen"
                            :class="isFolderOpen('admin', ['Config', 'Roles', 'Users']) ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="isFolderOpen('admin', ['Config', 'Roles', 'Users']) && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Administrator.Config', $permissions))
                            <a href="{{ route('config.index') }}" x-show="match('Config')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Config')">Config</a>
                        @endif
                        @if (in_array('Administrator.Roles', $permissions))
                            <a href="{{ route('roles.index') }}" x-show="match('Roles')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Roles')">Roles</a>
                        @endif
                        @if (in_array('Administrator.Users', $permissions))
                            <a href="{{ route('users.index') }}" x-show="match('Users')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Users')">Users</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: SAP Master Data -->
            @if (in_array('Administrator.Items', $permissions) || 
                 in_array('Administrator.Uoms', $permissions) || 
                 in_array('Administrator.Warehouses', $permissions) || 
                 in_array('Administrator.ChartOfAccounts', $permissions) || 
                 in_array('Administrator.Dimensions', $permissions) || 
                 in_array('Administrator.CostCenters', $permissions) || 
                 in_array('Administrator.Taxes', $permissions) || 
                 in_array('Administrator.WithholdingTaxes', $permissions) || 
                 in_array('Administrator.Branches', $permissions) || 
                 in_array('Administrator.BusinessPartners', $permissions))
                <div class="space-y-1 mt-1" x-show="match('SAP Master Data') || folderHasMatch(['Item Groups', 'Items', 'Units of Measure', 'Warehouses & Bins', 'Chart of Accounts', 'Dimensions', 'Cost Centers', 'Taxes', 'Withholding Taxes', 'Branches', 'Business Partners'])">
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
                        <span x-show="sidebarOpen" class="flex-1 text-left" x-html="highlight('SAP Master Data')">SAP Master Data</span>
                        <svg x-show="sidebarOpen"
                            :class="isFolderOpen('master_data', ['Item Groups', 'Items', 'Units of Measure', 'Warehouses & Bins', 'Chart of Accounts', 'Dimensions', 'Cost Centers', 'Taxes', 'Withholding Taxes', 'Branches', 'Business Partners']) ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="isFolderOpen('master_data', ['Item Groups', 'Items', 'Units of Measure', 'Warehouses & Bins', 'Chart of Accounts', 'Dimensions', 'Cost Centers', 'Taxes', 'Withholding Taxes', 'Branches', 'Business Partners']) && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Administrator.Items', $permissions))
                            <a href="{{ route('item-groups.index') }}" x-show="match('Item Groups')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Item Groups')">Item Groups</a>
                            <a href="{{ route('items.index') }}" x-show="match('Items')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Items')">Items</a>
                        @endif
                        @if (in_array('Administrator.Uoms', $permissions))
                            <a href="{{ route('uoms.index') }}" x-show="match('Units of Measure')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Units of Measure')">Units of Measure</a>
                        @endif
                        @if (in_array('Administrator.Warehouses', $permissions))
                            <a href="{{ route('warehouses.index') }}" x-show="match('Warehouses & Bins')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Warehouses & Bins')">Warehouses & Bins</a>
                        @endif
                        @if (in_array('Administrator.ChartOfAccounts', $permissions))
                            <a href="{{ route('chart-of-accounts.index') }}" x-show="match('Chart of Accounts')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Chart of Accounts')">Chart of Accounts</a>
                        @endif
                        @if (in_array('Administrator.Dimensions', $permissions))
                            <a href="{{ route('dimensions.index') }}" x-show="match('Dimensions')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Dimensions')">Dimensions</a>
                        @endif
                        @if (in_array('Administrator.CostCenters', $permissions))
                            <a href="{{ route('cost-centers.index') }}" x-show="match('Cost Centers')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Cost Centers')">Cost Centers</a>
                        @endif
                        @if (in_array('Administrator.Taxes', $permissions))
                            <a href="{{ route('taxes.index') }}" x-show="match('Taxes')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Taxes')">Taxes</a>
                        @endif
                        @if (in_array('Administrator.WithholdingTaxes', $permissions))
                            <a href="{{ route('withholding-taxes.index') }}" x-show="match('Withholding Taxes')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Withholding Taxes')">Withholding Taxes</a>
                        @endif
                        @if (in_array('Administrator.Branches', $permissions))
                            <a href="{{ route('branches.index') }}" x-show="match('Branches')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Branches')">Branches</a>
                        @endif
                        @if (in_array('Administrator.BusinessPartners', $permissions))
                            <a href="{{ route('business-partners.index') }}" x-show="match('Business Partners')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Business Partners')">Business Partners</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: Scheduler -->
            @if (in_array('Scheduler.MasterData', $permissions) || in_array('Scheduler.Document', $permissions))
                <div class="space-y-1 mt-1" x-show="match('Scheduler') || folderHasMatch(['Master Data', 'Document'])">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'scheduler'; } else { activeFolder = activeFolder === 'scheduler' ? null : 'scheduler'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left" x-html="highlight('Scheduler')">Scheduler</span>
                        <svg x-show="sidebarOpen"
                            :class="isFolderOpen('scheduler', ['Master Data', 'Document']) ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="isFolderOpen('scheduler', ['Master Data', 'Document']) && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Scheduler.MasterData', $permissions))
                            <a href="{{ route('scheduler.master-data') }}" x-show="match('Master Data')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Master Data')">Master Data</a>
                        @endif
                        @if (in_array('Scheduler.Document', $permissions))
                            <a href="{{ route('scheduler.document') }}" x-show="match('Document')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Document')">Document</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: Approval -->
            @if (in_array('Approval.Stages', $permissions) || in_array('Approval.Templates', $permissions) || in_array('Approval.Decisions', $permissions))
                <div class="space-y-1 mt-1" x-show="match('Approval') || folderHasMatch(['Approval Stage', 'Approval Template', 'Approval Decision'])">
                    <button
                        @click="if (!sidebarOpen) { sidebarOpen = true; activeFolder = 'approval'; } else { activeFolder = activeFolder === 'approval' ? null : 'approval'; }"
                        type="button"
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white group focus:outline-none">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left" x-html="highlight('Approval')">Approval</span>
                        <svg x-show="sidebarOpen"
                            :class="isFolderOpen('approval', ['Approval Stage', 'Approval Template', 'Approval Decision']) ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="isFolderOpen('approval', ['Approval Stage', 'Approval Template', 'Approval Decision']) && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Approval.Stages', $permissions))
                            <a href="{{ route('approvals.stages.index') }}" x-show="match('Approval Stage')" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Approval Stage')">Approval Stage</a>
                        @endif
                        @if (in_array('Approval.Templates', $permissions))
                            <a href="{{ route('approvals.templates.index') }}" x-show="match('Approval Template')" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Approval Template')">Approval Template</a>
                        @endif
                        @if (in_array('Approval.Decisions', $permissions))
                            <a href="{{ route('approvals.decisions.index') }}" x-show="match('Approval Decision')" class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Approval Decision')">Approval Decision</a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Folder: Transaction -->
            @if (in_array('Purchase.PurchaseRequest', $permissions) || in_array('Purchase.PurchaseQuotation', $permissions) || in_array('Purchase.PurchaseOrder', $permissions))
                <div class="space-y-1 mt-1" x-show="match('Transaction') || folderHasMatch(['Purchase Requisition', 'Purchase Quotation', 'Purchase Order'])">
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
                        <span x-show="sidebarOpen" class="flex-1 text-left" x-html="highlight('Transaction')">Transaction</span>
                        <svg x-show="sidebarOpen"
                            :class="isFolderOpen('transaction', ['Purchase Requisition', 'Purchase Quotation', 'Purchase Order']) ? 'rotate-90 text-gray-400' : 'text-gray-400'"
                            class="w-5 h-5 ml-auto transition-colors duration-150 ease-in-out transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div x-show="isFolderOpen('transaction', ['Purchase Requisition', 'Purchase Quotation', 'Purchase Order']) && sidebarOpen" x-collapse class="space-y-1">
                        @if (in_array('Purchase.PurchaseRequest', $permissions))
                            <a href="{{ route('purchase-request.index') }}" x-show="match('Purchase Requisition')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Purchase Requisition')">Purchase Requisition</a>
                        @endif
                        @if (in_array('Purchase.PurchaseQuotation', $permissions))
                            <a href="{{ route('purchase-quotation.index') }}" x-show="match('Purchase Quotation')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Purchase Quotation')">Purchase Quotation</a>
                        @endif
                        @if (in_array('Purchase.PurchaseOrder', $permissions))
                            <a href="{{ route('purchase-order.index') }}" x-show="match('Purchase Order')"
                                class="flex items-center w-full py-2 pl-11 pr-2 text-sm font-medium text-gray-400 rounded-md hover:text-white hover:bg-gray-800" x-html="highlight('Purchase Order')">Purchase Order</a>
                        @endif
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
