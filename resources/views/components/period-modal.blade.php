<div {{ $attributes }} 
     x-data="periodModal()" 
     @open-period-modal.window="openModal($event.detail)" 
     x-show="isOpen" 
     class="fixed z-[100] inset-0 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
        <!-- Backdrop with Glassmorphism -->
        <div x-show="isOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
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
             class="relative z-50 inline-block align-middle bg-white/90 backdrop-blur-xl border border-white/40 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-xl sm:w-full">
            
            <div class="px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-xl font-semibold text-gray-800" id="modal-title">Select Period Indicator</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div x-show="isLoading" class="py-12 flex flex-col justify-center items-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600 font-medium tracking-wide">Connecting to SAP Service Layer...</span>
                </div>
                
                <div x-show="!isLoading" class="mt-4">
                    <div class="max-h-60 overflow-y-auto pr-2 rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50 backdrop-blur-sm sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Indicator</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/40 divide-y divide-gray-100">
                                <template x-for="item in indicators" :key="item.Indicator">
                                    <tr @click="selectIndicator(item.Indicator)" class="hover:bg-white/80 hover:shadow-md transition-all duration-200 cursor-pointer transform hover:-translate-y-px">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="item.Indicator"></td>
                                    </tr>
                                </template>
                                <tr x-show="indicators.length === 0">
                                    <td class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center italic">No period indicators found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('periodModal', () => ({
            isOpen: false,
            isLoading: false,
            indicators: [],
            
            openModal(credentials) {
                this.isOpen = true;
                this.isLoading = true;
                this.indicators = [];
                
                fetch('{{ route("api.config.fetch-period") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        base_url: credentials.baseUrl,
                        database: credentials.database
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.indicators = data.indicators || [];
                        if (this.indicators.length === 0) {
                            this.isOpen = false;
                            window.dispatchEvent(new CustomEvent('flash-message', {
                                detail: { type: 'error', message: 'No active period found for today.' }
                            }));
                        }
                    } else {
                        // Close modal on error and show flash message
                        this.isOpen = false;
                        window.dispatchEvent(new CustomEvent('flash-message', {
                            detail: { type: 'error', message: data.message }
                        }));
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.isOpen = false;
                    window.dispatchEvent(new CustomEvent('flash-message', {
                        detail: { type: 'error', message: 'Failed to connect to SAP Service Layer.' }
                    }));
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },
            
            closeModal() {
                this.isOpen = false;
            },
            
            selectIndicator(indicatorStr) {
                this.$dispatch('indicator-selected', indicatorStr);
                this.closeModal();
            }
        }));
    });
</script>
