<x-layout title="IoT Device Status" active="iot">

    {{-- Chart.js in head --}}
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    </x-slot>

    {{-- Device Status Badge & Selector in Navbar --}}
    <x-slot name="navbarSlot">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            {{-- Device Selector --}}
            @if(isset($devices) && $devices->count() > 1)
            <div class="relative flex-shrink-0">
                <select id="deviceSelector" onchange="switchDevice(this.value)" 
                    style="background-color: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px);" 
                    class="appearance-none border border-white/30 rounded-lg px-2 sm:px-4 py-1.5 sm:py-2 pr-8 sm:pr-10 text-xs sm:text-sm text-white font-medium focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 cursor-pointer hover:bg-white/20 transition-all duration-200 max-w-[140px] sm:max-w-none">
                    @foreach($devices as $dev)
                        <option value="{{ $dev->device_id }}" {{ $dev->device_id === $device->device_id ? 'selected' : '' }} 
                            style="background-color: #1f2937; color: white; padding: 8px;">
                            {{ $dev->name }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            @endif
            
            {{-- Status Badge --}}
            <div id="deviceStatus" class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full {{ $device->isOnline() ? 'bg-green-500/20 border border-green-500/50' : 'bg-gray-500/20 border border-gray-500/50' }}">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $device->isOnline() ? 'bg-green-400 animate-pulse' : 'bg-gray-400' }}"></div>
                <span class="text-xs sm:text-sm {{ $device->isOnline() ? 'text-green-100' : 'text-gray-100' }} font-medium">{{ $device->isOnline() ? 'Online' : 'Offline' }}</span>
            </div>
        </div>
    </x-slot>

    {{-- Main Content --}}
    {{-- Device Info Card --}}
    <div class="mb-8 bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
            </svg>
            Device Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-indigo-300">Device ID</p>
                <p class="text-lg text-white font-mono">{{ $device->device_id }}</p>
            </div>
            <div>
                <p class="text-sm text-indigo-300">Control Mode</p>
                <p class="text-lg text-white capitalize">{{ $device->control_mode }}</p>
            </div>
            <div>
                <p class="text-sm text-indigo-300">Last Update</p>
                <p class="text-lg text-white" id="lastUpdate">{{ $device->last_seen?->diffForHumans() ?? 'Never' }}</p>
            </div>
        </div>
    </div>

    {{-- Real-time Charts --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-4">Real-time Monitoring</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Temperature Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Temperature (°C)</h3>
                <div class="h-64">
                    <canvas id="tempChart"></canvas>
                </div>
            </div>

            {{-- Humidity Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Humidity (%)</h3>
                <div class="h-64">
                    <canvas id="humidityChart"></canvas>
                </div>
            </div>

            {{-- Water Level Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Water Level</h3>
                <div class="h-64">
                    <canvas id="waterChart"></canvas>
                </div>
            </div>

            {{-- Soil Moisture Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Soil Moisture</h3>
                <div class="h-64">
                    <canvas id="soilChart"></canvas>
                </div>
            </div>

            {{-- CO₂ PPM Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">CO₂ PPM</h3>
                <div class="h-64">
                    <canvas id="odorChart"></canvas>
                </div>
            </div>

            {{-- Load Cell Chart --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">Weight (g)</h3>
                <div class="h-64">
                    <canvas id="weightChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Sensor Cards --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-4">Current Sensor Readings</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- DHT22 Sensor --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"></path>
                    </svg>
                    DHT22
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Temperature</p>
                        <p class="text-2xl font-bold text-white" id="dht_temp">--°C</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Humidity</p>
                        <p class="text-2xl font-bold text-white" id="dht_rh">--%</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Heat Index</p>
                        <p class="text-lg font-bold text-white" id="dht_hi">--°C</p>
                    </div>
                </div>
            </div>

            {{-- MQ135 Sensor --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    MQ135 (Air Quality)
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">CO₂ PPM</p>
                        <p class="text-2xl font-bold text-white" id="co2_ppm">--</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Voltage Pin</p>
                        <p class="text-lg text-white" id="mq_vpin">-- V</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Gas Voltage</p>
                        <p class="text-lg text-white" id="mq_vgas">-- V</p>
                    </div>
                </div>
            </div>

            {{-- Water Level Sensor --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Water Level
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Level</p>
                        <p class="text-2xl font-bold text-white" id="water_level">--%</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Zone</p>
                        <p class="text-lg font-semibold text-white" id="water_zone">--</p>
                    </div>
                </div>
            </div>

            {{-- Soil Moisture Sensor --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Soil Moisture
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Moisture</p>
                        <p class="text-2xl font-bold text-white" id="soil_pct">--%</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Zone</p>
                        <p class="text-lg font-semibold text-white" id="soil_zone">--</p>
                    </div>
                </div>
            </div>

            {{-- Load Cell Sensor --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                    </svg>
                    Load Cell
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Weight</p>
                        <p class="text-2xl font-bold text-white" id="weight">-- g</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actuator Status (Read-only Display) --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-4">Actuator Status</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Fan Status --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Fan
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Status</p>
                        <p class="text-xl font-bold text-white" id="fanStatus">OFF</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Duty Cycle</p>
                        <p class="text-2xl font-bold text-white" id="fanDuty">0%</p>
                    </div>
                </div>
            </div>

            {{-- Humidifier Status --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                    Humidifier
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Mode</p>
                        <p class="text-lg font-bold text-white capitalize" id="humidMode">--</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">State</p>
                        <p class="text-xl font-bold text-white" id="humidState">OFF</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Duty Cycle</p>
                        <p class="text-2xl font-bold text-white" id="humidDuty">0%</p>
                    </div>
                </div>
            </div>

            {{--Heater Status --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                    </svg>
                    Heater
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-indigo-300">Mode</p>
                        <p class="text-lg font-bold text-white capitalize" id="heaterMode">--</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">State</p>
                        <p class="text-xl font-bold text-white" id="heaterState">OFF</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-300">Duty Cycle</p>
                        <p class="text-2xl font-bold text-white" id="heaterDuty">0%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Feeder Control Panel --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Feeder Control
            </h2>
            <div class="space-y-6">
                {{-- Input Amount --}}
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Servo Cycles (Open/Close)</label>
                    <input type="number" id="feedAmount" value="1" min="1" max="10" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-indigo-400 mt-2">1 cycle = servo membuka → menutup feeder sekali</p>
                </div>

                {{-- Buttons Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button onclick="feederOpen()" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span>Open</span>
                    </button>
                    <button onclick="feederClose()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Close</span>
                    </button>
                    <button onclick="feederFeed()" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                        </svg>
                        <span>Feed</span>
                    </button>
                    <button onclick="feederCancel()" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Command Interface --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6">Send Custom Command</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Device ID</label>
                    <input type="text" id="cmdDeviceId" value="{{ $device->device_id }}" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono">
                </div>
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Command (JSON)</label>
                    <textarea id="cmdJson" rows="4" placeholder='{"command":"status"}' class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono text-sm"></textarea>
                </div>
                <button onclick="sendCustomCommand()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all duration-300">
                    Send Command
                </button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let charts = {};

            // Device switcher function
            function switchDevice(deviceId) {
                window.location.href = '/iot/status?device_id=' + encodeURIComponent(deviceId);
            }

            // Initialize all charts
            function initCharts() {
                const chartConfig = (color) => ({
                    type: 'line',
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { ticks: { color: 'rgb(199, 210, 254)' }, grid: { color: 'rgba(255, 255, 255, 0.1)' } },
                            x: { ticks: { color: 'rgb(199, 210, 254)', maxRotation: 0 }, grid: { color: 'rgba(255, 255, 255, 0.1)' } }
                        }
                    },
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            borderColor: color,
                            backgroundColor: color.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                            tension: 0.4,
                            fill: true
                        }]
                    }
                });

                charts.temp = new Chart(document.getElementById('tempChart'), chartConfig('rgb(249, 115, 22)'));
                charts.humidity = new Chart(document.getElementById('humidityChart'), chartConfig('rgb(59, 130, 246)'));
                charts.water = new Chart(document.getElementById('waterChart'), chartConfig('rgb(34, 211, 238)'));
                charts.soil = new Chart(document.getElementById('soilChart'), chartConfig('rgb(217, 119, 6)'));
                charts.odor = new Chart(document.getElementById('odorChart'), chartConfig('rgb(16, 185, 129)'));
                charts.weight = new Chart(document.getElementById('weightChart'), chartConfig('rgb(168, 85, 247)'));
            }

            function updateSensorData() {
                const deviceId = '{{ $device->device_id }}';
                fetch(`/iot/sensor-data?device_id=${encodeURIComponent(deviceId)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || !data.sensors) {
                            console.warn('No sensor data received');
                            return;
                        }
                        
                        const sensors = data.sensors;
                        
                        // DHT22 - map to OLD element IDs
                        document.getElementById('dht_temp').textContent = (sensors.temperature || '--') + '°C';
                        document.getElementById('dht_rh').textContent = (sensors.humidity || '--') + '%';
                        document.getElementById('dht_hi').textContent = (sensors.heat_index || '--') + '°C';

                        // MQ135 - map to OLD element IDs
                        document.getElementById('co2_ppm').textContent = sensors.co2_ppm || '--';  
                        document.getElementById('mq_vpin').textContent = (sensors.mq_vpin || sensors.vpin || '--') + ' V';
                        document.getElementById('mq_vgas').textContent = (sensors.mq_vgas || sensors.vgas || '--') + ' V';

                        // Water Level - map to OLD element IDs
                        document.getElementById('water_level').textContent = (sensors.water_level || '--') + '%';
                        document.getElementById('water_zone').textContent = sensors.water_zone || sensors.wl_zone || '--';

                        // Soil Moisture - map to OLD element IDs
                        document.getElementById('soil_pct').textContent = (sensors.soil_pct || sensors.soil_moisture || '--') + '%';
                        document.getElementById('soil_zone').textContent = sensors.soil_zone || '--';

                        // Load Cell - map to OLD element IDs
                        document.getElementById('weight').textContent = (sensors.weight || '--') + ' g';

                        // Actuator Status - Update UI elements
                        if (data.actuators) {
                            const act = data.actuators;
                            
                            // Fan
                            document.getElementById('fanStatus').textContent = (act.fan_duty_pct > 0) ? 'ON' : 'OFF';
                            document.getElementById('fanDuty').textContent = Math.round(act.fan_duty_pct || 0) + '%';
                            
                            // Humidifier
                            document.getElementById('humidMode').textContent = act.humidifier_on ? 'ON' : 'OFF';
                            document.getElementById('humidState').textContent = act.humidifier_on ? 'ON' : 'OFF';
                            document.getElementById('humidDuty').textContent = Math.round(act.humid_duty_pct || 0) + '%';
                            
                            // Heater  
                            document.getElementById('heaterMode').textContent = act.heater_on ? 'ON' : 'OFF';
                            document.getElementById('heaterState').textContent = act.heater_on ? 'ON' : 'OFF';
                            document.getElementById('heaterDuty').textContent = Math.round(act.heater_duty_pct || 0) + '%';
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }


            function updateCharts() {
                const deviceId = '{{ $device->device_id }}';
                fetch(`/iot/historical-data?device_id=${encodeURIComponent(deviceId)}&hours=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || data.length === 0) {
                            console.warn('No historical data');
                            return;
                        }
                        
                        const labels = data.map(d => new Date(d.created_at).toLocaleTimeString());
                        
                        // Update each chart
                        if (charts.temp) {
                            charts.temp.data.labels = labels;
                            charts.temp.data.datasets[0].data = data.map(d => d.temperature || 0);
                            charts.temp.update();
                        }
                        
                        if (charts.humidity) {
                            charts.humidity.data.labels = labels;
                            charts.humidity.data.datasets[0].data = data.map(d => d.humidity || 0);
                            charts.humidity.update();
                        }
                        
                        if (charts.water) {
                            charts.water.data.labels = labels;
                            charts.water.data.datasets[0].data = data.map(d => d.water_level || 0);
                            charts.water.update();
                        }
                        
                        if (charts.soil) {
                            charts.soil.data.labels = labels;
                            charts.soil.data.datasets[0].data = data.map(d => d.soil_moisture || d.soil_pct || 0);
                            charts.soil.update();
                        }
                        
                        if (charts.odor) {
                            charts.odor.data.labels = labels;
                            charts.odor.data.datasets[0].data = data.map(d => d.co2_ppm || 0);
                            charts.odor.update();
                        }
                        
                        if (charts.weight) {
                            charts.weight.data.labels = labels;
                            charts.weight.data.datasets[0].data = data.map(d => d.weight || 0);
                            charts.weight.update();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function sendCommand(command) {
                fetch('/iot/send-command', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(command)
                })
                .then(response => response.json())
                .then(data => alert(data.message || 'Command sent'))
                .catch(error => console.error('Error:', error));
            }


            // Feeder control functions - ESP32 format
            function feederOpen() {
                const deviceId = '{{ $device->device_id }}';
                fetch('/iot/control-feeder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ device_id: deviceId, action: 'open' })
                })
                .then(response => response.json())
                .then(data => alert(data.message || 'Feeder opened'))
                .catch(error => console.error('Error:', error));
            }
            
            function feederClose() {
                const deviceId = '{{ $device->device_id }}';
                fetch('/iot/control-feeder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ device_id: deviceId, action: 'close' })
                })
                .then(response => response.json())
                .then(data => alert(data.message || 'Feeder closed'))
                .catch(error => console.error('Error:', error));
            }
            
            function feederFeed() {
                const deviceId = '{{ $device->device_id }}';
                const amount = parseInt(document.getElementById('feedAmount').value) || 1;
                fetch('/iot/control-feeder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ device_id: deviceId, action: 'feed', amount: amount })
                })
                .then(response => response.json())
                .then(data => alert(data.message || `Feeding ${amount} times`))
                .catch(error => console.error('Error:', error));
            }
            
            function feederCancel() {
                const deviceId = '{{ $device->device_id }}';
                fetch('/iot/control-feeder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ device_id: deviceId, action: 'cancel' })
                })
                .then(response => response.json())
                .then(data => alert(data.message || 'Feeding cancelled'))
                .catch(error => console.error('Error:', error));
            }

            function sendCustomCommand() {
                try {
                    const deviceId = '{{ $device->device_id }}';
                    const commandJson = document.getElementById('cmdJson').value;
                    
                    // Validate JSON
                    JSON.parse(commandJson);
                    
                    // Send to controller with device_id and command
                    sendCommand({ 
                        device_id: deviceId,
                        command: commandJson  // Send as string, controller will parse
                    });
                } catch (e) {
                    alert('Invalid JSON format: ' + e.message);
                }
            }

            // Initialize
            initCharts();
            updateSensorData();
            updateCharts();

            // Auto-refresh every 5 seconds
            setInterval(() => {
                updateSensorData();
                updateCharts();
            }, 5000);
        </script>
    </x-slot>

</x-layout>
