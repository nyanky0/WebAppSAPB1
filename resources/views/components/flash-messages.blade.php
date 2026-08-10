<div x-data="flashMessages()" 
     @flash-message.window="addMessage($event.detail)"
     class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[200] flex flex-col items-center pointer-events-none w-full max-w-sm space-y-4">
    
    <template x-for="message in messages" :key="message.id">
        <div x-show="message.show"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 -translate-y-4"
             class="pointer-events-auto w-full bg-white/80 backdrop-blur-xl border border-white/50 shadow-2xl rounded-2xl p-6 flex flex-col items-center text-center relative overflow-hidden">
             
            <!-- Background glow -->
            <div class="absolute inset-0 opacity-20 pointer-events-none z-0" :class="{
                'bg-gradient-to-br from-green-400 to-emerald-600': message.type === 'success',
                'bg-gradient-to-br from-red-400 to-rose-600': message.type === 'error'
            }"></div>

            <!-- Icon -->
            <div class="mb-4 rounded-full p-3 shadow-inner relative z-10" :class="{
                'bg-green-100 text-green-600': message.type === 'success',
                'bg-red-100 text-red-600': message.type === 'error'
            }">
                <!-- Success Icon -->
                <svg x-show="message.type === 'success'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <!-- Error Icon -->
                <svg x-show="message.type === 'error'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-800 mb-2 relative z-10" x-text="message.type === 'success' ? 'Success!' : 'Error!'"></h3>
            <p class="text-sm font-medium text-gray-600 relative z-10" x-html="message.text"></p>
            
            <!-- Close Button -->
            <button @click="removeMessage(message.id)" class="relative z-10 mt-6 px-6 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg shadow-md hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                Okay
            </button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('flashMessages', () => ({
            messages: [],
            
            init() {
                // Intercept Laravel session successes
                @if(session('success'))
                    this.addMessage({ type: 'success', message: '{{ session('success') }}' });
                @endif
                
                // Intercept Laravel validation errors
                @if($errors->any())
                    let errorList = '<ul class="list-disc list-inside text-left mt-2">';
                    @foreach($errors->all() as $error)
                        errorList += '<li>{{ $error }}</li>';
                    @endforeach
                    errorList += '</ul>';
                    this.addMessage({ type: 'error', message: errorList });
                @endif
            },

            addMessage(detail) {
                const id = Date.now();
                this.messages.push({
                    id: id,
                    type: detail.type || 'success',
                    text: detail.message,
                    show: true
                });
                
                // Auto dismiss after 5 seconds if it's a success
                if (detail.type === 'success') {
                    setTimeout(() => {
                        this.removeMessage(id);
                    }, 5000);
                }
            },

            removeMessage(id) {
                const message = this.messages.find(m => m.id === id);
                if (message) {
                    message.show = false;
                    setTimeout(() => {
                        this.messages = this.messages.filter(m => m.id !== id);
                    }, 300); // Wait for transition
                }
            }
        }));
    });
</script>
