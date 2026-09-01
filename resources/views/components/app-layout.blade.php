<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'IBT Request Fulfillment' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Add Alpine.js and plugins for interactive elements -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50 relative min-h-screen">
    <!-- Mesh / Gradient Background -->
    <div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-100 via-white to-purple-100 opacity-80 pointer-events-none"></div>
    <div class="fixed inset-0 -z-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSIvPgo8cGF0aCBkPSJNMCAwdjRoNHYtNEgweiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIwLjAyIi8+Cjwvc3ZnPg==')] opacity-40 pointer-events-none"></div>

    <x-flash-messages />

    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden bg-white/40 backdrop-blur-3xl">
        
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Header -->
            <header class="sticky top-0 z-30 flex items-center justify-between px-6 py-4 bg-white/60 backdrop-blur-md border-b border-white/40 shadow-sm transition-all">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-indigo-600 transition-colors focus:outline-none transform hover:scale-110 active:scale-95">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <span class="text-sm font-medium text-gray-700">Welcome, {{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                </div>
            </header>

            <!-- Main area -->
            <main class="w-full px-6 py-8 mx-auto">
                {{ $slot }}
            </main>
        </div>
        <!-- Global Modals -->
        <x-debug-modal />
    </div>
</body>
</html>
