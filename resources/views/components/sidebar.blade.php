{{-- Sidebar Navigation Component --}}
@props(['active' => null])

<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white/10 backdrop-blur-xl border-r border-white/20 transform -translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="flex flex-col h-full">
        {{-- Sidebar Header --}}
        <div class="flex items-center justify-between p-6 border-b border-white/20">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-white">Menu</span>
            </div>
            <button onclick="toggleSidebar()" class="text-white hover:text-indigo-300 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Navigation Links --}}
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <!-- {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'dashboard' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium">Dashboard</span>
            </a> -->

            {{-- IoT Status --}}
            <a href="{{ route('iot.status') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'iot' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                <span class="font-medium">IoT Status</span>
            </a>

            {{-- Devices --}}
            <a href="{{ route('devices.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'devices' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                <span class="font-medium">Devices</span>
            </a>

            {{-- Live Camera --}}
            <a href="{{ route('camera.live') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'camera' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium">Live Camera</span>
            </a>

            {{-- Notifications --}}
            <a href="{{ route('notifications.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'notifications' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="font-medium">Notifications</span>
            </a>

            {{-- Historical Data --}}
            <a href="{{ route('history.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'history' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="font-medium">Historical Data</span>
            </a>

            @if(auth()->user()->isAdmin())
                {{-- Manage Users (Admin Only) --}}
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'users' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Manage Users</span>
                </a>

                {{-- Manage Devices (Admin Only) --}}
                <a href="{{ route('admin.devices.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $active === 'admin-devices' ? 'bg-indigo-500/20 text-white border border-indigo-500/50' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }} transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    <span class="font-medium">Manage Devices</span>
                </a>
            @endif
        </nav>

        {{-- Sidebar Footer (Logout) --}}
        <div class="p-4 border-t border-white/20">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-red-500/20 hover:bg-red-500/30 border border-red-500/50 text-red-100 rounded-lg transition-all duration-300 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Overlay --}}
<div id="overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>
