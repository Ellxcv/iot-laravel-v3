<x-layout title="Live Camera View" active="camera">

    {{-- Stream Status in Navbar --}}
    <x-slot name="navbarSlot">
        <div id="streamStatus" class="flex items-center space-x-2 px-3 py-1 rounded-full bg-gray-500/20 border border-gray-500/50">
            <div class="w-2 h-2 rounded-full bg-gray-400"></div>
            <span class="text-sm text-gray-100 font-medium">Connecting...</span>
        </div>
    </x-slot>

    {{-- Main Content --}}
    {{-- Camera Stream --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Cat Cage Live Stream
                </h2>
                <button onclick="refreshStream()" class="px-4 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>

            {{-- Video Stream Container --}}
            <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio: 16/9;">
                <img id="streamImage" 
                     src="" 
                     alt="ESP32-CAM Live Stream" 
                     class="w-full h-full object-contain"
                     onerror="handleStreamError()"
                     onload="handleStreamLoad()">
                
                {{-- Loading Overlay --}}
                <div id="loadingOverlay" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-indigo-400 mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-white text-lg">Connecting to camera...</p>
                </div>

                {{-- Error Overlay --}}
                <div id="errorOverlay" class="absolute inset-0 bg-black/80 flex-col items-center justify-center hidden">
                    <svg class="w-16 h-16 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-white text-lg mb-2">Camera Connection Failed</p>
                    <p class="text-gray-300 text-sm mb-4">Unable to connect to ESP32-CAM</p>
                    <button onclick="refreshStream()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        Try Again
                    </button>
                </div>
            </div>

            {{-- Stream Info --}}
            @if($selectedCamera)
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white/5 rounded-lg p-4">
                        <p class="text-sm text-indigo-300 mb-1">Device ID</p>
                        <p class="text-lg text-white font-mono">{{ $selectedCamera->deviceId }}</p>
                    </div>
                    <div class="bg-white/5 rounded-lg p-4">
                        <p class="text-sm text-indigo-300 mb-1">Resolution</p>
                        <p class="text-lg text-white font-semibold">{{ $selectedCamera->resolution ?? '800x600' }}</p>
                    </div>
                    <div class="bg-white/5 rounded-lg p-4">
                        <p class="text-sm text-indigo-300 mb-1">Frame Rate</p>
                        <p class="text-lg text-white font-semibold"><span data-fps>{{ $selectedCamera->fps ?? 0 }}</span> FPS</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Camera Stream Selection & Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Device Selection --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                Camera Device ID
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Enter Device ID</label>
                    <input type="text" 
                           id="deviceIdInput" 
                           value="{{ $selectedCamera->deviceId ?? '' }}" 
                           placeholder="Example: CAM_001"
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           onkeypress="if(event.key === 'Enter') loadCamera()">
                </div>
                <button onclick="loadCamera()" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300">
                    Load Camera Stream
                </button>
                
                @if($selectedCamera)
                    <div class="border-t border-white/10 pt-4">
                        <p class="text-xs text-indigo-300 mb-3">Current Camera</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/5 rounded-lg p-3">
                                <p class="text-xs text-indigo-300 mb-1">Name</p>
                                <p class="text-sm text-white font-semibold">{{ $selectedCamera->name }}</p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-3">
                                <p class="text-xs text-indigo-300 mb-1">Type</p>
                                <p class="text-sm text-white font-semibold capitalize">{{ $selectedCamera->type }}</p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-3">
                                <p class="text-xs text-indigo-300 mb-1">Status</p>
                                <p data-status class="text-sm font-semibold capitalize {{ $selectedCamera->status === 'online' ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $selectedCamera->status }}
                                </p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-3">
                                <p class="text-xs text-indigo-300 mb-1">Resolution</p>
                                <p class="text-sm text-white font-semibold">{{ $selectedCamera->resolution ?? '800x600' }}</p>
                            </div>
                        </div>
                    </div>
                @elseif(request()->has('device_id'))
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                        <p class="text-red-200 text-sm font-semibold">Camera device not found!</p>
                        <p class="text-red-300/70 text-xs mt-1">Device ID "{{ request('device_id') }}" does not exist.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Setup Instructions
            </h3>
            <div class="space-y-3 text-sm text-indigo-200">
                <div class="flex items-start space-x-2">
                    <span class="text-indigo-400 font-bold">1.</span>
                    <p>Pastikan kamera sudah terdaftar di database dengan <code class="text-xs bg-white/10 px-2 py-1 rounded">device_id</code></p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-indigo-400 font-bold">2.</span>
                    <p>Ketik device_id kamera (contoh: <code class="text-xs bg-white/10 px-2 py-1 rounded">CAM_001</code>)</p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-indigo-400 font-bold">3.</span>
                    <p>Klik "Load Camera Stream" atau tekan Enter</p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-indigo-400 font-bold">4.</span>
                    <p>Stream otomatis dimulai dari URL yang tersimpan di database</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Camera Controls Panel --}}
    @if($selectedCamera)
    <div class="mt-6 bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
            </svg>
            Camera Controls
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Basic Controls --}}
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-indigo-300 uppercase tracking-wide">Basic Commands</h4>
                
                <button onclick="sendCameraCommand('capture')" 
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                    <span>📸 Capture Image</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button onclick="sendCameraCommand('stream_start')" 
                            class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all">
                        <span>▶️ Start Stream</span>
                    </button>
                    <button onclick="sendCameraCommand('stream_stop')" 
                            class="px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all">
                        <span>⏹️ Stop Stream</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button onclick="sendFlashCommand('on')" 
                            class="px-4 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-all">
                        <span>💡 Flash ON</span>
                    </button>
                    <button onclick="sendFlashCommand('off')" 
                            class="px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-all">
                        <span>Flash OFF</span>
                    </button>
                </div>
            </div>

            {{-- Advanced Settings --}}
            <div class="space-y-4">
                <h4 class="text-sm font-semibold text-indigo-300 uppercase tracking-wide">Advanced Settings</h4>
                
                <div class="bg-white/5 rounded-lg p-4">
                    <label class="block text-sm text-indigo-300 mb-2">
                        JPEG Quality: <span id="qualityValue" class="text-white font-bold">10</span>
                        <span class="text-xs text-gray-400">(lower = better)</span>
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="range" id="qualitySlider" min="0" max="63" value="10" 
                               oninput="updateQualityValue(this.value)"
                               class="flex-1">
                        <button onclick="applyQuality()" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                            Apply
                        </button>
                    </div>
                </div>

                <div class="bg-white/5 rounded-lg p-4">
                    <label class="block text-sm text-indigo-300 mb-2">Resolution</label>
                    <div class="flex items-center space-x-3">
                        <select id="resolutionSelect" 
                                class="flex-1 px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white">
                            <option value="QVGA">QVGA (320x240)</option>
                            <option value="VGA" selected>VGA (640x480)</option>
                            <option value="SVGA">SVGA (800x600)</option>
                            <option value="XGA">XGA (1024x768)</option>
                            <option value="SXGA">SXGA (1280x1024)</option>
                            <option value="UXGA">UXGA (1600x1200)</option>
                        </select>
                        <button onclick="applyResolution()" 
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg">
                            Apply
                        </button>
                    </div>
                </div>

                <div id="commandStatus" class="hidden bg-green-500/10 border border-green-500/30 rounded-lg p-3">
                    <p class="text-green-200 text-sm" id="commandMessage"></p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            @if($selectedCamera)
                let currentStreamUrl = '{{ $selectedCamera->streamUrl }}';
            @else
                let currentStreamUrl = '';
            @endif
            let retryCount = 0;
            const maxRetries = 3;

            function loadCamera() {
                const deviceId = document.getElementById('deviceIdInput').value.trim();
                if (deviceId) {
                    window.location.href = '{{ route("camera.live") }}?device_id=' + deviceId;
                } else {
                    alert('Please enter a Device ID');
                }
            }

            function startStream() {
                const streamImage = document.getElementById('streamImage');
                const loadingOverlay = document.getElementById('loadingOverlay');
                const errorOverlay = document.getElementById('errorOverlay');
                const streamStatus = document.getElementById('streamStatus');

                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
                errorOverlay.classList.remove('flex');
                errorOverlay.classList.add('hidden');

                streamStatus.innerHTML = `
                    <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                    <span class="text-sm text-yellow-100 font-medium">Connecting...</span>
                `;

                streamImage.src = currentStreamUrl + '?t=' + new Date().getTime();
            }

            function handleStreamLoad() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                const streamStatus = document.getElementById('streamStatus');

                loadingOverlay.classList.remove('flex');
                loadingOverlay.classList.add('hidden');
                retryCount = 0;

                streamStatus.innerHTML = `
                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                    <span class="text-sm text-green-100 font-medium">Live</span>
                `;
            }

            function handleStreamError() {
                const errorOverlay = document.getElementById('errorOverlay');
                const loadingOverlay = document.getElementById('loadingOverlay');
                const streamStatus = document.getElementById('streamStatus');

                loadingOverlay.classList.remove('flex');
                loadingOverlay.classList.add('hidden');

                if (retryCount < maxRetries) {
                    retryCount++;
                    console.log(`Retry ${retryCount}/${maxRetries}...`);
                    setTimeout(startStream, 2000);
                } else {
                    errorOverlay.classList.remove('hidden');
                    errorOverlay.classList.add('flex');
                    
                    streamStatus.innerHTML = `
                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                        <span class="text-sm text-red-100 font-medium">Offline</span>
                    `;
                }
            }

            function refreshStream() {
                retryCount = 0;
                startStream();
            }

            // Start stream on page load
            window.addEventListener('load', function() {
                setTimeout(startStream, 1000);
            });

            // Auto-refresh stream every 30 seconds
            setInterval(function() {
                if (retryCount === 0) {
                    const streamImage = document.getElementById('streamImage');
                    streamImage.src = currentStreamUrl + '?t=' + new Date().getTime();
                }
            }, 30000);

            // Auto-update device status (FPS, etc) every 5 seconds
            @if($selectedCamera)
            setInterval(function() {
                fetch('/camera/{{ $selectedCamera->deviceId }}/status')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update FPS display
                            const fpsElements = document.querySelectorAll('[data-fps]');
                            fpsElements.forEach(el => {
                                el.textContent = data.data.fps ?? 0;
                            });

                            // Update status indicator
                            const statusElements = document.querySelectorAll('[data-status]');
                            statusElements.forEach(el => {
                                el.textContent = data.data.status;
                                el.className = el.className.replace(/text-(green|red)-400/, 
                                    data.data.status === 'online' ? 'text-green-400' : 'text-red-400');
                            });
                        }
                    })
                    .catch(err => console.error('Status update failed:', err));
            }, 5000);
            @endif

            // Camera Control Functions
            const deviceId = '{{ $selectedCamera->deviceId ?? "" }}';
            const csrfToken = '{{ csrf_token() }}';

            function sendCameraCommand(cmd) {
                const routes = {
                    'capture': `/camera/${deviceId}/capture`,
                    'stream_start': `/camera/${deviceId}/stream/start`,
                    'stream_stop': `/camera/${deviceId}/stream/stop`
                };

                fetch(routes[cmd], {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                .then(res => res.json())
                .then(data => {
                    showCommandStatus(data.message, data.success);
                })
                .catch(err => {
                    showCommandStatus('Command failed: ' + err.message, false);
                });
            }

            function sendFlashCommand(state) {
                fetch(`/camera/${deviceId}/flash`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ state: state })
                })
                .then(res => res.json())
                .then(data => {
                    showCommandStatus(data.message, data.success);
                })
                .catch(err => {
                    showCommandStatus('Flash command failed: ' + err.message, false);
                });
            }

            function updateQualityValue(value) {
                document.getElementById('qualityValue').textContent = value;
            }

            function applyQuality() {
                const quality = document.getElementById('qualitySlider').value;
                
                fetch(`/camera/${deviceId}/quality`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ quality: parseInt(quality) })
                })
                .then(res => res.json())
                .then(data => {
                    showCommandStatus(data.message, data.success);
                })
                .catch(err => {
                    showCommandStatus('Quality update failed: ' + err.message, false);
                });
            }

            function applyResolution() {
                const resolution = document.getElementById('resolutionSelect').value;
                
                fetch(`/camera/${deviceId}/resolution`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ resolution: resolution })
                })
                .then(res => res.json())
                .then(data => {
                    showCommandStatus(data.message, data.success);
                })
                .catch(err => {
                    showCommandStatus('Resolution update failed: ' + err.message, false);
                });
            }

            function showCommandStatus(message, success) {
                const statusDiv = document.getElementById('commandStatus');
                const messageP = document.getElementById('commandMessage');
                
                messageP.textContent = message;
                statusDiv.className = success 
                    ? 'bg-green-500/10 border border-green-500/30 rounded-lg p-3'
                    : 'bg-red-500/10 border border-red-500/30 rounded-lg p-3';
                messageP.className = success ? 'text-green-200 text-sm' : 'text-red-200 text-sm';
                
                statusDiv.classList.remove('hidden');
                
                // Auto-hide after 3 seconds
                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 3000);
            }
        </script>
    </x-slot>

</x-layout>
```
