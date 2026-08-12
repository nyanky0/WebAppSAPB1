<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Warehouse</h1>
                <p class="mt-1 text-sm text-gray-500">Add a new Warehouse to local database.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('warehouses.index') }}" class="inline-flex items-center rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('warehouses.store') }}" method="POST" class="mt-8 space-y-8">
            @csrf
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="whs_code" class="block text-sm font-medium leading-6 text-gray-900">Warehouse Code</label>
                            <div class="mt-2">
                                <input type="text" name="whs_code" id="whs_code" value="{{ old('whs_code') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required placeholder="e.g. 01, WH01">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="whs_name" class="block text-sm font-medium leading-6 text-gray-900">Warehouse Name</label>
                            <div class="mt-2">
                                <input type="text" name="whs_name" id="whs_name" value="{{ old('whs_name') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required placeholder="e.g. Main Warehouse">
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="location" class="block text-sm font-medium leading-6 text-gray-900">Location</label>
                            <div class="mt-2">
                                <input type="text" name="location" id="location" value="{{ old('location') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. Jakarta Building A">
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="bin_enabled" name="bin_enabled" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="bin_enabled" class="font-medium text-gray-900 select-none">Enable Bin Locations</label>
                                    <p class="text-gray-500">Enable bin location management for this warehouse.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8">
                    <div class="flex items-center">
                        <input id="instant_sync" name="instant_sync" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="instant_sync" class="ml-2 text-sm font-medium text-gray-700 select-none">Instant Sync to SAP</label>
                    </div>

                    <div class="flex items-center gap-x-3">
                        <a href="{{ route('warehouses.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">Cancel</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                            Save Warehouse
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
