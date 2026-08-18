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
        { name: 'UoM Groups', route: '{{ route('sap.sync.uom-groups') }}', status: 'pending', message: '' },
        { name: 'Units of Measure', route: '{{ route('sap.sync.uoms') }}', status: 'pending', message: '' },
        { name: 'Warehouses', route: '{{ route('sap.sync.warehouses') }}', status: 'pending', message: '' },
        { name: 'Bin Locations', route: '{{ route('sap.sync.bin-locations') }}', status: 'pending', message: '' },
        { name: 'Chart of Accounts', route: '{{ route('sap.sync.coa') }}', status: 'pending', message: '' },
        { name: 'Dimensions', route: '{{ route('sap.sync.dimensions') }}', status: 'pending', message: '' },
        { name: 'Cost Centers', route: '{{ route('sap.sync.cost-centers') }}', status: 'pending', message: '' },
        { name: 'Taxes (VatGroups)', route: '{{ route('sap.sync.taxes') }}', status: 'pending', message: '' },
        { name: 'Withholding Taxes', route: '{{ route('sap.sync.wtax') }}', status: 'pending', message: '' },
        { name: 'Branches', route: '{{ route('sap.sync.branches') }}', status: 'pending', message: '' },
        { name: 'BP Groups', route: '{{ route('sap.sync.bp-groups') }}', status: 'pending', message: '' },
        { name: 'Business Partners', route: '{{ route('sap.sync.bp') }}', status: 'pending', message: '' }
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
        if (this.isSyncing) return;
        const text = this.logs.map(l => `[${l.time}] ${l.text}`).join('\n');
        navigator.clipboard.writeText(text).then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
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

    <!-- Solid Dark Overlay Backdrop -->
    <div x-show="showModal" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/70 flex items-center justify-center p-4">
        
        <!-- Windows 11 Solid White Modal Frame -->
        <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden transform transition-all"
             @click.outside="if (!isSyncing) showModal = false">
            
            <!-- Window Titlebar Header (Light Slate with Explicit Dark Slate Text) -->
            <div class="px-4 py-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-indigo-600" :class="isSyncing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <h3 class="text-sm font-extrabold tracking-tight" style="color: #0f172a !important;">Sync Everything From SAP</h3>
                    <span class="text-[11px] text-slate-500 font-normal">| Master Data</span>
                </div>

                <button x-show="!isSyncing" @click="showModal = false" type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-md transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="p-4 space-y-3.5 bg-white">
                
                <!-- Live Counter & Progress Bar Header -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-gray-800">
                            <template x-if="isSyncing">
                                <span>Task <span class="text-indigo-600 font-bold" x-text="currentIndex + 1"></span> working (<span class="text-emerald-600 font-bold" x-text="completedCount"></span> done) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                            <template x-if="completed">
                                <span class="text-emerald-600 font-bold">Completed (<span x-text="completedCount"></span> done) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                            <template x-if="cancelled">
                                <span class="text-amber-600 font-bold">Cancelled (<span x-text="completedCount"></span> processed) of <span x-text="tasks.length"></span> tasks</span>
                            </template>
                        </span>
                        <span class="text-xs font-bold text-indigo-600" x-text="`${progressPercent}%`"></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300 ease-out"
                             :style="`width: ${progressPercent}%`"></div>
                    </div>
                </div>

                <!-- Windows 11 Task List (Max 6 Items Visible, Height 240px) -->
                <div class="flex flex-col space-y-1.5 h-[240px] max-h-[240px] overflow-y-auto pr-1">
                    <template x-for="(t, idx) in tasks" :key="idx">
                        <div class="h-9 px-3 py-1.5 rounded-lg border text-xs flex items-center justify-between shrink-0 transition-colors"
                             :class="{
                                'bg-indigo-50 border-indigo-300 text-indigo-900 font-medium': t.status === 'syncing',
                                'bg-emerald-50 border-emerald-300 text-emerald-800': t.status === 'done',
                                'bg-red-50 border-red-300 text-red-800': t.status === 'error',
                                'bg-gray-50 border-gray-200 text-gray-700': t.status === 'pending'
                             }">
                            <div class="flex items-center space-x-2 truncate">
                                <span class="font-bold text-gray-400 w-4" x-text="`${idx + 1}.`"></span>
                                <span class="truncate font-semibold text-gray-900" x-text="t.name"></span>
                            </div>
                            
                            <!-- Status Indicator Icons -->
                            <div class="shrink-0 ml-2 flex items-center">
                                <template x-if="t.status === 'syncing'">
                                    <span class="inline-flex items-center text-indigo-600 font-medium text-[11px]">
                                        <svg class="w-3.5 h-3.5 animate-spin text-indigo-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Syncing...
                                    </span>
                                </template>
                                <template x-if="t.status === 'done'">
                                    <span class="inline-flex items-center text-emerald-600 font-semibold text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Done
                                    </span>
                                </template>
                                <template x-if="t.status === 'error'">
                                    <span class="inline-flex items-center text-red-600 font-semibold text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-red-600 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Error
                                    </span>
                                </template>
                                <template x-if="t.status === 'pending'">
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Pending</span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Synchronization Activity Log Terminal (Pitch-Black Background, Exactly 6 Lines Max) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-[11px] font-bold text-gray-600 uppercase tracking-wider">Synchronization Activity Log</h4>
                        
                        <!-- Copy Logs Button Disabled (Greyed Out) During Syncing -->
                        <button type="button" 
                                @click="copyLogs()" 
                                :disabled="isSyncing"
                                class="px-3 py-1 text-xs font-semibold rounded-md transition-all focus:outline-none"
                                :class="isSyncing ? 'bg-gray-300 text-gray-500 cursor-not-allowed border border-gray-300 opacity-70 shadow-none' : 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-sm hover:shadow active:scale-95'">
                            <span x-text="copied ? 'Logs Copied!' : 'Copy Logs'"></span>
                        </button>
                    </div>

                    <!-- Log Container (Strict Height max 6 lines via inline styles, light grey background) -->
                    <div id="sync-log-container" 
                         class="font-mono text-[11px] leading-[18px] p-2.5 rounded-lg overflow-y-auto shadow-inner border border-gray-200"
                         style="max-height: 128px; background-color: #f3f4f6 !important; color: #1f2937 !important;">
                        <template x-for="(log, lIdx) in logs" :key="lIdx">
                            <div class="leading-[18px] flex items-start space-x-1.5 shrink-0 py-0.5">
                                <span class="text-gray-500 font-normal shrink-0" x-text="`[${log.time}]`"></span>
                                <span class="text-gray-800 font-medium break-words" x-text="log.text"></span>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Modal Action Footer -->
            <div class="px-4 py-3 bg-gray-100 border-t border-gray-200 flex items-center justify-between">
                <div class="text-[11px] text-gray-600">
                    <span x-show="isSyncing" class="inline-flex items-center text-indigo-600 font-medium">
                        <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-ping mr-1.5"></span>
                        Syncing SAP Master Data...
                    </span>
                    <span x-show="completed" class="text-emerald-600 font-semibold">
                        ✅ Synchronization finished.
                    </span>
                    <span x-show="cancelled" class="text-amber-600 font-semibold">
                        ⚠️ Sync cancelled.
                    </span>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Explicit Red Cancel Sync Button -->
                    <button x-show="isSyncing" 
                            @click="cancelSync()" 
                            type="button" 
                            class="px-4 py-1.5 text-xs font-bold text-white rounded-md shadow-sm transition-all cursor-pointer focus:outline-none hover:opacity-90 active:scale-95"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: none !important;">
                        Cancel Sync
                    </button>

                    <!-- Close Button Color Matched to Copy Logs (Indigo Theme) -->
                    <button x-show="!isSyncing"
                            @click="showModal = false" 
                            type="button" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold text-xs rounded-md shadow-sm hover:shadow transition-all cursor-pointer focus:outline-none">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
