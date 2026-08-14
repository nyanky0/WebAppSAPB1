<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8" x-data="approvalStagesPage()">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Approval Stages</h1>
                <p class="mt-1 text-sm text-gray-500">Configure stage approver users, minimum approval votes, and rejection thresholds.</p>
            </div>
            <div>
                <button type="button" @click="openAddModal()" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    + Add Approval Stage
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Stage Name</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Req. Approvals</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Req. Rejections</th>
                        <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase">Approvers</th>
                        <th class="px-3 py-3.5 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($stages as $stage)
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 pl-6 pr-3 text-sm font-bold text-gray-900">{{ $stage->name }}</td>
                            <td class="px-3 py-4 text-sm text-gray-600">{{ $stage->description ?? '-' }}</td>
                            <td class="px-3 py-4 text-sm text-center font-mono font-semibold text-green-600">{{ $stage->min_approvals }}</td>
                            <td class="px-3 py-4 text-sm text-center font-mono font-semibold text-red-600">{{ $stage->min_rejections }}</td>
                            <td class="px-3 py-4 text-sm text-gray-900">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($stage->approvers() as $apprUser)
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">{{ $apprUser->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-center font-medium space-x-2">
                                <button type="button" @click="openEditModal({{ json_encode($stage) }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                <form action="{{ route('approvals.stages.destroy', $stage->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this stage?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-500">No approval stages found. Click "+ Add Approval Stage" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="relative inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full p-6 z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-4" x-text="editMode ? 'Edit Approval Stage' : 'Add Approval Stage'"></h3>
                    
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stage Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="stageName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" x-model="stageDesc" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Req. Approvals <span class="text-red-500">*</span></label>
                                    <input type="number" min="1" name="min_approvals" x-model="minApprovals" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Req. Rejections <span class="text-red-500">*</span></label>
                                    <input type="number" min="1" name="min_rejections" x-model="minRejections" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Approver Users <span class="text-red-500">*</span></label>
                                <div class="max-h-40 overflow-y-auto border rounded-md p-3 space-y-2 bg-gray-50">
                                    @foreach($users as $u)
                                        <label class="flex items-center text-sm">
                                            <input type="checkbox" name="approver_user_ids[]" value="{{ $u->uid7 }}" x-model="selectedUserIds" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-gray-800">{{ $u->name }} ({{ $u->username }})</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="closeModal()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save Stage</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function approvalStagesPage() {
            return {
                showModal: false,
                editMode: false,
                formAction: '{{ route("approvals.stages.store") }}',
                stageName: '',
                stageDesc: '',
                minApprovals: 1,
                minRejections: 1,
                selectedUserIds: [],

                openAddModal() {
                    this.editMode = false;
                    this.formAction = '{{ route("approvals.stages.store") }}';
                    this.stageName = '';
                    this.stageDesc = '';
                    this.minApprovals = 1;
                    this.minRejections = 1;
                    this.selectedUserIds = [];
                    this.showModal = true;
                },

                openEditModal(stage) {
                    this.editMode = true;
                    this.formAction = '/approvals/stages/' + stage.id;
                    this.stageName = stage.name;
                    this.stageDesc = stage.description || '';
                    this.minApprovals = stage.min_approvals;
                    this.minRejections = stage.min_rejections;
                    this.selectedUserIds = stage.approver_user_ids || [];
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                }
            }
        }
    </script>
</x-app-layout>
