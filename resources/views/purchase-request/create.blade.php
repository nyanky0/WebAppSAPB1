<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseRequestForm()">
        <!-- Toolbar -->
        <div class="flex items-center justify-between mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex space-x-2">
                <button type="button" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" title="Find Mode">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Find
                </button>
                <button type="button" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" title="Add Mode">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Add
                </button>
                <div class="h-6 border-l border-gray-300 mx-2"></div>
                <button type="button" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" title="Previous Record">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button type="button" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" title="Next Record">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Purchase Request</h1>
        </div>

        <form action="#" method="POST" class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <!-- Header Section -->
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Vendor</label>
                            <div class="w-2/3 flex space-x-2">
                                <input type="text" x-model="vendorCode" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-shadow hover:shadow-md" placeholder="CardCode">
                                <button type="button" @click="$dispatch('open-vendor-modal')" class="p-1.5 border border-gray-300 rounded-md bg-gray-50 hover:bg-white hover:shadow-md hover:border-indigo-300 transition-all transform hover:scale-105 active:scale-95"><svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Name</label>
                            <div class="w-2/3">
                                <input type="text" x-model="vendorName" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100" readonly placeholder="Vendor Name">
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Contact Person</label>
                            <div class="w-2/3">
                                <select x-model="selectedContactPerson" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Contact...</option>
                                    <template x-for="contact in contactPersons" :key="contact.InternalCode">
                                        <option :value="contact.InternalCode" x-text="contact.Name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">No.</label>
                            <div class="w-2/3 flex space-x-2">
                                <select x-model="selectedSeries" class="block w-1/3 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                    <template x-for="series in seriesList" :key="series.Series">
                                        <option :value="series.Series" x-text="series.Name"></option>
                                    </template>
                                </select>
                                <input type="text" class="block w-2/3 px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100" readonly placeholder="DocNum (Auto)">
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Posting Date</label>
                            <div class="w-2/3">
                                <input type="date" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Valid Until</label>
                            <div class="w-2/3">
                                <input type="date" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="flex items-center">
                            <label class="w-1/3 text-sm font-medium text-gray-700">Document Date</label>
                            <div class="w-2/3">
                                <input type="date" class="block w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lines Section (Matrix) -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item No.</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Description</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Whse Code</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Whse Name</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Code</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (LC)</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-gray-500" x-text="index + 1"></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-1">
                                        <input type="text" x-model="row.itemCode" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                        <button type="button" class="p-1 border border-gray-300 rounded bg-gray-100 hover:bg-gray-200"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
                                    </div>
                                </td>
                                <td class="px-3 py-2"><input type="text" x-model="row.itemName" class="block w-full px-2 py-1 text-sm border border-gray-300 rounded bg-gray-50" readonly></td>
                                <td class="px-3 py-2"><input type="number" x-model="row.quantity" class="block w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 text-right"></td>
                                <td class="px-3 py-2"><input type="text" x-model="row.uom" class="block w-16 px-2 py-1 text-sm border border-gray-300 rounded bg-gray-50" readonly></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-1">
                                        <input type="text" x-model="row.whsCode" class="block w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                        <button type="button" class="p-1 border border-gray-300 rounded bg-gray-100 hover:bg-gray-200"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
                                    </div>
                                </td>
                                <td class="px-3 py-2"><input type="text" x-model="row.whsName" class="block w-32 px-2 py-1 text-sm border border-gray-300 rounded bg-gray-50" readonly></td>
                                <td class="px-3 py-2"><input type="number" x-model="row.price" class="block w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 text-right"></td>
                                <td class="px-3 py-2"><input type="text" x-model="row.taxCode" class="block w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"></td>
                                <td class="px-3 py-2"><input type="text" :value="(row.quantity * row.price).toFixed(2)" class="block w-24 px-2 py-1 text-sm border border-gray-300 rounded bg-gray-50 text-right" readonly></td>
                                <td class="px-3 py-2 text-right">
                                    <button @click="removeRow(index)" type="button" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    <button @click="addRow()" type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 focus:outline-none">
                        + Add Row
                    </button>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <!-- Remarks -->
                    <div class="flex flex-col space-y-2">
                        <label class="text-sm font-medium text-gray-700">Remarks</label>
                        <textarea rows="4" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Before Discount</span>
                            <input type="text" class="block w-40 px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100 text-right font-semibold" readonly value="0.00">
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Tax</span>
                            <input type="text" class="block w-40 px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-gray-100 text-right font-semibold" readonly value="0.00">
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-gray-300">
                            <span class="text-base font-bold text-gray-900">Total Payment</span>
                            <input type="text" class="block w-40 px-3 py-1.5 text-base border border-gray-300 rounded-md bg-indigo-50 text-indigo-900 text-right font-bold" readonly value="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50/50 backdrop-blur-sm border-t border-gray-200 flex justify-start space-x-3">
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg shadow hover:bg-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add
                </button>
                <button type="button" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md hover:border-gray-400 transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
            </div>
        </form>
        <!-- Missing Config Modal -->
        <div x-show="missingConfig" 
             class="fixed z-[200] inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="missingConfig" 
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                     aria-hidden="true"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="missingConfig" 
                     class="inline-block align-middle bg-white/90 backdrop-blur-xl border border-white/50 rounded-2xl text-center overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-sm sm:w-full p-8">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-6">
                        <svg class="h-10 w-10 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Configuration Required</h3>
                    <p class="text-sm text-gray-600 mb-8">
                        The Period Indicator is not configured. Please go to the configuration page to fetch and save the active Period Indicator before creating a document.
                    </p>
                    <a href="{{ route('config.index') }}" class="inline-flex justify-center w-full rounded-lg border border-transparent shadow-md px-6 py-3 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:-translate-y-0.5 active:scale-95">
                        Go to Configuration
                    </a>
                </div>
            </div>
        </div>

        <!-- Reusable Vendor Modal Component -->
        <x-vendor-modal @vendor-selected="handleVendorSelected($event.detail)" />
    <script>
        function purchaseRequestForm() {
            return {
                vendorCode: '',
                vendorName: '',
                contactPersons: [],
                selectedContactPerson: '',
                seriesList: [],
                selectedSeries: '',
                missingConfig: false,
                
                init() {
                    this.fetchSeries();
                },
                
                fetchSeries() {
                    fetch('/api/purchase-request/series')
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.seriesList = data.data;
                                // Default to the first series, or find one where IsDefault === 'tYES' if SAP provides it
                                if (this.seriesList.length > 0) {
                                    this.selectedSeries = this.seriesList[0].Series;
                                }
                            } else if (data.missing_config) {
                                this.missingConfig = true;
                            } else {
                                window.dispatchEvent(new CustomEvent('flash-message', {
                                    detail: { type: 'error', message: 'Failed to fetch series: ' + data.message }
                                }));
                            }
                        })
                        .catch(err => console.error(err));
                },
                
                rows: [
                    { itemCode: '', itemName: '', quantity: 1, uom: '', whsCode: '', whsName: '', price: 0.0, taxCode: '' }
                ],
                
                addRow() {
                    this.rows.push({ itemCode: '', itemName: '', quantity: 1, uom: '', whsCode: '', whsName: '', price: 0.0, taxCode: '' });
                },
                
                removeRow(index) {
                    this.rows.splice(index, 1);
                    if (this.rows.length === 0) {
                        this.addRow();
                    }
                },
                
                handleVendorSelected(vendor) {
                    this.vendorCode = vendor.CardCode;
                    this.vendorName = vendor.CardName;
                    this.contactPersons = vendor.ContactEmployees || [];
                    
                    if (this.contactPersons.length === 0) {
                        this.selectedContactPerson = '';
                    } else if (this.contactPersons.length === 1) {
                        this.selectedContactPerson = this.contactPersons[0].InternalCode;
                    } else {
                        // More than one contact: try to find default, or pick first
                        // Default contact in SAP B1 is usually matched by vendor.ContactPerson (Name), but here we just take the first as default.
                        this.selectedContactPerson = this.contactPersons[0].InternalCode;
                    }
                }
            }
        }
    </script>
</x-app-layout>
