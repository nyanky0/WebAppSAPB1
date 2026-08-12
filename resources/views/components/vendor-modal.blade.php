<div {{ $attributes }} x-data="vendorModal()" @open-vendor-modal.window="openModal()" x-show="isOpen" class="fixed z-[100] inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <!-- Backdrop with Glassmorphism -->
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
             aria-hidden="true" 
             @click="closeModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal Panel -->
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-middle bg-white/80 backdrop-blur-xl border border-white/40 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
            
            <div class="px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-semibold text-gray-800" id="modal-title">Select Vendor</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="mb-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Search by Vendor Code or Name..." class="pl-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white">
                </div>
                
                <div x-show="isLoading" class="py-16 flex flex-col justify-center items-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600 font-medium tracking-wide">Loading vendors from local database...</span>
                </div>

                <div x-show="!isLoading" class="mt-2 h-[400px] overflow-y-auto border border-white/50 rounded-lg shadow-inner bg-white/40">
                    <table class="min-w-full divide-y divide-gray-200/50">
                        <thead class="bg-gray-100/50 backdrop-blur-md sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vendor Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vendor Name</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/50">
                            <template x-for="vendor in filteredVendors" :key="vendor.CardCode">
                                <tr @click="selectVendor(vendor)" class="hover:bg-white/80 hover:shadow-md transition-all duration-200 cursor-pointer transform hover:-translate-y-px">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900" x-text="vendor.CardCode"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" x-text="vendor.CardName"></td>
                                </tr>
                            </template>
                            <tr x-show="filteredVendors.length === 0 && !isLoading">
                                <td colspan="2" class="px-6 py-8 text-center text-sm text-gray-500 bg-white/30">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p>No vendors found.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('vendorModal', () => ({
            isOpen: false,
            vendors: [],
            isLoading: false,
            searchQuery: '',
            hasFetched: false,

            openModal() {
                this.isOpen = true;
                if (!this.hasFetched) {
                    this.fetchVendors();
                }
            },
            
            closeModal() {
                this.isOpen = false;
                this.searchQuery = '';
            },
            
            fetchVendors() {
                this.isLoading = true;
                fetch('{{ route("api.vendors") }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.vendors = data.data;
                            this.hasFetched = true;
                        } else {
                            // Emit a global error flash message
                            window.dispatchEvent(new CustomEvent('flash-message', {
                                detail: { type: 'error', message: 'SAP Error: ' + data.message }
                            }));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('flash-message', {
                            detail: { type: 'error', message: 'An error occurred while connecting to the server.' }
                        }));
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },
            
            selectVendor(vendor) {
                this.$dispatch('vendor-selected', vendor);
                this.closeModal();
            },
            
            get filteredVendors() {
                if (this.searchQuery === '') return this.vendors;
                const query = this.searchQuery.toLowerCase();
                return this.vendors.filter(v => 
                    (v.CardCode && v.CardCode.toLowerCase().includes(query)) ||
                    (v.CardName && v.CardName.toLowerCase().includes(query))
                );
            }
        }));
    });
</script>
