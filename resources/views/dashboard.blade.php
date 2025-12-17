<x-layout title="Dashboard" active="dashboard">
    
    {{-- Navbar User Info --}}
    <x-slot name="navbarSlot">
        <div class="hidden md:flex items-center space-x-4">
            <div class="text-right">
                <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-indigo-300">{{ auth()->user()->email }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                <span class="text-white font-bold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </div>
    </x-slot>

    {{-- Main Content --}}
    <div class="space-y-8">
        {{-- Welcome Section --}}
        <div class="bg-gradient-to-r from-indigo-500/20 to-purple-500/20 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
            <h1 class="text-4xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-indigo-200">Here's what's happening with your IoT devices today.</p>
        </div>

        {{-- Quick Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Active Devices --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-500/20 rounded-xl">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-white">{{ $stats['active_devices'] ?? 0 }}</span>
                </div>
                <p class="text-sm text-indigo-300 font-medium">Active Devices</p>
                <p class="text-xs text-indigo-400 mt-1">Online & reporting</p>
            </div>

            {{-- Total Sensors --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-500/20 rounded-xl">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-white">{{ $stats['total_sensors'] ?? 0 }}</span>
                </div>
                <p class="text-sm text-indigo-300 font-medium">Total Sensors</p>
                <p class="text-xs text-indigo-400 mt-1">Deployed sensors</p>
            </div>

            {{-- Alerts Today --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-yellow-500/20 rounded-xl">
                        <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-white">{{ $stats['alerts_today'] ?? 0 }}</span>
                </div>
                <p class="text-sm text-indigo-300 font-medium">Alerts Today</p>
                <p class="text-xs text-indigo-400 mt-1">Threshold breaches</p>
            </div>

            {{-- Data Points --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-500/20 rounded-xl">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold text-white">{{ number_format($stats['data_points'] ?? 0) }}</span>
                </div>
                <p class="text-sm text-indigo-300 font-medium">Data Points</p>
                <p class="text-xs text-indigo-400 mt-1">Total records</p>
            </div>
        </div>

        {{-- Recent Activity & Quick Actions Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Activity --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Recent Activity
                </h2>
                <div class="space-y-4">
                    @forelse($recentActivity ?? [] as $activity)
                        <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-white/5 transition-colors">
                            <div class="p-2 bg-indigo-500/20 rounded-lg">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-white">{{ $activity['message'] ?? 'No message' }}</p>
                                <p class="text-xs text-indigo-300 mt-1">{{ $activity['timestamp'] ?? now()->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-8">No recent activity</p>
                    @endforelse
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Quick Actions
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('iot.status') }}" class="p-4 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 hover:from-indigo-500/30 hover:to-purple-500/30 rounded-xl border border-indigo-500/30 transition-all duration-300 text-center">
                        <svg class="w-8 h-8 text-indigo-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-sm font-medium text-white">View Status</p>
                    </a>
                    <a href="{{ route('camera.live') }}" class="p-4 bg-gradient-to-br from-green-500/20 to-emerald-500/20 hover:from-green-500/30 hover:to-emerald-500/30 rounded-xl border border-green-500/30 transition-all duration-300 text-center">
                        <svg class="w-8 h-8 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm font-medium text-white">Live Camera</p>
                    </a>
                    <a href="{{ route('history.index') }}" class="p-4 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 hover:from-blue-500/30 hover:to-cyan-500/30 rounded-xl border border-blue-500/30 transition-all duration-300 text-center">
                        <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-sm font-medium text-white">View History</p>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="p-4 bg-gradient-to-br from-orange-500/20 to-red-500/20 hover:from-orange-500/30 hover:to-red-500/30 rounded-xl border border-orange-500/30 transition-all duration-300 text-center">
                        <svg class="w-8 h-8 text-orange-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-sm font-medium text-white">Notifications</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

</x-layout>
