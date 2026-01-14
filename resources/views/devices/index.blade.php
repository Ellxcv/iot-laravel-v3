<x-layout title="Device Management" active="devices">

    {{-- Main Content --}}
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-500/50 text-green-100 px-6 py-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-500/50 text-red-100 px-6 py-4 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Add Device Form --}}
    <div class="mb-8 bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
        <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Device
        </h2>
        
        <form method="POST" action="{{ route('devices.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Device Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Living Room Sensor">
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Device ID</label>
                    <input type="text" name="device_id" value="{{ old('device_id') }}" required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="ESP32_001">
                    @error('device_id')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Device Type</label>
                    <select name="type" required
                            class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                        <option value="sensor" {{ old('type') == 'sensor' ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">Sensor</option>
                        <option value="camera" {{ old('type') == 'camera' ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">Camera</option>
                        <option value="controller" {{ old('type') == 'controller' ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">Controller</option>
                    </select>
                    @error('type')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                        <span class="text-white font-medium">Active (Enabled)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Add Device</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Device Offline Notification Settings --}}
    <div class="mb-8 bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
        <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            Device Offline Notifications
        </h2>

        @php
            $offlineSetting = auth()->user()->deviceOfflineSetting;
        @endphp

        <form method="POST" action="{{ route('devices.offline-settings.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
            
            <div class="bg-white/5 rounded-xl p-4 space-y-4">
                <div class="flex items-start space-x-4">
                    <svg class="w-12 h-12 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white mb-2">Offline Alert System</h3>
                        <p class="text-sm text-indigo-200">
                            Get notified via Telegram and Firebase when your devices haven't sent updates for a specified time period.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="flex items-center space-x-3 cursor-pointer p-3 rounded-lg hover:bg-white/5 transition-colors">
                            <input type="checkbox" name="notification_enabled" value="1" 
                                   {{ $offlineSetting && $offlineSetting->notification_enabled ? 'checked' : '' }}
                                   class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                            <div>
                                <span class="text-white font-medium block">Enable Notifications</span>
                                <span class="text-xs text-indigo-300">Send alerts when devices go offline</span>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="text-sm text-indigo-300 mb-2 block">Offline Timeout (Minutes)</label>
                        <input type="number" name="offline_timeout_minutes" 
                               min="1" max="1440" 
                               value="{{ $offlineSetting ? $offlineSetting->offline_timeout_minutes : 5 }}" 
                               required
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="5">
                        <p class="text-xs text-indigo-400 mt-1">
                            Device considered offline if no update received for this many minutes
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-white/10">
                    <div class="text-sm text-indigo-300">
                        <span class="text-white font-semibold">Note:</span> Notifications sent max once per hour to prevent spam
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save Settings</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Device List --}}
    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
        <h2 class="text-2xl font-bold text-white mb-6 flex items-center justify-between">
            <span class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                Device List
            </span>
            <span class="text-sm text-indigo-300 font-normal">{{ count($devices) }} device(s)</span>
        </h2>

        @if(count($devices) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Name</th>
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Device ID</th>
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Type</th>
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Status</th>
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Active</th>
                            <th class="text-left text-sm text-indigo-300 font-semibold py-3 px-4">Last Seen</th>
                            <th class="text-right text-sm text-indigo-300 font-semibold py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-4 px-4">
                                    <span class="text-white font-medium">{{ $device->name }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <code class="text-sm text-indigo-200 bg-white/10 px-2 py-1 rounded">{{ $device->deviceId }}</code>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($device->type === 'sensor') bg-blue-500/20 text-blue-300
                                        @elseif($device->type === 'camera') bg-purple-500/20 text-purple-300
                                        @else bg-green-500/20 text-green-300
                                        @endif">
                                        {{ ucfirst($device->type) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $device->isOnline() ? 'bg-green-500/20 text-green-300' : 'bg-gray-500/20 text-gray-300' }}">
                                        {{ $device->isOnline() ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <button onclick="toggleStatus({{ $device->id }}, {{ $device->isActive ? 'false' : 'true' }})"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-300
                                                {{ $device->isActive ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-600 hover:bg-gray-700 text-white' }}">
                                        {{ $device->isActive ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="py-4 px-4 text-sm text-indigo-200">
                                    {{ $device->lastSeen ? $device->lastSeen->format('Y-m-d H:i') : 'Never' }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="openThresholdModal({{ $device->id }}, '{{ $device->name }}')" 
                                                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-all duration-300">
                                            Thresholds
                                        </button>
                                        <form method="POST" action="{{ route('devices.destroy', $device->id) }}" class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this device?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-all duration-300">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4 text-indigo-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                <p class="text-indigo-300 text-lg">No devices found</p>
                <p class="text-indigo-400 text-sm mt-2">Add your first device using the form above</p>
            </div>
        @endif
    </div>

    {{-- Threshold Settings Modal --}}
    <div id="thresholdModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4">
        <div class="bg-gradient-to-br from-indigo-900 to-purple-900 rounded-2xl border border-white/20 p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-white" id="modalTitle">Sensor Thresholds</h3>
                <button onclick="closeThresholdModal()" class="text-white/60 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="thresholdContent" class="space-y-6">
                <!-- Temperature -->
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">🌡️</span>
                            <h4 class="text-lg font-bold text-white">Temperature</h4>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-indigo-200">Enabled</span>
                            <input type="checkbox" id="temp_enabled" class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Min (°C)</label>
                            <input type="number" step="0.1" id="temp_min" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Max (°C)</label>
                            <input type="number" step="0.1" id="temp_max" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-indigo-200 mb-1 block">Cooldown (minutes)</label>
                        <input type="number" id="temp_cooldown" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                    </div>
                </div>

                <!-- Humidity -->
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">💧</span>
                            <h4 class="text-lg font-bold text-white">Humidity</h4>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-indigo-200">Enabled</span>
                            <input type="checkbox" id="hum_enabled" class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Min (%)</label>
                            <input type="number" step="0.1" id="hum_min" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Max (%)</label>
                            <input type="number" step="0.1" id="hum_max" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-indigo-200 mb-1 block">Cooldown (minutes)</label>
                        <input type="number" id="hum_cooldown" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                    </div>
                </div>

                <!-- Air Quality -->
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">💨</span>
                            <h4 class="text-lg font-bold text-white">Air Quality</h4>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-indigo-200">Enabled</span>
                            <input type="checkbox" id="air_enabled" class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Min (PPM)</label>
                            <input type="number" step="0.1" id="air_min" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white" placeholder="Optional">
                        </div>
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Max (PPM)</label>
                            <input type="number" step="0.1" id="air_max" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-indigo-200 mb-1 block">Cooldown (minutes)</label>
                        <input type="number" id="air_cooldown" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                    </div>
                </div>

                <!-- Water Level -->
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">💧</span>
                            <h4 class="text-lg font-bold text-white">Water Level</h4>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-indigo-200">Enabled</span>
                            <input type="checkbox" id="water_enabled" class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Min (%)</label>
                            <input type="number" step="0.1" id="water_min" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Max (%)</label>
                            <input type="number" step="0.1" id="water_max" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-indigo-200 mb-1 block">Cooldown (minutes)</label>
                        <input type="number" id="water_cooldown" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                    </div>
                </div>

                <!-- Weight -->
                <div class="bg-white/10 rounded-xl p-4 border border-white/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">⚖️</span>
                            <h4 class="text-lg font-bold text-white">Weight</h4>
                        </div>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <span class="text-sm text-indigo-200">Enabled</span>
                            <input type="checkbox" id="weight_enabled" class="w-5 h-5 rounded bg-white/10 border-white/20 text-indigo-600">
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Min (grams)</label>
                            <input type="number" step="0.1" id="weight_min" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                        <div>
                            <label class="text-sm text-indigo-200 mb-1 block">Max (grams)</label>
                            <input type="number" step="0.1" id="weight_max" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-sm text-indigo-200 mb-1 block">Cooldown (minutes)</label>
                        <input type="number" id="weight_cooldown" class="w-full px-3 py-2 bg-white/10 border border-white/20 rounded text-white">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeThresholdModal()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-all">
                    Cancel
                </button>
                <button onclick="saveThresholds()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all">
                    Save Thresholds
                </button>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            function toggleStatus(deviceId, newStatus) {
                fetch(`/devices/${deviceId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ is_active: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update device status');
                });
            }

            let currentDeviceId = null;

            async function openThresholdModal(deviceId, deviceName) {
                currentDeviceId = deviceId;
                document.getElementById('modalTitle').textContent = `Sensor Thresholds - ${deviceName}`;
                
                // Load current thresholds
                try {
                    const response = await fetch(`/devices/${deviceId}/thresholds`);
                    const data = await response.json();
                    
                    if (data.success) {
                        // Populate form with current values
                        data.thresholds.forEach(t => {
                            const prefix = t.sensor_type === 'temperature' ? 'temp' : 
                                         t.sensor_type === 'humidity' ? 'hum' : 
                                         t.sensor_type === 'water_level' ? 'water' :
                                         t.sensor_type === 'weight' ? 'weight' : 'air';
                            
                            document.getElementById(`${prefix}_enabled`).checked = t.enabled;
                            document.getElementById(`${prefix}_min`).value = t.min_value || '';
                            document.getElementById(`${prefix}_max`).value = t.max_value || '';
                            document.getElementById(`${prefix}_cooldown`).value = t.cooldown_minutes;
                        });
                    }
                } catch (error) {
                    console.error('Error loading thresholds:', error);
                }
                
                // Show modal
                document.getElementById('thresholdModal').classList.remove('hidden');
            }

            function closeThresholdModal() {
                document.getElementById('thresholdModal').classList.add('hidden');
                currentDeviceId = null;
            }

            async function saveThresholds() {
                if (!currentDeviceId) return;
                
                const thresholds = [
                    {
                        sensor_type: 'temperature',
                        min_value: document.getElementById('temp_min').value || null,
                        max_value: document.getElementById('temp_max').value || null,
                        enabled: document.getElementById('temp_enabled').checked,
                        cooldown_minutes: parseInt(document.getElementById('temp_cooldown').value) || 30
                    },
                    {
                        sensor_type: 'humidity',
                        min_value: document.getElementById('hum_min').value || null,
                        max_value: document.getElementById('hum_max').value || null,
                        enabled: document.getElementById('hum_enabled').checked,
                        cooldown_minutes: parseInt(document.getElementById('hum_cooldown').value) || 30
                    },
                    {
                        sensor_type: 'air_quality',
                        min_value: document.getElementById('air_min').value || null,
                        max_value: document.getElementById('air_max').value || null,
                        enabled: document.getElementById('air_enabled').checked,
                        cooldown_minutes: parseInt(document.getElementById('air_cooldown').value) || 30
                    },
                    {
                        sensor_type: 'water_level',
                        min_value: document.getElementById('water_min').value || null,
                        max_value: document.getElementById('water_max').value || null,
                        enabled: document.getElementById('water_enabled').checked,
                        cooldown_minutes: parseInt(document.getElementById('water_cooldown').value) || 30
                    },
                    {
                        sensor_type: 'weight',
                        min_value: document.getElementById('weight_min').value || null,
                        max_value: document.getElementById('weight_max').value || null,
                        enabled: document.getElementById('weight_enabled').checked,
                        cooldown_minutes: parseInt(document.getElementById('weight_cooldown').value) || 30
                    }
                ];
                
                try {
                    const response = await fetch(`/devices/${currentDeviceId}/thresholds`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ thresholds })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('✅ Thresholds saved successfully!');
                        closeThresholdModal();
                    } else {
                        alert('❌ Error: ' + (data.message || 'Failed to save thresholds'));
                    }
                } catch (error) {
                    console.error('Error saving thresholds:', error);
                    alert('❌ Error saving thresholds');
                }
            }
        </script>
    </x-slot>

</x-layout>
