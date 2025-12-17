{{-- Main Layout Component --}}
@props(['title' => 'Dashboard', 'active' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    
    {{-- Sidebar --}}
    <x-sidebar :active="$active" />

    {{-- Main Content Wrapper --}}
    <div id="mainContent" class="transition-all duration-300">
        {{-- Navbar --}}
        <x-navbar :title="$title">
            {{ $navbarSlot ?? '' }}
        </x-navbar>

        {{-- Main Content --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </div>

    {{-- Common Scripts --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

    {{ $scripts ?? '' }}
</body>
</html>
