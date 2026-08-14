<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseRequisitionForm()">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Requisition</h1>
                <p class="mt-1 text-sm text-gray-500">Internal Web App Purchase Requisition document with approval workflow.</p>
            </div>
            <div>
                <a href="{{ route('purchase-request.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
            </div>
        </div>

        <form method="POST" action="{{ route('purchase-request.store') }}">
            @csrf

            <!-- Header Details -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Header Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Requisition Type</label>
                        <select name="doc_type" x-model="docType" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="dssItem">Item Requisition</option>
                            <option value="dssService">Service Requisition</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urgency Level <span class="text-red-500">*</span></label>
                        <select name="urgency_level" x-model="urgencyLevel" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-600">
                            <option value="low">Low Urgency</option>
                            <option value="normal">Normal Urgency</option>
                            <option value="high">High Urgency</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Code <span class="text-red-500">*</span></label>
                        <select name="tax_code" x-model="taxCode" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($taxes as $t)
                                <option value="{{ $t->code }}">{{ $t->code }} - {{ $t->name }} ({{ number_format($t->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Posting Date <span class="text-red-500">*</span></label>
                        <input type="date" name="posting_date" x-model="postingDate" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Required / Delivery Date <span class="text-red-500">*</span></label>
                        <input type="date" name="delivery_date" x-model="deliveryDate" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Date <span class="text-red-500">*</span></label>
                        <input type="date" name="document_date" x-model="documentDate" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comments / Remarks</label>
                        <input type="text" name="comments" x-model="comments" placeholder="Optional remarks for this Purchase Requisition..." class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Lines Details Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Line Requisition Items</h3>
                    <button type="button" @click="addLine()" class="inline-flex items-center text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100">+ Add Row</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 pl-4 pr-2 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <template x-if="docType === 'dssService'">
                                    <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[200px]">G/L Account</th>
                                </template>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[180px]">Item Code</th>
                                </template>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[220px]">Description</th>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-24">Qty</th>
                                </template>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-28">Price</th>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[150px]">Warehouse</th>
                                </template>
                                <template x-if="docType === 'dssItem'">
                                    <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-24">On-Hand Stock</th>
                                </template>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[140px]">Cost Center</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td class="py-3 pl-4 pr-2 text-xs font-mono text-gray-500" x-text="index + 1"></td>
                                    
                                    <template x-if="docType === 'dssService'">
                                        <td class="px-2 py-2">
                                            <select :name="'lines['+index+'][account_code]'" x-model="line.account_code" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                                <option value="">-- Select Account --</option>
                                                @foreach($chartOfAccounts as $acc)
                                                    <option value="{{ $acc->code }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </template>

                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <input type="text" :name="'lines['+index+'][item_code]'" x-model="line.item_code" @change="fetchStock(index)" placeholder="Item Code..." class="w-full rounded-md border-gray-300 text-xs font-mono focus:ring-indigo-500">
                                        </td>
                                    </template>

                                    <td class="px-2 py-2">
                                        <input type="text" :name="'lines['+index+'][item_description]'" x-model="line.item_description" placeholder="Description..." class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                    </td>

                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <input type="number" step="any" :name="'lines['+index+'][quantity]'" x-model="line.quantity" class="w-full rounded-md border-gray-300 text-xs text-right focus:ring-indigo-500">
                                        </td>
                                    </template>

                                    <td class="px-2 py-2">
                                        <input type="number" step="any" :name="'lines['+index+'][price]'" x-model="line.price" class="w-full rounded-md border-gray-300 text-xs text-right font-mono focus:ring-indigo-500">
                                    </td>

                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2">
                                            <select :name="'lines['+index+'][whs_code]'" x-model="line.whs_code" @change="onWhsChange(index)" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                                <option value="">Select Warehouse</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->whs_code }}">{{ $wh->whs_code }} - {{ $wh->whs_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </template>

                                    <!-- Read-only On Hand Stock Display -->
                                    <template x-if="docType === 'dssItem'">
                                        <td class="px-2 py-2 text-center bg-gray-50">
                                            <span class="text-xs font-mono font-bold text-indigo-700" x-text="line.on_hand_qty || '0.00'"></span>
                                            <input type="hidden" :name="'lines['+index+'][on_hand_qty]'" x-model="line.on_hand_qty">
                                        </td>
                                    </template>

                                    <td class="px-2 py-2">
                                        <select :name="'lines['+index+'][costing_code]'" x-model="line.costing_code" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                            <option value="">None</option>
                                            @foreach($costCenters as $cc)
                                                <option value="{{ $cc->center_code }}">{{ $cc->center_code }} - {{ $cc->center_name }}</option>
                                            @endforeach
                                        </select>
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

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Submit Purchase Requisition</button>
            </div>
        </form>
    </div>

    <script>
        function purchaseRequisitionForm() {
            const prefilled = @json($prefilledData ?? null);
            return {
                docType: prefilled ? prefilled.doc_type : 'dssItem',
                urgencyLevel: prefilled ? prefilled.urgency_level : 'normal',
                taxCode: prefilled ? prefilled.tax_code : 'PPN11',
                postingDate: prefilled ? prefilled.posting_date : new Date().toISOString().split('T')[0],
                deliveryDate: prefilled ? prefilled.delivery_date : new Date(Date.now() + 3*86400000).toISOString().split('T')[0],
                documentDate: prefilled ? prefilled.document_date : new Date().toISOString().split('T')[0],
                comments: prefilled ? prefilled.comments : '',
                lines: prefilled && prefilled.lines ? prefilled.lines : [
                    { item_code: '', item_description: '', account_code: '', account_name: '', quantity: 1, price: 0, uom_code: '', whs_code: '', on_hand_qty: 0, costing_code: '' }
                ],

                addLine() {
                    this.lines.push({ item_code: '', item_description: '', account_code: '', account_name: '', quantity: 1, price: 0, uom_code: '', whs_code: '', on_hand_qty: 0, costing_code: '' });
                },
                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                },
                async fetchStock(index) {
                    const itemCode = this.lines[index].item_code;
                    if (!itemCode) return;
                    try {
                        const res = await fetch('/api/items/' + itemCode + '/stock');
                        const data = await res.json();
                        if (data.success) {
                            this.lines[index].stockMap = data.data;
                            this.onWhsChange(index);
                        }
                    } catch(e) {}
                },
                onWhsChange(index) {
                    const whsCode = this.lines[index].whs_code;
                    const stockMap = this.lines[index].stockMap || [];
                    const found = stockMap.find(s => s.whs_code === whsCode);
                    this.lines[index].on_hand_qty = found ? found.on_hand_qty : 0;
                }
            }
        }
    </script>
</x-app-layout>
