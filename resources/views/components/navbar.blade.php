{{-- Navbar Component --}}
@props(['title' => 'Dashboard'])

<nav class="bg-white/10 backdrop-blur-xl border-b border-white/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-4">
                <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-white/10 transition-colors text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-base sm:text-xl font-bold text-white truncate max-w-[150px] sm:max-w-none">{{ $title }}</h1>
            </div>
            <div class="flex items-center space-x-3">
                {{ $slot }}
            </div>
        </div>
    </div>
</nav>
