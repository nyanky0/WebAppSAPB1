<x-app-layout>
    <div class="py-6" x-data="userModal()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Users Management</h1>
                <button @click="openAddModal()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add User
                </button>
            </div>

            <div class="bg-white/60 backdrop-blur-xl border border-white/40 overflow-hidden shadow-xl sm:rounded-2xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50 backdrop-blur-sm">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Web Username</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SAP Username</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                            <th scope="col" class="relative px-6 py-3.5"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/40 divide-y divide-gray-200/50">
                        @foreach($users as $user)
                            <tr class="hover:bg-white/80 hover:shadow-md transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">{{ $user->username }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">{{ $user->sap_user ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-md bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        {{ $user->role->name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <button @click="openEditModal({{ \Illuminate\Support\Js::from($user) }})" type="button" class="text-indigo-600 hover:text-indigo-900 font-semibold transition-colors">Edit</button>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
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
                     class="relative z-50 w-full max-w-lg bg-white/95 backdrop-blur-xl border border-white/40 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all my-8">
                    
                    <form :action="formAction" method="POST">
                        @csrf
                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white/80 px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold text-gray-900" id="modal-title" x-text="editMode ? 'Edit User' : 'Add User'"></h3>
                                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" name="name" x-model="name" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Web Username</label>
                                    <input type="text" name="username" x-model="username" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Web Password <span x-show="editMode" class="text-xs text-gray-400 font-normal">(Leave blank to keep current)</span></label>
                                    <input type="password" name="password" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white" :required="!editMode">
                                </div>

                                <div class="border-t border-gray-200/80 pt-4 mt-4">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">SAP Integration Credentials</h4>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">SAP Username</label>
                                            <input type="text" name="sap_user" x-model="sap_user" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">SAP Password</label>
                                            <input type="password" name="sap_password" x-model="sap_password" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/60 transition-colors focus:bg-white">
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200/80 pt-4">
                                    <label class="block text-sm font-medium text-gray-700">User Role</label>
                                    <select name="role_id" x-model="role_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg bg-white/60" required>
                                        <option value="">Select a role...</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50/80 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-xl border-t border-gray-200/50">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save User
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
            Alpine.data('userModal', () => ({
                showModal: false,
                editMode: false,
                formAction: '{{ route("users.store") }}',
                name: '',
                username: '',
                sap_user: '',
                sap_password: '',
                role_id: '',

                openAddModal() {
                    this.editMode = false;
                    this.name = '';
                    this.username = '';
                    this.sap_user = '';
                    this.sap_password = '';
                    this.role_id = '';
                    this.formAction = '{{ route("users.store") }}';
                    this.showModal = true;
                },

                openEditModal(user) {
                    this.editMode = true;
                    this.name = user.name || '';
                    this.username = user.username || '';
                    this.sap_user = user.sap_user || '';
                    this.sap_password = user.sap_password || '';
                    this.role_id = user.role_id || '';
                    this.formAction = '/users/' + user.uid7;
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                }
            }));
        });
    </script>
</x-app-layout>
