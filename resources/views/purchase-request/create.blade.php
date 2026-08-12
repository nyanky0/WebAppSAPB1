<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseRequestForm()" @vendor-selected.window="handleVendorSelected($event.detail)">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Purchase Request</h1>
                <p class="mt-1 text-sm text-gray-500">Submit a new purchase request to SAP Business One.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('purchase-request.index') }}" class="inline-flex items-center rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('purchase-request.store') }}" method="POST" class="mt-8 space-y-8" @submit.prevent="submitForm">
            @csrf

            <!-- Header Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        
                        <!-- Document Type -->
                        <div class="sm:col-span-3">
                            <label for="doc_type" class="block text-sm font-medium leading-6 text-gray-900">Document Type</label>
                            <div class="mt-2">
                                <select name="doc_type" id="doc_type" x-model="formData.doc_type" @change="onDocTypeChange" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 font-semibold text-indigo-700">
                                    <option value="dssItem">Item Document</option>
                                    <option value="dssService">Service Document</option>
                                </select>
                            </div>
                        </div>

                        <!-- Vendor Selection -->
                        <div class="sm:col-span-3">
                            <label for="vendor" class="block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                            <div class="mt-2 flex space-x-2">
                                <input type="text" x-model="vendorName" readonly class="block w-full rounded-md border-0 py-1.5 bg-gray-50 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6" placeholder="Select a vendor...">
                                <input type="hidden" name="vendor" x-model="formData.vendor" required>
                                <button type="button" @click="openVendorModal" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Select</button>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="sm:col-span-3">
                            <label for="document_date" class="block text-sm font-medium leading-6 text-gray-900">Document Date</label>
                            <div class="mt-2">
                                <input type="date" name="document_date" id="document_date" x-model="formData.document_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="valid_until" class="block text-sm font-medium leading-6 text-gray-900">Valid Until</label>
                            <div class="mt-2">
                                <input type="date" name="valid_until" id="valid_until" x-model="formData.valid_until" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="posting_date" class="block text-sm font-medium leading-6 text-gray-900">Posting Date</label>
                            <div class="mt-2">
                                <input type="date" name="posting_date" id="posting_date" x-model="formData.posting_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="required_date" class="block text-sm font-medium leading-6 text-gray-900">Required Date</label>
                            <div class="mt-2">
                                <input type="date" name="required_date" id="required_date" x-model="formData.required_date" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <!-- Warehouse Header Selection (Item Doc Type) -->
                        <div class="sm:col-span-3" x-show="formData.doc_type === 'dssItem'">
                            <label for="whs_code" class="block text-sm font-medium leading-6 text-gray-900">Warehouse</label>
                            <div class="mt-2">
                                <select name="whs_code" id="whs_code" x-model="formData.whs_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">Select a Warehouse (Synced)</option>
                                    @foreach($warehouses as $whs)
                                        <option value="{{ $whs->whs_code }}">{{ $whs->whs_code }} - {{ $whs->whs_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tax Code -->
                        <div class="sm:col-span-3">
                            <label for="tax_code" class="block text-sm font-medium leading-6 text-gray-900">Default Tax Code</label>
                            <div class="mt-2">
                                <select name="tax_code" id="tax_code" x-model="formData.tax_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                                    <option value="" disabled>Select a Tax Code</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->code }}">{{ $tax->code }} - {{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">
                            Document Lines 
                            <span x-text="formData.doc_type === 'dssItem' ? '(Items)' : '(Services)'" class="text-xs font-normal text-indigo-600"></span>
                        </h2>
                        
                        <!-- Add Button -->
                        <div>
                            <button type="button" x-show="formData.doc_type === 'dssItem'" @click="openItemModal" class="rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                Add Item
                            </button>
                            <button type="button" x-show="formData.doc_type === 'dssService'" @click="openAccountModal" class="rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                Add Account
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 w-12">#</th>
                                    
                                    <!-- Item Columns -->
                                    <template x-if="formData.doc_type === 'dssItem'">
                                        <th class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Item Code</th>
                                    </template>
                                    
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>

                                    <!-- Service Columns -->
                                    <template x-if="formData.doc_type === 'dssService'">
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">G/L Account</th>
                                    </template>

                                    <!-- Item UoM & Qty -->
                                    <template x-if="formData.doc_type === 'dssItem'">
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">UoM</th>
                                    </template>
                                    <template x-if="formData.doc_type === 'dssItem'">
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-28">Quantity</th>
                                    </template>

                                    <!-- Cost Center / Dimensions -->
                                    @foreach($dimensions as $dim)
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-40">Dim {{ $dim->dimension_code }} ({{ $dim->dimension_name }})</th>
                                    @endforeach

                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-36" x-text="formData.doc_type === 'dssItem' ? 'Unit Price' : 'Total Amount'"></th>
                                    <th class="relative whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template x-for="(line, index) in formData.lines" :key="index">
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-500" x-text="index + 1"></td>
                                        
                                        <!-- Item Code (Item Doc) -->
                                        <template x-if="formData.doc_type === 'dssItem'">
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                <input type="hidden" :name="`lines[${index}][item_code]`" :value="line.item_code">
                                                <span x-text="line.item_code" class="font-mono font-semibold text-indigo-600"></span>
                                            </td>
                                        </template>

                                        <!-- Description -->
                                        <td class="px-3 py-4 text-sm text-gray-700 min-w-[200px]">
                                            <template x-if="formData.doc_type === 'dssItem'">
                                                <span>
                                                    <input type="hidden" :name="`lines[${index}][item_description]`" :value="line.item_description">
                                                    <span x-text="line.item_description"></span>
                                                </span>
                                            </template>
                                            <template x-if="formData.doc_type === 'dssService'">
                                                <input type="text" :name="`lines[${index}][item_description]`" x-model="line.item_description" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" placeholder="Service description..." required>
                                            </template>
                                        </td>

                                        <!-- G/L Account (Service Doc) -->
                                        <template x-if="formData.doc_type === 'dssService'">
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 min-w-[180px]">
                                                <input type="hidden" :name="`lines[${index}][account_code]`" :value="line.account_code">
                                                <input type="hidden" :name="`lines[${index}][account_name]`" :value="line.account_name">
                                                <div>
                                                    <span x-text="line.account_code" class="font-mono font-semibold text-indigo-600"></span>
                                                    <div x-text="line.account_name" class="text-xs text-gray-500"></div>
                                                </div>
                                            </td>
                                        </template>

                                        <!-- Item UoM & Qty -->
                                        <template x-if="formData.doc_type === 'dssItem'">
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <select :name="`lines[${index}][uom_code]`" x-model="line.uom_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                                    <option value="">Manual / Blank</option>
                                                    @foreach($uoms as $uom)
                                                        <option value="{{ $uom->code }}">{{ $uom->code }} ({{ $uom->name }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </template>
                                        <template x-if="formData.doc_type === 'dssItem'">
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <input type="number" :name="`lines[${index}][quantity]`" x-model="line.quantity" min="0.01" step="0.01" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" required>
                                            </td>
                                        </template>

                                        <!-- Dimensions / Cost Centers -->
                                        @foreach($dimensions as $dim)
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @php
                                                    $costingField = $dim->dimension_code == 1 ? 'costing_code' : "costing_code{$dim->dimension_code}";
                                                    $dimCostCenters = $costCenters->where('dimension_code', $dim->dimension_code);
                                                @endphp
                                                <select :name="`lines[${index}][{{ $costingField }}]`" x-model="line.{{ $costingField }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                                    <option value="">None</option>
                                                    @foreach($dimCostCenters as $cc)
                                                        <option value="{{ $cc->center_code }}">{{ $cc->center_code }} - {{ $cc->center_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endforeach

                                        <!-- Price / Total Amount -->
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <input type="number" :name="`lines[${index}][price]`" x-model="line.price" min="0" step="0.01" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" placeholder="0.00" required>
                                        </td>

                                        <!-- Actions -->
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                            <button type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="formData.lines.length === 0">
                                    <td colspan="{{ 5 + count($dimensions) }}" class="py-8 text-center text-sm text-gray-500">
                                        No lines added yet. Click <strong class="text-indigo-600" x-text="formData.doc_type === 'dssItem' ? 'Add Item' : 'Add Account'"></strong> above to add document lines.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    <input type="hidden" name="instant_sync" value="0">
                    <input id="instant_sync" name="instant_sync" value="1" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="instant_sync" class="text-sm font-medium text-gray-700 cursor-pointer select-none">Instant Sync to SAP</label>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('purchase-request.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-500 transition-all">Save Purchase Request</button>
                </div>
            </div>
        </form>

        <x-vendor-modal />
        
        <!-- Item Selection Modal -->
        <div x-show="isItemModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="isItemModalOpen = false"></div>
                <div class="relative z-50 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Select Item</h3>
                        <button type="button" @click="isItemModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900 mb-1">Item</label>
                            <select x-model="selectedItemCode" @change="onItemChange" class="block w-full rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                <option value="">Select an Item</option>
                                <template x-for="item in availableItems" :key="item.ItemCode">
                                    <option :value="item.ItemCode" x-text="item.ItemCode + ' - ' + item.ItemName"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="isLoadingItems" class="py-4 text-center">
                            <svg class="animate-spin h-6 w-6 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-500">Loading items from local database...</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="isItemModalOpen = false" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="addItemLine" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add Item</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Selection Modal (Service Doc Type) -->
        <div x-show="isAccountModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="isAccountModalOpen = false"></div>
                <div class="relative z-50 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Select G/L Account</h3>
                        <button type="button" @click="isAccountModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900 mb-1">G/L Account</label>
                            <select x-model="selectedAccountCode" @change="onAccountChange" class="block w-full rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                <option value="">Select a G/L Account</option>
                                <template x-for="acc in availableAccounts" :key="acc.Code">
                                    <option :value="acc.Code" x-text="acc.FormatCode + ' - ' + acc.Name"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="isLoadingAccounts" class="py-4 text-center">
                            <svg class="animate-spin h-6 w-6 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-500">Loading accounts from local database...</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="isAccountModalOpen = false" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                        <button type="button" @click="addAccountLine" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add Account</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            const today = new Date().toISOString().split('T')[0];
            
            Alpine.data('purchaseRequestForm', () => ({
                formData: {
                    doc_type: 'dssItem',
                    document_date: today,
                    valid_until: today,
                    posting_date: today,
                    required_date: today,
                    vendor: '',
                    whs_code: '',
                    tax_code: '',
                    lines: []
                },
                vendorName: '',
                isItemModalOpen: false,
                isAccountModalOpen: false,
                availableItems: [],
                availableAccounts: [],
                isLoadingItems: false,
                isLoadingAccounts: false,
                selectedItemCode: '',
                selectedAccountCode: '',
                newItem: {
                    item_code: '',
                    item_description: '',
                    quantity: 1,
                    price: 0,
                    uom_code: '',
                    costing_code: '',
                    costing_code2: '',
                    costing_code3: '',
                    costing_code4: '',
                    costing_code5: ''
                },
                newAccount: {
                    account_code: '',
                    account_name: '',
                    item_description: '',
                    price: 0,
                    costing_code: '',
                    costing_code2: '',
                    costing_code3: '',
                    costing_code4: '',
                    costing_code5: ''
                },

                handleVendorSelected(detail) {
                    if (detail && detail.CardCode) {
                        this.formData.vendor = detail.CardCode;
                        this.vendorName = (detail.CardName || '') + ' (' + detail.CardCode + ')';
                    }
                },

                openVendorModal() {
                    window.dispatchEvent(new CustomEvent('open-vendor-modal'));
                },

                onDocTypeChange() {
                    if (this.formData.lines.length > 0) {
                        if (confirm('Changing document type will clear existing lines. Proceed?')) {
                            this.formData.lines = [];
                        }
                    }
                },

                openItemModal() {
                    this.newItem = { item_code: '', item_description: '', quantity: 1, price: 0, uom_code: '', costing_code: '', costing_code2: '', costing_code3: '', costing_code4: '', costing_code5: '' };
                    this.selectedItemCode = '';
                    this.isItemModalOpen = true;
                    if (this.availableItems.length === 0) {
                        this.fetchItems();
                    }
                },

                openAccountModal() {
                    this.newAccount = { account_code: '', account_name: '', item_description: '', price: 0, costing_code: '', costing_code2: '', costing_code3: '', costing_code4: '', costing_code5: '' };
                    this.selectedAccountCode = '';
                    this.isAccountModalOpen = true;
                    if (this.availableAccounts.length === 0) {
                        this.fetchAccounts();
                    }
                },

                fetchItems() {
                    this.isLoadingItems = true;
                    fetch('{{ route("api.items") }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.availableItems = data.data;
                            }
                        })
                        .finally(() => {
                            this.isLoadingItems = false;
                        });
                },

                fetchAccounts() {
                    this.isLoadingAccounts = true;
                    fetch('{{ route("api.accounts") }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.availableAccounts = data.data;
                            }
                        })
                        .finally(() => {
                            this.isLoadingAccounts = false;
                        });
                },

                onItemChange() {
                    const item = this.availableItems.find(i => i.ItemCode === this.selectedItemCode);
                    if (item) {
                        this.newItem.item_code = item.ItemCode;
                        this.newItem.item_description = item.ItemName;
                        this.newItem.uom_code = item.ResolvedUom || '';
                    } else {
                        this.newItem.item_code = '';
                        this.newItem.item_description = '';
                        this.newItem.uom_code = '';
                    }
                },

                onAccountChange() {
                    const acc = this.availableAccounts.find(a => a.Code === this.selectedAccountCode);
                    if (acc) {
                        this.newAccount.account_code = acc.Code;
                        this.newAccount.account_name = acc.Name;
                    } else {
                        this.newAccount.account_code = '';
                        this.newAccount.account_name = '';
                    }
                },

                addItemLine() {
                    if (this.newItem.item_code) {
                        this.formData.lines.push({...this.newItem});
                        this.isItemModalOpen = false;
                    } else {
                        alert('Item Code is required');
                    }
                },

                addAccountLine() {
                    if (this.newAccount.account_code) {
                        this.formData.lines.push({...this.newAccount});
                        this.isAccountModalOpen = false;
                    } else {
                        alert('G/L Account Code is required');
                    }
                },

                removeLine(index) {
                    this.formData.lines.splice(index, 1);
                },

                submitForm(e) {
                    if (this.formData.lines.length === 0) {
                        alert('Please add at least one line item or account.');
                        return;
                    }
                    e.target.submit();
                }
            }))
        })
    </script>
</x-app-layout>
