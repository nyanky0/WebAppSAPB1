<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">System Configuration</h1>
            </div>

            <div class="bg-white/60 backdrop-blur-xl border border-white/40 overflow-hidden shadow-xl sm:rounded-2xl transition-all">
                <div class="p-6">
                    <form method="POST" action="{{ route('config.update') }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Base URL -->
                            <div class="sm:col-span-2">
                                <label for="base_url" class="block text-sm font-medium text-gray-700">Service Layer Base URL</label>
                                <div class="mt-1">
                                    <input type="url" name="base_url" id="base_url" value="{{ old('base_url', $config->base_url) }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. https://110.239.87.30:65432/b1s/v1/" required>
                                </div>
                            </div>

                            <!-- Database Name -->
                            <div class="sm:col-span-2" x-data="{
                                databaseName: '{{ old('database', $config->database) }}',
                                openDatabaseModal() {
                                    const baseUrl = document.getElementById('base_url').value;
                                    
                                    if (!baseUrl) {
                                        window.dispatchEvent(new CustomEvent('flash-message', {
                                            detail: { type: 'error', message: 'Please enter Base URL first.' }
                                        }));
                                        return;
                                    }
                                    
                                    window.dispatchEvent(new CustomEvent('open-database-modal', {
                                        detail: { baseUrl: baseUrl }
                                    }));
                                },
                                handleDatabaseSelected(dbName) {
                                    this.databaseName = dbName;
                                }
                            }"
                            @database-selected.window="handleDatabaseSelected($event.detail)">
                                <label for="database" class="block text-sm font-medium text-gray-700">Database Name</label>
                                <div class="mt-1 flex space-x-3">
                                    <input type="text" name="database" id="database" x-model="databaseName" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. SBODEMOUS">
                                    <button type="button" @click="openDatabaseModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-sm w-48">
                                        Fetch from SAP
                                    </button>
                                </div>
                            </div>

                            <!-- Period Indicator -->
                            <div class="sm:col-span-2" x-data="{ 
                                periodIndicator: '{{ old('period_indicator', $config->period_indicator) }}',
                                openPeriodModal() {
                                    const baseUrl = document.getElementById('base_url').value;
                                    const db = document.getElementById('database').value;
                                    
                                    if (!baseUrl || !db) {
                                        window.dispatchEvent(new CustomEvent('flash-message', {
                                            detail: { type: 'error', message: 'Please enter Base URL and Database first.' }
                                        }));
                                        return;
                                    }
                                    
                                    window.dispatchEvent(new CustomEvent('open-period-modal', {
                                        detail: { baseUrl: baseUrl, database: db }
                                    }));
                                },
                                handleIndicatorSelected(indicator) {
                                    this.periodIndicator = indicator;
                                }
                            }"
                            @indicator-selected.window="handleIndicatorSelected($event.detail)">
                                <label for="period_indicator" class="block text-sm font-medium text-gray-700">Period Indicator</label>
                                <div class="mt-1 flex space-x-3">
                                    <input type="text" name="period_indicator" id="period_indicator" x-model="periodIndicator" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. 2026">
                                    <button type="button" @click="openPeriodModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all shadow-sm w-48">
                                        Fetch from SAP
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">The active period indicator for the current date, used to automatically select the correct document series.</p>
                            </div>
                        </div>

                        <div class="pt-6">
                            <div class="flex justify-end">
                                <button type="submit" class="ml-3 inline-flex justify-center py-2.5 px-6 border border-transparent shadow-md text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <x-period-modal />
    <x-database-modal />
</x-app-layout>
