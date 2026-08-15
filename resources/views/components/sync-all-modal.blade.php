<div x-data="{
    showModal: false,
    isSyncing: false,
    completed: false,
    cancelled: false,
    copied: false,
    currentIndex: 0,
    completedCount: 0,
    logs: [],
    abortController: null,
    tasks: [
        { name: 'Item Groups', route: '{{ route('sap.sync.item-groups') }}', status: 'pending', message: '' },
        { name: 'Items', route: '{{ route('sap.sync.items') }}', status: 'pending', message: '' },
        { name: 'Units of Measure', route: '{{ route('sap.sync.uoms') }}', status: 'pending', message: '' },
        { name: 'Warehouses & Bins', route: '{{ route('sap.sync.warehouses') }}', status: 'pending', message: '' },
        { name: 'Chart of Accounts', route: '{{ route('sap.sync.coa') }}', status: 'pending', message: '' },
        { name: 'Dimensions', route: '{{ route('sap.sync.dimensions') }}', status: 'pending', message: '' },
        { name: 'Cost Centers', route: '{{ route('sap.sync.cost-centers') }}', status: 'pending', message: '' },
        { name: 'Taxes (VatGroups)', route: '{{ route('sap.sync.taxes') }}', status: 'pending', message: '' },
        { name: 'Withholding Taxes', route: '{{ route('sap.sync.wtax') }}', status: 'pending', message: '' },
        { name: 'Branches', route: '{{ route('sap.sync.branches') }}', status: 'pending', message: '' },
        { name: 'Business Partners', route: '{{ route('sap.sync.bp') }}', status: 'pending', message: '' },
        { name: 'Period Indicators', route: '{{ route('sap.sync.period-indicators') }}', status: 'pending', message: '' }
    ],
    get progressPercent() {
        return Math.round((this.completedCount / this.tasks.length) * 100);
    },
    addLog(msg) {
        this.logs.push({ time: new Date().toLocaleTimeString(), text: msg });
        this.$nextTick(() => {
            const container = document.getElementById('sync-log-container');
            if (container) container.scrollTop = container.scrollHeight;
        });
    },
    copyLogs() {
        const text = this.logs.map(l => `[${l.time}] ${l.text}`).join('\n');
        navigator.clipboard.writeText(text).then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }).catch(err => {
            console.error('Failed to copy logs: ', err);
        });
    },
    cancelSync() {
        if (!this.isSyncing) return;
        this.cancelled = true;
        this.isSyncing = false;
        if (this.abortController) {
            this.abortController.abort();
        }
        this.addLog(`⚠️ Synchronization cancelled by user. All staged operations rolled back.`);
    },
    async startSyncAll() {
        this.showModal = true;
        this.isSyncing = true;
        this.completed = false;
        this.cancelled = false;
        this.currentIndex = 0;
        this.completedCount = 0;
        this.logs = [];
        this.tasks.forEach(t => { t.status = 'pending'; t.message = ''; });

        const csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content');

        for (let i = 0; i < this.tasks.length; i++) {
            if (this.cancelled) break;

            this.currentIndex = i;
            const task = this.tasks[i];
            task.status = 'syncing';
            this.addLog(`Task ${i + 1} working (${this.completedCount} done) of ${this.tasks.length} tasks: Syncing ${task.name}...`);

            this.abortController = new AbortController();

            try {
                const res = await fetch(task.route, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: this.abortController.signal
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    task.status = 'done';
                    task.message = data.message || 'Synced successfully.';
                    this.addLog(`✅ Task ${i + 1} done: ${task.name} - ${task.message}`);
                } else {
                    task.status = 'error';
                    task.message = data.message || 'Failed to sync.';
                    this.addLog(`❌ Task ${i + 1} error: ${task.name} - ${task.message}`);
                }
            } catch (err) {
                if (err.name === 'AbortError' || this.cancelled) {
                    task.status = 'error';
                    task.message = 'Cancelled by user.';
                    break;
                }
                task.status = 'error';
                task.message = err.message || 'Network communication error.';
                this.addLog(`❌ Task ${i + 1} error: ${task.name} - ${task.message}`);
            }

            if (this.cancelled) break;
            this.completedCount = i + 1;
        }

        this.isSyncing = false;
        if (!this.cancelled) {
            this.completed = true;
            this.addLog(`🎉 Completed (${this.completedCount} done) of ${this.tasks.length} master data sync tasks.`);
        }
    }
}"
@open-sync-all-modal.window="startSyncAll()">

    <!-- Progress Modal Backdrop -->
    <div x-show="showModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/70 backdrop-blur-md flex items-center justify-center p-4">
        
        <!-- Modal Card -->
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 transform transition-all"
             @click.outside="if (!isSyncing) showModal = false">
            
            <!-- Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-indigo-700 via-indigo-800 to-purple-800 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/10 rounded-lg backdrop-blur-sm">
                        <svg class="w-6 h-6 text-indigo-200" :class="isSyncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Sync Everything From SAP</h3>
                        <p class="text-xs text-indigo-200">Bulk Master Data Synchronization Engine</p>
                    </div>
                </div>

                <button x-show="!isSyncing" @click="showModal = false" type="button" class="text-indigo-200 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-all hover:-translate-y-0.5 active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="p-6 space-y-6">
                
                <!-- Live Counter & Progress Bar Header -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-800">
                            <template x-if="isSyncing">
                                <span>Task <span class="text-indigo-600 font-bold" x-text="currentIndex + 1"></span> working (<span class="text-green-600 font-bold" x-text="completedCount"></span> done) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                            <template x-if="completed">
                                <span class="text-green-600 font-bold">Completed (<span x-text="completedCount"></span> done) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                            <template x-if="cancelled">
                                <span class="text-amber-600 font-bold">Cancelled (<span x-text="completedCount"></span> processed) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                        </span>
                        <span class="text-sm font-bold text-indigo-600" x-text="`${progressPercent}%`"></span>
                    </div>

                    <!-- Animated Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-3.5 overflow-hidden p-0.5">
                        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-green-500 h-2.5 rounded-full transition-all duration-500 ease-out"
                             :style="`width: ${progressPercent}%`"></div>
                    </div>
                </div>

                <!-- Task Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-56 overflow-y-auto pr-1">
                    <template x-for="(t, idx) in tasks" :key="idx">
                        <div class="p-2.5 rounded-lg border text-xs flex items-center justify-between transition-colors"
                             :class="{
                                'bg-indigo-50/70 border-indigo-200 text-indigo-900 font-medium shadow-sm': t.status === 'syncing',
                                'bg-green-50/60 border-green-200 text-green-800': t.status === 'done',
                                'bg-red-50/60 border-red-200 text-red-800': t.status === 'error',
                                'bg-gray-50 border-gray-200 text-gray-500': t.status === 'pending'
                             }">
                            <div class="flex items-center space-x-2 truncate">
                                <span class="font-bold text-gray-400" x-text="`${idx + 1}.`"></span>
                                <span class="truncate font-semibold" x-text="t.name"></span>
                            </div>
                            
                            <!-- Status Indicator Icons -->
                            <div class="shrink-0 ml-2">
                                <template x-if="t.status === 'syncing'">
                                    <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </template>
                                <template x-if="t.status === 'done'">
                                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </template>
                                <template x-if="t.status === 'error'">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </template>
                                <template x-if="t.status === 'pending'">
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Pending</span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Detailed Real-time Output Log Area -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Synchronization Activity Log</h4>
                        
                        <!-- Copy Activity Logs Button -->
                        <button type="button" 
                                @click="copyLogs()" 
                                class="inline-flex items-center space-x-1.5 px-3 py-1 bg-gray-800 hover:bg-gray-700 text-gray-200 hover:text-white rounded-lg text-xs font-semibold transition-all hover:shadow-md hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span x-text="copied ? 'Logs Copied!' : 'Copy Logs'"></span>
                        </button>
                    </div>

                    <div id="sync-log-container" class="bg-gray-900 text-gray-200 font-mono text-[11px] p-3 rounded-lg h-32 overflow-y-auto space-y-1 shadow-inner border border-gray-800">
                        <template x-for="(log, lIdx) in logs" :key="lIdx">
                            <div class="leading-relaxed">
                                <span class="text-gray-500" x-text="`[${log.time}]`"></span>
                                <span x-text="log.text"></span>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    <span x-show="isSyncing" class="inline-flex items-center text-indigo-600 font-medium">
                        <span class="w-2 h-2 bg-indigo-600 rounded-full animate-ping mr-2"></span>
                        Synchronizing SAP Master Data...
                    </span>
                    <span x-show="completed" class="text-green-600 font-semibold">
                        ✅ Master Data synchronization finished.
                    </span>
                    <span x-show="cancelled" class="text-amber-600 font-semibold">
                        ⚠️ Sync cancelled by user.
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Cancel Button -->
                    <button x-show="isSyncing" 
                            @click="cancelSync()" 
                            type="button" 
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-500 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer">
                        Cancel Sync
                    </button>

                    <!-- Close Button -->
                    <button @click="showModal = false" 
                            type="button" 
                            :disabled="isSyncing"
                            :class="isSyncing ? 'opacity-50 cursor-not-allowed bg-gray-400 shadow-none' : 'bg-indigo-600 hover:bg-indigo-500 shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 cursor-pointer'"
                            class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all focus:outline-none">
                        <span x-text="isSyncing ? 'Syncing...' : 'Close'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
