<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Item</h1>
                <p class="mt-1 text-sm text-gray-500">Add a new Item to the local database and optionally sync it to SAP Business One immediately.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('items.index') }}" class="inline-flex items-center rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('items.store') }}" method="POST" class="mt-8 space-y-8">
            @csrf
            
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        
                        <div class="sm:col-span-3">
                            <label for="item_code" class="block text-sm font-medium leading-6 text-gray-900">Item Code</label>
                            <div class="mt-2">
                                <input type="text" name="item_code" id="item_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="item_name" class="block text-sm font-medium leading-6 text-gray-900">Item Name</label>
                            <div class="mt-2">
                                <input type="text" name="item_name" id="item_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="foreign_name" class="block text-sm font-medium leading-6 text-gray-900">Foreign Name (Optional)</label>
                            <div class="mt-2">
                                <input type="text" name="foreign_name" id="foreign_name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="uom" class="block text-sm font-medium leading-6 text-gray-900">Inventory UOM</label>
                            <div class="mt-2">
                                <input type="text" name="uom" id="uom" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="item_group" class="block text-sm font-medium leading-6 text-gray-900">Item Group</label>
                            <div class="mt-2">
                                <select name="item_group" id="item_group" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    <option value="">No Group</option>
                                    @foreach($itemGroups as $group)
                                        <option value="{{ $group }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <div class="relative flex gap-x-3">
                                <div class="flex h-6 items-center">
                                    <input id="is_active" name="is_active" type="checkbox" checked value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="is_active" class="font-medium text-gray-900">Active</label>
                                    <p class="text-gray-500">Determine if this item is currently active in SAP.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    <input type="hidden" name="instant_sync" value="0">
                    <input id="instant_sync" name="instant_sync" value="1" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="instant_sync" class="text-sm font-medium text-gray-700 cursor-pointer select-none">Instant Sync to SAP</label>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('items.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-500 transition-all">Save Item</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
