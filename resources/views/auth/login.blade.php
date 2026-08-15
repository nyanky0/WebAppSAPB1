<x-guest-layout>
    <!-- Header Logo & App Title -->
    <div class="flex flex-col items-center justify-center mb-6 space-y-2">
        <div class="flex items-center space-x-2">
            <img src="{{ asset('images/sap-logo.svg') }}" alt="SAP Logo" class="h-7 w-auto object-contain drop-shadow-md">
            <span class="text-xs font-semibold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full">AddOn</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900 tracking-tight text-center mt-1">IBT Request Fulfillment</h1>
        <p class="text-xs text-gray-500">Sign in to your account to continue</p>
    </div>

    <!-- Login Form -->
    <form method="POST" action="/login">
        @csrf

        <!-- Username -->
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input id="username" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input id="password" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" type="password" name="password" required autocomplete="current-password" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Log in
            </button>
        </div>
    </form>
</x-guest-layout>
