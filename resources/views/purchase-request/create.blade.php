<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseRequestForm()">
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

                        <!-- Vendor Selection -->
                        <div class="sm:col-span-3">
                            <label for="vendor" class="block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                            <div class="mt-2 flex space-x-2">
                                <input type="text" x-model="vendorName" readonly class="block w-full rounded-md border-0 py-1.5 bg-gray-50 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6" placeholder="Select a vendor...">
                                <input type="hidden" name="vendor" x-model="formData.vendor" required>
                                <button type="button" @click="openVendorModal" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Select</button>
                            </div>
                        </div>

                        <!-- Tax Code -->
                        <div class="sm:col-span-3">
                            <label for="tax_code" class="block text-sm font-medium leading-6 text-gray-900">Tax Code</label>
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

            <!-- Items Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
                <div class="px-4 py-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Document Lines</h2>
                        <button type="button" @click="openItemModal" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-indigo-600 shadow-sm ring-1 ring-inset ring-indigo-300 hover:bg-indigo-50">
                            Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Item Code</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-32">Quantity</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-48">Unit Price</th>
                                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(line, index) in formData.lines" :key="index">
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900">
                                            <input type="hidden" :name="`lines[${index}][item_code]`" :value="line.item_code">
                                            <span x-text="line.item_code"></span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <input type="hidden" :name="`lines[${index}][item_description]`" :value="line.item_description">
                                            <span x-text="line.item_description"></span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <input type="number" :name="`lines[${index}][quantity]`" x-model="line.quantity" min="0.01" step="0.01" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <input type="number" :name="`lines[${index}][price]`" x-model="line.price" min="0" step="0.01" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                            <button type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-900">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="formData.lines.length === 0">
                                    <td colspan="5" class="py-4 text-center text-sm text-gray-500">No items added yet.</td>
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
        <div x-show="isItemModalOpen" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <!-- Background overlay -->
                <div x-show="isItemModalOpen" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                     @click="isItemModalOpen = false"></div>

                <!-- Modal Panel -->
                <div x-show="isItemModalOpen" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative z-50 inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full p-6 border border-gray-100">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Select Item</h3>
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
                        <button type="button" @click="isItemModalOpen = false" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="button" @click="addLine" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">Add Item</button>
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
                    document_date: today,
                    valid_until: today,
                    posting_date: today,
                    required_date: today,
                    vendor: '',
                    tax_code: '',
                    lines: []
                },
                vendorName: '',
                isItemModalOpen: false,
                availableItems: [],
                isLoadingItems: false,
                selectedItemCode: '',
                newItem: {
                    item_code: '',
                    item_description: '',
                    quantity: 1,
                    price: 0
                },

                openVendorModal() {
                    window.dispatchEvent(new CustomEvent('open-vendor-modal'));
                },

                openItemModal() {
                    this.newItem = { item_code: '', item_description: '', quantity: 1, price: 0 };
                    this.selectedItemCode = '';
                    this.isItemModalOpen = true;
                    if (this.availableItems.length === 0) {
                        this.fetchItems();
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

                onItemChange() {
                    const item = this.availableItems.find(i => i.ItemCode === this.selectedItemCode);
                    if (item) {
                        this.newItem.item_code = item.ItemCode;
                        this.newItem.item_description = item.ItemName;
                    } else {
                        this.newItem.item_code = '';
                        this.newItem.item_description = '';
                    }
                },

                addLine() {
                    if (this.newItem.item_code) {
                        this.formData.lines.push({...this.newItem});
                        this.isItemModalOpen = false;
                    } else {
                        alert('Item Code is required');
                    }
                },

                removeLine(index) {
                    this.formData.lines.splice(index, 1);
                },

                submitForm(e) {
                    if (this.formData.lines.length === 0) {
                        alert('Please add at least one item.');
                        return;
                    }
                    e.target.submit();
                }
            }))
        })

        // Listen for vendor selection from vendor-modal
        window.addEventListener('vendor-selected', (e) => {
            const form = document.querySelector('[x-data="purchaseRequestForm()"]').__x.$data;
            form.formData.vendor = e.detail.CardCode;
            form.vendorName = e.detail.CardName + ' (' + e.detail.CardCode + ')';
        });
    </script>
</x-app-layout>
