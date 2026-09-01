<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'IBT Request Fulfillment' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50 relative min-h-screen">
    <!-- Mesh / Gradient Background -->
    <div class="fixed inset-0 -z-10 bg-gradient-to-br from-indigo-100 via-white to-purple-100 opacity-80 pointer-events-none"></div>
    <div class="fixed inset-0 -z-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSIvPgo8cGF0aCBkPSJNMCAwdjRoNHYtNEgweiIgZmlsbD0iI2ZmZiIgZmlsbC1vcGFjaXR5PSIwLjAyIi8+Cjwvc3ZnPg==')] opacity-40 pointer-events-none"></div>

    <x-flash-messages />

    <div class="relative z-10 min-h-screen flex flex-col justify-center items-center px-4 py-8">
        <div class="w-full max-w-md bg-white/75 backdrop-blur-xl border border-white/40 shadow-2xl overflow-hidden rounded-2xl p-6 sm:p-8 transition-all hover:shadow-indigo-500/10">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
