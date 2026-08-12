<x-app-layout>
    <div class="py-6" x-data="roleModal()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Roles Management</h1>
                <button @click="openAddModal()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Role
                </button>
            </div>

            <div class="bg-white/60 backdrop-blur-xl border border-white/40 overflow-hidden shadow-xl sm:rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50 backdrop-blur-sm">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role Name</th>
                            <th scope="col" class="relative px-6 py-3.5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/40 divide-y divide-gray-200/50">
                        @foreach($roles as $role)
                            <tr class="hover:bg-white/80 hover:shadow-md transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $role->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <button @click="openEditModal({{ \Illuminate\Support\Js::from($role) }})" type="button" class="text-indigo-600 hover:text-indigo-900 font-semibold transition-colors">Edit</button>
                                    
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold transition-colors">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal" 
             class="fixed z-[100] inset-0 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true" 
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                
                <!-- Backdrop with Glassmorphism -->
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 z-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
                     aria-hidden="true" 
                     @click="closeModal()"></div>

                <!-- Modal Panel -->
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative z-50 w-full max-w-2xl bg-white/95 backdrop-blur-xl border border-white/40 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all my-8">
                    
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white/80 px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold text-gray-900" id="modal-title" x-text="editMode ? 'Edit Role' : 'Add Role'"></h3>
                                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="mt-4">
                                <label for="roleName" class="block text-sm font-medium text-gray-700">Role Name</label>
                                <input type="text" name="name" id="roleName" x-model="roleName" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white" required>
                            </div>

                            <div class="mt-6 border-t border-gray-200/80 pt-4">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Permissions & Access Control</h4>
                                <div class="space-y-4">
                                    
                                    <!-- Administrator Folder -->
                                    <div class="border border-gray-200/80 rounded-lg p-4 bg-gray-50/60">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="folder_admin" x-model="folderAdmin" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="folder_admin" class="ml-2 block text-sm font-semibold text-gray-900">Administrator Folder</label>
                                        </div>
                                        <div class="ml-6 space-y-2">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Config" id="perm_admin_config" x-model="perms.admin_config" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_admin_config" class="ml-2 block text-sm text-gray-700">Config</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Roles" id="perm_admin_roles" x-model="perms.admin_roles" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_admin_roles" class="ml-2 block text-sm text-gray-700">Roles</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Users" id="perm_admin_users" x-model="perms.admin_users" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_admin_users" class="ml-2 block text-sm text-gray-700">Users</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Logs" id="perm_admin_logs" x-model="perms.admin_logs" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_admin_logs" class="ml-2 block text-sm text-gray-700">System Logs</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SAP Master Data Folder -->
                                    <div class="border border-gray-200/80 rounded-lg p-4 bg-gray-50/60">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="folder_master" x-model="folderMaster" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="folder_master" class="ml-2 block text-sm font-semibold text-gray-900">SAP Master Data Folder</label>
                                        </div>
                                        <div class="ml-6 space-y-2">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Items" id="perm_master_items" x-model="perms.master_items" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_master_items" class="ml-2 block text-sm text-gray-700">Item Groups & Items</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Administrator.Taxes" id="perm_master_taxes" x-model="perms.master_taxes" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_master_taxes" class="ml-2 block text-sm text-gray-700">Taxes & Business Partners</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Scheduler Folder -->
                                    <div class="border border-gray-200/80 rounded-lg p-4 bg-gray-50/60">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="folder_scheduler" x-model="folderScheduler" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="folder_scheduler" class="ml-2 block text-sm font-semibold text-gray-900">Scheduler Folder</label>
                                        </div>
                                        <div class="ml-6 space-y-2">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Scheduler.MasterData" id="perm_scheduler_md" x-model="perms.scheduler_md" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_scheduler_md" class="ml-2 block text-sm text-gray-700">Master Data</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Scheduler.Document" id="perm_scheduler_doc" x-model="perms.scheduler_doc" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_scheduler_doc" class="ml-2 block text-sm text-gray-700">Document</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Transaction Folder -->
                                    <div class="border border-gray-200/80 rounded-lg p-4 bg-gray-50/60">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="folder_purchase" x-model="folderPurchase" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="folder_purchase" class="ml-2 block text-sm font-semibold text-gray-900">Transaction Folder</label>
                                        </div>
                                        <div class="ml-6 space-y-2">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="Purchase.PurchaseRequest" id="perm_purchase_pr" x-model="perms.purchase_pr" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_purchase_pr" class="ml-2 block text-sm text-gray-700">Purchase Request</label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50/80 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-xl border-t border-gray-200/50">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save Role
                            </button>
                            <button type="button" @click="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('roleModal', () => ({
                showModal: false,
                editMode: false,
                formAction: '{{ route("roles.store") }}',
                roleId: '',
                roleName: '',
                perms: {
                    admin_config: false,
                    admin_roles: false,
                    admin_users: false,
                    admin_logs: false,
                    master_items: false,
                    master_taxes: false,
                    scheduler_md: false,
                    scheduler_doc: false,
                    purchase_pr: false
                },

                openAddModal() {
                    this.editMode = false;
                    this.roleId = '';
                    this.roleName = '';
                    this.formAction = '{{ route("roles.store") }}';
                    this.resetPerms();
                    this.showModal = true;
                },

                openEditModal(role) {
                    this.editMode = true;
                    this.roleId = role.id;
                    this.roleName = role.name;
                    this.formAction = '/roles/' + role.id;
                    
                    const list = role.permissions || [];
                    this.perms.admin_config = list.includes('Administrator.Config');
                    this.perms.admin_roles = list.includes('Administrator.Roles');
                    this.perms.admin_users = list.includes('Administrator.Users');
                    this.perms.admin_logs = list.includes('Administrator.Logs');
                    this.perms.master_items = list.includes('Administrator.Items');
                    this.perms.master_taxes = list.includes('Administrator.Taxes');
                    this.perms.scheduler_md = list.includes('Scheduler.MasterData');
                    this.perms.scheduler_doc = list.includes('Scheduler.Document');
                    this.perms.purchase_pr = list.includes('Purchase.PurchaseRequest');

                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                resetPerms() {
                    this.perms = {
                        admin_config: false,
                        admin_roles: false,
                        admin_users: false,
                        admin_logs: false,
                        master_items: false,
                        master_taxes: false,
                        scheduler_md: false,
                        scheduler_doc: false,
                        purchase_pr: false
                    };
                },

                get folderAdmin() {
                    return this.perms.admin_config && this.perms.admin_roles && this.perms.admin_users && this.perms.admin_logs;
                },
                set folderAdmin(value) {
                    this.perms.admin_config = value;
                    this.perms.admin_roles = value;
                    this.perms.admin_users = value;
                    this.perms.admin_logs = value;
                },

                get folderMaster() {
                    return this.perms.master_items && this.perms.master_taxes;
                },
                set folderMaster(value) {
                    this.perms.master_items = value;
                    this.perms.master_taxes = value;
                },

                get folderScheduler() {
                    return this.perms.scheduler_md && this.perms.scheduler_doc;
                },
                set folderScheduler(value) {
                    this.perms.scheduler_md = value;
                    this.perms.scheduler_doc = value;
                },

                get folderPurchase() {
                    return this.perms.purchase_pr;
                },
                set folderPurchase(value) {
                    this.perms.purchase_pr = value;
                }
            }));
        });
    </script>
</x-app-layout>
