<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseOrderForm()">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Order</h1>
                @if($basePr)
                    <p class="mt-1 text-sm text-indigo-600 font-semibold flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Copied from Purchase Request #{{ $basePr->doc_num ?? $basePr->id }} (DocEntry: {{ $basePr->doc_entry }})
                    </p>
                @else
                    <p class="mt-1 text-sm text-gray-500">Create a new SAP Business One Purchase Order.</p>
                @endif
            </div>
            <div>
                <a href="{{ route('purchase-order.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
            </div>
        </div>

        <form method="POST" action="{{ route('purchase-order.store') }}" @submit="isSubmitting = true">
            @csrf
            
            <!-- Document Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Header Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                        <select name="doc_type" x-model="docType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="dssItem">Item Document</option>
                            <option value="dssService">Service Document</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor (Supplier) <span class="text-red-500">*</span></label>
                        <select name="card_code" x-model="cardCode" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->card_code }}">{{ $v->card_code }} - {{ $v->card_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Code <span class="text-red-500">*</span></label>
                        <select name="tax_code" x-model="taxCode" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach($taxes as $t)
                                <option value="{{ $t->code }}">{{ $t->code }} - {{ $t->name }} ({{ number_format($t->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Posting Date <span class="text-red-500">*</span></label>
                        <input type="date" name="posting_date" x-model="postingDate" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery / Due Date <span class="text-red-500">*</span></label>
                        <input type="date" name="delivery_date" x-model="deliveryDate" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Date <span class="text-red-500">*</span></label>
                        <input type="date" name="document_date" x-model="documentDate" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div x-show="docType === 'dssItem'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                        <select name="whs_code" x-model="whsCode" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Default Warehouse --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->whs_code }}">{{ $wh->whs_code }} - {{ $wh->whs_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comments / Remarks</label>
                        <input type="text" name="comments" x-model="comments" placeholder="Optional Purchase Order remarks..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Line Details Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Line Items / Services</h3>
                    <button type="button" @click="addLine()" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-md hover:bg-indigo-100">
                        + Add Row
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 pl-4 pr-2 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <template x-if="docType === 'dssService'">
                                    <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[200px]">G/L Account <span class="text-red-500">*</span></th>
                                </template>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[180px]">Item Code <span class="text-red-500">*</span></th>
                                </template>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[220px]">Description</th>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-28">Qty</th>
                                </template>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-32">Price / Total</th>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-28">UOM</th>
                                </template>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[160px]">Cost Center / Dim</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td class="py-3 pl-4 pr-2 text-xs font-mono text-gray-500" x-text="index + 1"></td>
                                    
                                    <!-- Service Account Selection -->
                                    <template x-if="docType === 'dssService'">
                                        <td class="px-2 py-2">
                                            <select :name="'lines['+index+'][account_code]'" x-model="line.account_code" @change="onAccountChange(index)" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">-- Select Account --</option>
                                                @foreach($chartOfAccounts as $acc)
                                                    <option value="{{ $acc->code }}" data-name="{{ $acc->name }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" :name="'lines['+index+'][account_name]'" x-model="line.account_name">
                                        </td>
                                    </template>

                                    <!-- Item Selection -->
                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <input type="text" :name="'lines['+index+'][item_code]'" x-model="line.item_code" placeholder="Item Code..." class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                                        </td>
                                    </template>

                                    <td class="px-2 py-2">
                                        <input type="text" :name="'lines['+index+'][item_description]'" x-model="line.item_description" placeholder="Description..." class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                    </td>

                                    <!-- Quantity -->
                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <input type="number" step="any" :name="'lines['+index+'][quantity]'" x-model="line.quantity" class="w-full rounded-md border-gray-300 text-xs text-right focus:ring-indigo-500 focus:border-indigo-500">
                                        </td>
                                    </template>

                                    <!-- Price -->
                                    <td class="px-2 py-2">
                                        <input type="number" step="any" :name="'lines['+index+'][price]'" x-model="line.price" class="w-full rounded-md border-gray-300 text-xs text-right focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                                    </td>

                                    <!-- UOM -->
                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <select :name="'lines['+index+'][uom_code]'" x-model="line.uom_code" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Default</option>
                                                @foreach($uoms as $u)
                                                    <option value="{{ $u->code }}">{{ $u->code }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </template>

                                    <!-- Cost Center -->
                                    <td class="px-2 py-2">
                                        <select :name="'lines['+index+'][costing_code]'" x-model="line.costing_code" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">None</option>
                                            @foreach($costCenters as $cc)
                                                <option value="{{ $cc->center_code }}">{{ $cc->center_code }} - {{ $cc->center_name }}</option>
                                            @endforeach
                                        </select>
                                        
                                        <!-- Hidden Base Document Linkage Inputs -->
                                        <input type="hidden" :name="'lines['+index+'][base_type]'" x-model="line.base_type">
                                        <input type="hidden" :name="'lines['+index+'][base_entry]'" x-model="line.base_entry">
                                        <input type="hidden" :name="'lines['+index+'][base_line]'" x-model="line.base_line">
                                    </td>

                                    <td class="px-2 py-2 text-center">
                                        <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700 text-xs font-semibold">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3">
                <button type="submit" name="instant_sync" value="0" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Save as Draft
                </button>
                <button type="submit" name="instant_sync" value="1" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Save & Sync to SAP
                </button>
            </div>
        </form>
    </div>

    <script>
        function purchaseOrderForm() {
            const prefilled = @json($prefilledData);
            return {
                docType: prefilled ? prefilled.doc_type : 'dssItem',
                cardCode: prefilled ? prefilled.card_code : '',
                taxCode: prefilled ? prefilled.tax_code : 'PPN11',
                whsCode: prefilled ? prefilled.whs_code : '',
                postingDate: prefilled ? prefilled.posting_date : new Date().toISOString().split('T')[0],
                deliveryDate: prefilled ? prefilled.delivery_date : new Date(Date.now() + 3*86400000).toISOString().split('T')[0],
                documentDate: prefilled ? prefilled.document_date : new Date().toISOString().split('T')[0],
                comments: prefilled ? ('Copied from PR #' + (prefilled.base_num || '')) : '',
                lines: prefilled && prefilled.lines ? prefilled.lines : [
                    { item_code: '', item_description: '', account_code: '', account_name: '', quantity: 1, price: 0, uom_code: '', costing_code: '', base_type: '', base_entry: '', base_line: '' }
                ],
                isSubmitting: false,

                addLine() {
                    this.lines.push({ item_code: '', item_description: '', account_code: '', account_name: '', quantity: 1, price: 0, uom_code: '', costing_code: '', base_type: '', base_entry: '', base_line: '' });
                },

                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                },

                onAccountChange(index) {
                    // Pre-fill account_name from select option data
                }
            }
        }
    </script>
</x-app-layout>
