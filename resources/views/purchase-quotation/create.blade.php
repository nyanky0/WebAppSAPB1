<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="purchaseQuotationForm()">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Purchase Quotation</h1>
                <p class="mt-1 text-sm text-gray-500">Create a Purchase Quotation or copy from active Purchase Requisitions.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Copy From Button -->
                <button type="button" @click="openCopyModal()" :disabled="!cardCode" class="inline-flex items-center rounded-md bg-indigo-50 px-3.5 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 disabled:opacity-40 disabled:cursor-not-allowed">
                    Copy From Purchase Requisition
                </button>
                <a href="{{ route('purchase-quotation.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
            </div>
        </div>

        <form method="POST" action="{{ route('purchase-quotation.store') }}">
            @csrf
            <input type="hidden" name="base_requisition_id" x-model="baseRequisitionId">

            <!-- Header Card -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Header Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor (Supplier) <span class="text-red-500">*</span></label>
                        <select name="card_code" x-model="cardCode" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->bp_code }}">{{ $v->bp_code }} - {{ $v->name }}</option>
                            @endforeach
                        </select>
                        <span x-show="!cardCode" class="text-xs text-amber-600 font-medium block mt-1">Select a Vendor to enable "Copy From Purchase Requisition" button.</span>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Date <span class="text-red-500">*</span></label>
                        <input type="date" name="document_date" x-model="documentDate" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due / Expiry Date <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" x-model="dueDate" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Comments / Remarks</label>
                        <input type="text" name="comments" x-model="comments" placeholder="Optional Purchase Quotation remarks..." class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Lines Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Quotation Line Items</h3>
                    <button type="button" @click="addLine()" class="inline-flex items-center text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100">+ Add Row</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 pl-4 pr-2 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[160px]">Item Code</th>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[200px]">Description</th>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-24">Req. Qty</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-28">Req. Date</th>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-28">Quoted Qty</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-28">Quoted Date</th>
                                <th class="px-2 py-3 text-right text-xs font-semibold text-gray-700 uppercase w-28">Unit Price</th>
                                <th class="px-2 py-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[140px]">Warehouse</th>
                                <th class="px-2 py-3 text-center text-xs font-semibold text-gray-700 uppercase w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td class="py-3 pl-4 pr-2 text-xs font-mono text-gray-500" x-text="index + 1"></td>
                                    
                                    <td class="px-2 py-2">
                                        <input type="text" :name="'lines['+index+'][item_code]'" x-model="line.item_code" placeholder="Item Code..." class="w-full rounded-md border-gray-300 text-xs font-mono focus:ring-indigo-500">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="text" :name="'lines['+index+'][item_description]'" x-model="line.item_description" placeholder="Description..." class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="number" step="any" :name="'lines['+index+'][required_qty]'" x-model="line.required_qty" class="w-full rounded-md border-gray-300 text-xs text-right focus:ring-indigo-500 bg-gray-50">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="date" :name="'lines['+index+'][required_date]'" x-model="line.required_date" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 bg-gray-50">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="number" step="any" :name="'lines['+index+'][quoted_qty]'" x-model="line.quoted_qty" class="w-full rounded-md border-gray-300 text-xs text-right font-bold focus:ring-indigo-500">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="date" :name="'lines['+index+'][quoted_date]'" x-model="line.quoted_date" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 font-semibold">
                                    </td>

                                    <td class="px-2 py-2">
                                        <input type="number" step="any" :name="'lines['+index+'][unit_price]'" x-model="line.unit_price" class="w-full rounded-md border-gray-300 text-xs text-right font-mono focus:ring-indigo-500">
                                    </td>

                                    <td class="px-2 py-2">
                                        <select :name="'lines['+index+'][whs_code]'" x-model="line.whs_code" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                            <option value="">Select Warehouse</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->whs_code }}">{{ $wh->whs_code }} - {{ $wh->whs_name }}</option>
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
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Submit Purchase Quotation</button>
            </div>
        </form>

        <!-- Copy From Requisition Modal -->
        <div x-show="showCopyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCopyModal = false"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Copy From Active Purchase Requisitions</h3>
                    <p class="text-xs text-gray-500 mb-4">Select an open or approved Purchase Requisition to copy lines into this Quotation.</p>

                    <div class="max-h-60 overflow-y-auto border rounded-md divide-y divide-gray-200">
                        <template x-for="pr in requisitionsList" :key="pr.id">
                            <div class="p-3 hover:bg-gray-50 flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-indigo-600 text-sm" x-text="'PR #' + (pr.doc_num || pr.id)"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="'Date: ' + pr.document_date"></span>
                                    <span class="text-xs text-gray-600 block" x-text="pr.lines.length + ' item lines'"></span>
                                </div>
                                <button type="button" @click="copyFromRequisition(pr)" class="rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Select & Copy</button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="showCopyModal = false" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseQuotationForm() {
            const prefilled = @json($prefilledData ?? null);
            return {
                cardCode: prefilled ? prefilled.card_code : '',
                urgencyLevel: prefilled ? prefilled.urgency_level : 'normal',
                taxCode: prefilled ? prefilled.tax_code : 'PPN11',
                documentDate: prefilled ? prefilled.document_date : new Date().toISOString().split('T')[0],
                dueDate: prefilled ? prefilled.due_date : new Date(Date.now() + 7*86400000).toISOString().split('T')[0],
                comments: prefilled ? prefilled.comments : '',
                baseRequisitionId: prefilled ? prefilled.base_requisition_id : null,
                lines: prefilled && prefilled.lines ? prefilled.lines : [
                    { item_code: '', item_description: '', required_qty: 1, required_date: new Date().toISOString().split('T')[0], quoted_qty: 1, quoted_date: new Date().toISOString().split('T')[0], unit_price: 0, whs_code: '' }
                ],
                showCopyModal: false,
                requisitionsList: [],

                addLine() {
                    this.lines.push({ item_code: '', item_description: '', required_qty: 1, required_date: new Date().toISOString().split('T')[0], quoted_qty: 1, quoted_date: new Date().toISOString().split('T')[0], unit_price: 0, whs_code: '' });
                },
                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                },
                async openCopyModal() {
                    if (!this.cardCode) return;
                    try {
                        const res = await fetch('/api/requisitions/vendor-eligible?card_code=' + this.cardCode);
                        const data = await res.json();
                        if (data.success) {
                            this.requisitionsList = data.data;
                            this.showCopyModal = true;
                        }
                    } catch(e) {}
                },
                copyFromRequisition(pr) {
                    this.baseRequisitionId = pr.id;
                    this.comments = 'Copied from Purchase Requisition #' + (pr.doc_num || pr.id);
                    this.lines = pr.lines.map(l => ({
                        item_code: l.item_code,
                        item_description: l.item_description,
                        required_qty: l.quantity,
                        required_date: l.required_date || this.documentDate,
                        quoted_qty: l.quantity,
                        quoted_date: this.documentDate,
                        unit_price: l.price,
                        whs_code: l.whs_code,
                        on_hand_qty: l.on_hand_qty || 0,
                        base_requisition_line_id: l.id
                    }));
                    this.showCopyModal = false;
                }
            }
        }
    </script>
</x-app-layout>
