<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="approvalTemplateForm()">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900" x-text="isEdit ? 'Edit Approval Template' : 'Create Approval Template'"></h1>
                <p class="mt-1 text-sm text-gray-500">Define originator users, ordered stage flows, and optional condition terms.</p>
            </div>
            <a href="{{ route('approvals.templates.index') }}" class="rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to List</a>
        </div>

        <form method="POST" :action="isEdit ? '/approvals/templates/' + templateId : '{{ route("approvals.templates.store") }}'">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- General Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 mb-4 border-b pb-2">Template Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Template Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" required class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Transaction <span class="text-red-500">*</span></label>
                        <select name="target_document" x-model="targetDocument" required class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="PurchaseRequisition">Purchase Requisition</option>
                            <option value="PurchaseQuotation">Purchase Quotation</option>
                            <option value="PurchaseOrder">Purchase Order</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" x-model="description" placeholder="Optional template description..." class="w-full rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Originator Users</label>
                        <p class="text-xs text-gray-500 mb-2">Leave blank to apply to all users.</p>
                        <div class="max-h-36 overflow-y-auto border rounded-md p-3 space-y-2 bg-gray-50">
                            @foreach($users as $u)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="originator_user_ids[]" value="{{ $u->uid7 }}" x-model="originatorUserIds" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-gray-800">{{ $u->name }} ({{ $u->username }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center space-x-2 mt-6">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <span class="text-sm font-semibold text-gray-900">Active Template</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ordered Stages Section -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between border-b pb-3 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Stage Sequence Flow</h3>
                        <p class="text-xs text-gray-500">Document progresses sequentially from Stage 1 to the final stage.</p>
                    </div>
                    <button type="button" @click="addStage()" class="inline-flex items-center text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100">+ Add Stage Step</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(selectedStageId, index) in selectedStages" :key="index">
                        <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-md border border-gray-200">
                            <span class="text-sm font-bold text-gray-500 w-8" x-text="'Step ' + (index + 1)"></span>
                            
                            <select :name="'stages['+index+']'" x-model="selectedStages[index]" required class="flex-1 rounded-md border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Select Stage --</option>
                                @foreach($stages as $stg)
                                    <option value="{{ $stg->id }}">{{ $stg->name }} (Approvals: {{ $stg->min_approvals }}, Rejections: {{ $stg->min_rejections }})</option>
                                @endforeach
                            </select>

                            <div class="flex items-center gap-1">
                                <button type="button" @click="moveStageUp(index)" :disabled="index === 0" class="p-1 text-gray-500 hover:text-indigo-600 disabled:opacity-30">▲</button>
                                <button type="button" @click="moveStageDown(index)" :disabled="index === selectedStages.length - 1" class="p-1 text-gray-500 hover:text-indigo-600 disabled:opacity-30">▼</button>
                                <button type="button" @click="removeStage(index)" class="p-1 text-red-500 hover:text-red-700 text-xs font-bold ml-2">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Approval Terms / Conditions Section (Horizontal Table Layout) -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Approval Terms & Conditions</h3>
                
                <div class="flex items-center gap-6 mb-4 border-b pb-4">
                    <label class="flex items-center text-sm font-medium text-gray-900">
                        <input type="radio" name="terms_type" value="always" x-model="termsType" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2">Always Trigger Approval</span>
                    </label>
                    <label class="flex items-center text-sm font-medium text-gray-900">
                        <input type="radio" name="terms_type" value="conditional" x-model="termsType" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2">Conditional Terms</span>
                    </label>
                </div>

                <div x-show="termsType === 'conditional'">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs text-gray-500">Define terms when this template triggers. Minimum 1 term required.</p>
                        <button type="button" @click="addTerm()" class="inline-flex items-center text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100">+ Add Term Row</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[180px]">Target Level</th>
                                    <th scope="col" class="py-3 px-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[200px]">Field Name</th>
                                    <th scope="col" class="py-3 px-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[120px]">Operator</th>
                                    <th scope="col" class="py-3 px-3 text-left text-xs font-semibold text-gray-700 uppercase min-w-[200px]">Target Value</th>
                                    <th scope="col" class="py-3 px-3 text-center text-xs font-semibold text-gray-700 uppercase w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <template x-for="(term, index) in terms" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-2">
                                            <select :name="'terms['+index+'][target_level]'" x-model="term.target_level" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                                <option value="header">Header Level</option>
                                                <option value="detail">Detail Line Level (Total Sum)</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" :name="'terms['+index+'][field_name]'" x-model="term.field_name" placeholder="Field (e.g. price, urgency_level)" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 font-mono">
                                        </td>
                                        <td class="p-2">
                                            <select :name="'terms['+index+'][operator]'" x-model="term.operator" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500 font-bold">
                                                <option value="=">=</option>
                                                <option value=">">&gt;</option>
                                                <option value=">=">&gt;=</option>
                                                <option value="<">&lt;</option>
                                                <option value="<=">&lt;=</option>
                                                <option value="!=">!=</option>
                                                <option value="contains">contains</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" :name="'terms['+index+'][value]'" x-model="term.value" placeholder="Target Value (e.g. 500000, high)" class="w-full rounded-md border-gray-300 text-xs focus:ring-indigo-500">
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeTerm(index)" :disabled="terms.length <= 1" class="text-red-500 hover:text-red-700 text-xs font-bold disabled:opacity-30">Trash</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save Template</button>
            </div>
        </form>
    </div>

    <script>
        function approvalTemplateForm() {
            const tmpl = @json($template ?? null);
            return {
                isEdit: !!tmpl,
                templateId: tmpl ? tmpl.id : null,
                name: tmpl ? tmpl.name : '',
                description: tmpl ? (tmpl.description || '') : '',
                targetDocument: tmpl ? tmpl.target_document : 'PurchaseRequisition',
                isActive: tmpl ? tmpl.is_active : true,
                originatorUserIds: tmpl ? (tmpl.originator_user_ids || []) : [],
                termsType: tmpl ? tmpl.terms_type : 'always',
                selectedStages: tmpl && tmpl.stages ? tmpl.stages.map(s => s.approval_stage_id) : [1],
                terms: tmpl && tmpl.terms && tmpl.terms.length > 0 ? tmpl.terms : [
                    { target_level: 'header', field_name: 'price', operator: '>', value: '0' }
                ],

                addStage() {
                    this.selectedStages.push('');
                },
                removeStage(index) {
                    if (this.selectedStages.length > 1) {
                        this.selectedStages.splice(index, 1);
                    }
                },
                moveStageUp(index) {
                    if (index > 0) {
                        const temp = this.selectedStages[index - 1];
                        this.selectedStages[index - 1] = this.selectedStages[index];
                        this.selectedStages[index] = temp;
                    }
                },
                moveStageDown(index) {
                    if (index < this.selectedStages.length - 1) {
                        const temp = this.selectedStages[index + 1];
                        this.selectedStages[index + 1] = this.selectedStages[index];
                        this.selectedStages[index] = temp;
                    }
                },

                addTerm() {
                    this.terms.push({ target_level: 'header', field_name: '', operator: '=', value: '' });
                },
                removeTerm(index) {
                    if (this.terms.length > 1) {
                        this.terms.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-app-layout>
