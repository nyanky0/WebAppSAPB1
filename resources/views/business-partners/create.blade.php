<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Business Partner</h1>
                <p class="mt-1 text-sm text-gray-500">Add a new Business Partner to the local database and optionally sync it to SAP Business One immediately.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('business-partners.index') }}" class="inline-flex items-center rounded-md bg-white px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('business-partners.store') }}" method="POST" class="mt-8 space-y-8">
            @csrf
            
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                <div class="px-4 py-6 sm:p-8">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        
                        <div class="sm:col-span-3">
                            <label for="bp_code" class="block text-sm font-medium leading-6 text-gray-900">BP Code</label>
                            <div class="mt-2">
                                <input type="text" name="bp_code" id="bp_code" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">BP Name</label>
                            <div class="mt-2">
                                <input type="text" name="name" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="type" class="block text-sm font-medium leading-6 text-gray-900">BP Type</label>
                            <div class="mt-2">
                                <select name="type" id="type" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" required>
                                    <option value="Vendor">Vendor (Supplier)</option>
                                    <option value="Customer">Customer</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-6 border-t border-gray-200 pt-6 mt-2">
                            <h3 class="text-sm font-medium leading-6 text-gray-900 mb-4">Contact Persons (Optional)</h3>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="contact_person_1" class="block text-sm font-medium leading-6 text-gray-900">Contact Person 1</label>
                            <div class="mt-2">
                                <input type="text" name="contact_person_1" id="contact_person_1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="contact_person_2" class="block text-sm font-medium leading-6 text-gray-900">Contact Person 2</label>
                            <div class="mt-2">
                                <input type="text" name="contact_person_2" id="contact_person_2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
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
                    <a href="{{ route('business-partners.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">Cancel</a>
                    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-500 transition-all">Save Business Partner</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
