<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">System Configuration</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- SAP B1 Configuration Panel -->
                <div class="bg-white/60 backdrop-blur-xl border border-white/40 overflow-hidden shadow-xl sm:rounded-2xl transition-all"
                     x-data="{
                         initialBaseUrl: '{{ old('base_url', $config->base_url) }}',
                         initialDatabase: '{{ old('database', $config->database) }}',
                         initialPeriodIndicator: '{{ old('period_indicator', $config->period_indicator) }}',
                         initialMaxRetries: '{{ old('max_retries', $config->max_retries ?? 3) }}',

                         baseUrl: '{{ old('base_url', $config->base_url) }}',
                         database: '{{ old('database', $config->database) }}',
                         periodIndicator: '{{ old('period_indicator', $config->period_indicator) }}',
                         maxRetries: '{{ old('max_retries', $config->max_retries ?? 3) }}',
                         isSubmitting: false,

                         get isDirty() {
                             return (this.baseUrl ?? '').trim() !== (this.initialBaseUrl ?? '').trim() ||
                                    (this.database ?? '').trim() !== (this.initialDatabase ?? '').trim() ||
                                    (this.periodIndicator ?? '').trim() !== (this.initialPeriodIndicator ?? '').trim() ||
                                    String(this.maxRetries ?? '').trim() !== String(this.initialMaxRetries ?? '').trim();
                         },

                         openPeriodModal() {
                             if (!this.baseUrl) {
                                 window.dispatchEvent(new CustomEvent('flash-message', {
                                     detail: { type: 'error', message: 'Please enter Service Layer Base URL first.' }
                                 }));
                                 return;
                             }
                             if (!this.database) {
                                 window.dispatchEvent(new CustomEvent('flash-message', {
                                     detail: { type: 'error', message: 'Please select or enter Database Name first.' }
                                 }));
                                 return;
                             }
                             window.dispatchEvent(new CustomEvent('open-period-modal', {
                                 detail: { baseUrl: this.baseUrl, database: this.database }
                             }));
                         }
                     }"
                     @indicator-selected.window="periodIndicator = $event.detail"
                     @database-selected.window="database = $event.detail">
                    <div class="p-6">
                        <form method="POST" action="{{ route('config.update') }}" @submit="isSubmitting = true">
                            @csrf
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Base URL -->
                                <div class="sm:col-span-2">
                                    <label for="base_url" class="block text-sm font-medium text-gray-700">Service Layer Base URL</label>
                                    <div class="mt-1">
                                        <input type="url" name="base_url" id="base_url" x-model="baseUrl" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. https://110.239.87.30:65432/b1s/v1/" required>
                                    </div>
                                </div>

                                <!-- Database Name -->
                                <div class="sm:col-span-2">
                                    <label for="database" class="block text-sm font-medium text-gray-700">Database Name</label>
                                    <div class="mt-1 flex space-x-3">
                                        <input type="text" name="database" id="database" x-model="database" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. SBODEMOUS">
                                    </div>
                                </div>

                                <!-- Period Indicator -->
                                <div class="sm:col-span-2">
                                    <label for="period_indicator" class="block text-sm font-medium text-gray-700">Period Indicator</label>
                                    <div class="mt-1 flex space-x-3">
                                        <input type="text" name="period_indicator" id="period_indicator" x-model="periodIndicator" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" placeholder="e.g. 2026">
                                        <button type="button" @click="openPeriodModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all shadow-sm w-48">
                                            Fetch from SAP
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">The active period indicator for the current date, used to automatically select the correct document series.</p>
                                </div>

                                <!-- Maximum Service Layer Retries -->
                                <div class="sm:col-span-2">
                                    <label for="max_retries" class="block text-sm font-medium text-gray-700">Maximum Service Layer Retries</label>
                                    <div class="mt-1">
                                        <input type="number" name="max_retries" id="max_retries" x-model="maxRetries" min="1" max="10" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg bg-white/50 backdrop-blur-sm transition-colors focus:bg-white" required>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Number of automatic retries (default 3) when connection or 5xx server errors occur during SAP Service Layer communication.</p>
                                </div>
                            </div>

                            @php
                                $isConfigured = !empty($config->base_url) && !empty($config->database);
                            @endphp
                            <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div>
                                    @if ($isConfigured)
                                        <button type="button" 
                                                @click="$dispatch('open-sync-all-modal')" 
                                                class="inline-flex items-center space-x-2 py-2.5 px-4 border border-purple-200 text-sm font-semibold rounded-lg text-purple-700 bg-purple-50 hover:bg-purple-100 hover:text-purple-800 transition-all shadow-sm hover:shadow-md transform hover:-translate-y-0.5 active:scale-95 focus:outline-none cursor-pointer">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>Sync Everything From SAP</span>
                                        </button>
                                    @else
                                        <button type="button" 
                                                disabled 
                                                title="Please configure and save SAP Service Layer settings first." 
                                                class="inline-flex items-center space-x-2 py-2.5 px-4 border border-gray-200 text-sm font-medium rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>Sync Everything From SAP</span>
                                        </button>
                                    @endif
                                </div>

                                <button type="submit" 
                                        :disabled="!isDirty || isSubmitting"
                                        :title="!isDirty ? 'No changes detected in configuration settings.' : 'Save Configuration'"
                                        :class="!isDirty || isSubmitting 
                                            ? 'border border-gray-200 text-gray-400 bg-gray-100 cursor-not-allowed shadow-none' 
                                            : 'border border-transparent text-white bg-indigo-600 hover:bg-indigo-500 hover:shadow-lg transform hover:-translate-y-0.5 active:scale-95 cursor-pointer shadow-md'"
                                        class="inline-flex items-center space-x-2 py-2.5 px-6 text-sm font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg x-show="!isDirty && !isSubmitting" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="isSubmitting ? 'Testing & Saving...' : 'Save Configuration'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Scheduler Configuration Panel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form action="{{ route('config.update') }}" method="POST">
                        @csrf
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Background Scheduler Settings</h2>
                            <p class="text-sm text-gray-500 mb-6">Configure the background task that automatically pushes Draft Purchase Requests to SAP.</p>
                            
                            <div class="space-y-6">
                                <div class="flex items-center">
                                    <input type="checkbox" name="scheduler_active" id="scheduler_active" value="1" {{ old('scheduler_active', $config->scheduler_active ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="scheduler_active" class="ml-2 block text-sm font-medium text-gray-900">
                                        Enable Background Scheduler
                                    </label>
                                </div>

                                <div>
                                    <label for="scheduler_interval" class="block text-sm font-medium text-gray-700">Sync Interval (Minutes)</label>
                                    <div class="mt-1">
                                        <input type="number" name="scheduler_interval" id="scheduler_interval" min="1" value="{{ old('scheduler_interval', $config->scheduler_interval ?? 5) }}" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">How often the background job should run (e.g., 5 for every 5 minutes).</p>
                                </div>
                                
                                <!-- Hidden fields to preserve SAP config when submitting this form -->
                                <input type="hidden" name="base_url" value="{{ $config->base_url }}">
                                <input type="hidden" name="database" value="{{ $config->database }}">
                                <input type="hidden" name="period_indicator" value="{{ $config->period_indicator }}">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 text-right sm:rounded-b-lg">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Scheduler Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- User Settings Panel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <form action="{{ route('config.updatePersonal') }}" method="POST">
                        @csrf
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Personal Settings</h2>
                            <p class="text-sm text-gray-500 mb-6">These settings only apply to your own account.</p>
                            
                            <div class="space-y-6">
                                <div class="flex items-center">
                                    <input type="checkbox" name="debug_mode" id="debug_mode" value="1" {{ auth()->user()->debug_mode ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="debug_mode" class="ml-2 block text-sm font-medium text-gray-900">
                                        Enable SAP Debug Mode
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">When enabled, any active SAP sync triggered by you will display a popup containing the raw URL, HTTP method, request body, and response.</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 text-right sm:rounded-b-lg">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Personal Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <x-period-modal />
    <x-database-modal />
    <x-sync-all-modal />
</x-app-layout>
