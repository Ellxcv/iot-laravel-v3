<x-layout title="Notifications" active="notifications">

    {{-- Status Badge in Navbar --}}
    <x-slot name="navbarSlot">
        @if($settings && $settings->canSendNotifications())
            <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 rounded-full bg-green-500/20 border border-green-500/50">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-xs sm:text-sm text-green-100 font-medium">Enabled</span>
            </div>
        @else
            <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-3 py-1 rounded-full bg-gray-500/20 border border-gray-500/50">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gray-400"></div>
                <span class="text-xs sm:text-sm text-gray-100 font-medium">Disabled</span>
            </div>
        @endif
    </x-slot>

    {{-- Main Content --}}
    
    {{-- Telegram Notification Settings --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Notification Settings
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Settings Form --}}
                <div class="space-y-4">
                    <div class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4 mb-4">
                        <p class="text-indigo-200 text-sm">✓ Bot Token configured from system</p>
                        <p class="text-indigo-300/70 text-xs mt-1">You only need to configure your Chat ID</p>
                    </div>
                    <div>
                        <label class="text-sm text-indigo-300 mb-2 block">Telegram Chat ID</label>
                        <input type="text" id="chatId" value="{{ $settings->chatId ?? '' }}" placeholder="123456789" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-indigo-300">Enable Notifications</label>
                        <button onclick="toggleEnabled()" id="enabledToggle" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $settings && $settings->enabled ? 'bg-indigo-600' : 'bg-gray-600' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $settings && $settings->enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    <button onclick="saveSettings()" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300">
                        Save Settings
                    </button>
                </div>

                {{-- Setup Instructions --}}
                <div class="bg-white/5 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        How to Get Chat ID
                    </h3>
                    <div class="space-y-3 text-sm text-indigo-200">
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">1.</span>
                            <p>Open Telegram app</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">2.</span>
                            <p>Search dan chat <code class="bg-white/10 px-2 py-1 rounded text-xs">@userinfobot</code></p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">3.</span>
                            <p>Klik <strong>START</strong> button</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">4.</span>
                            <p>Bot akan reply dengan info Anda</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">5.</span>
                            <p>Copy <strong>Id</strong> (Chat ID) Anda</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-indigo-400 font-bold">6.</span>
                            <p>Paste di input field dan Save Settings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Firebase Push Notification Settings --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Firebase Push Notifications
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Firebase Settings Form --}}
                <div class="space-y-4">
                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 mb-4">
                        <p class="text-orange-200 text-sm">🔥 Firebase for browser/mobile push</p>
                        <p class="text-orange-300/70 text-xs mt-1">Get device token from your browser</p>
                    </div>
                    <div>
                        <label class="text-sm text-indigo-300 mb-2 block">Device Token</label>
                        <div class="flex gap-2">
                            <input type="text" id="fcmDeviceToken" value="{{ $settings->fcmDeviceToken ?? '' }}" placeholder="Click 'Get Token' button" readonly class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <button onclick="getFirebaseToken()" class="px-4 py-3 bg-orange-500/20 hover:bg-orange-500/30 border border-orange-500/50 text-orange-100 rounded-lg transition-all duration-300 font-medium whitespace-nowrap">
                                Get Token
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-indigo-300">Enable Firebase</label>
                        <button onclick="toggleFirebase()" id="firebaseToggle" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $settings && $settings->firebaseEnabled ? 'bg-orange-600' : 'bg-gray-600' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $settings && $settings->firebaseEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                    <button onclick="saveSettings()" class="w-full px-4 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold rounded-lg transition-all duration-300">
                        Save Firebase Settings
                    </button>
                </div>

                {{-- Firebase Setup Instructions --}}
                <div class="bg-white/5 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Firebase Setup
                    </h3>
                    <div class="space-y-3 text-sm text-indigo-200">
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">1.</span>
                            <p>Click "<strong>Get Token</strong>" button</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">2.</span>
                            <p>Allow browser notification permission</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">3.</span>
                            <p>Device token will auto-fill</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">4.</span>
                            <p>Enable Firebase toggle</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">5.</span>
                            <p>Click "Save Firebase Settings"</p>
                        </div>
                        <div class="flex items-start space-x-2">
                            <span class="text-orange-400 font-bold">6.</span>
                            <p>Test below to receive push notification!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Send Test Notification --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Send Test Notification
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <input type="text" id="testMessage" placeholder="Hello from IoT Dashboard! 🚀" class="flex-1 px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white text-sm placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button onclick="sendTest()" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span class="text-sm sm:text-base">Send Test</span>
                </button>
            </div>
            <div id="testResult" class="mt-4 hidden"></div>
        </div>
    </div>

    {{-- Notification Logs --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Notification History
                </h2>
                <button onclick="refreshLogs()" class="px-4 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Type</th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Message</th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Status</th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Sent At</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        @forelse($logs as $log)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-3 px-2">
                                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-100 rounded-lg text-xs font-medium capitalize">
                                        {{ str_replace('_', ' ', $log->type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <p class="text-sm text-white truncate max-w-md">{{ $log->message }}</p>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $log->status === 'sent' ? 'bg-green-500/20 text-green-100' : 'bg-red-500/20 text-red-100' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-sm text-indigo-200">{{ $log->sentAt?->format('M d, Y H:i') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center">
                                    <p class="text-gray-400">No notifications sent yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let isEnabled = {{ $settings && $settings->enabled ? 'true' : 'false' }};
            let isFirebaseEnabled = {{ $settings && $settings->firebaseEnabled ? 'true' : 'false' }};

            // Firebase config
            window.FIREBASE_CONFIG = {
                apiKey: '{{ config("services.firebase.api_key") }}',
                authDomain: '{{ config("services.firebase.auth_domain") }}',
                projectId: '{{ config("services.firebase.project_id") }}',
                storageBucket: '{{ config("services.firebase.storage_bucket") }}',
                messagingSenderId: '{{ config("services.firebase.messaging_sender_id") }}',
                appId: '{{ config("services.firebase.app_id") }}',
                vapidPublic: '{{ config("services.firebase.vapid_public") }}'
            };

            function toggleEnabled() {
                isEnabled = !isEnabled;
                const toggle = document.getElementById('enabledToggle');
                const span = toggle.querySelector('span');
                
                if (isEnabled) {
                    toggle.classList.remove('bg-gray-600');
                    toggle.classList.add('bg-indigo-600');
                    span.classList.remove('translate-x-1');
                    span.classList.add('translate-x-6');
                } else {
                    toggle.classList.remove('bg-indigo-600');
                    toggle.classList.add('bg-gray-600');
                    span.classList.remove('translate-x-6');
                    span.classList.add('translate-x-1');
                }
            }

            function toggleFirebase() {
                isFirebaseEnabled = !isFirebaseEnabled;
                const toggle = document.getElementById('firebaseToggle');
                const span = toggle.querySelector('span');
                
                if (isFirebaseEnabled) {
                    toggle.classList.remove('bg-gray-600');
                    toggle.classList.add('bg-orange-600');
                    span.classList.remove('translate-x-1');
                    span.classList.add('translate-x-6');
                } else {
                    toggle.classList.remove('bg-orange-600');
                    toggle.classList.add('bg-gray-600');
                    span.classList.remove('translate-x-6');
                    span.classList.add('translate-x-1');
                }
            }

            async function getFirebaseToken() {
                try {
                    const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js');
                    const { getMessaging, getToken } = await import('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js');

                    const app = initializeApp(window.FIREBASE_CONFIG);
                    const messaging = getMessaging(app);

                    const permission = await Notification.requestPermission();
                    if (permission !== 'granted') {
                        alert('Notification permission denied');
                        return;
                    }

                    const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    await navigator.serviceWorker.ready;

                    const token = await getToken(messaging, {
                        vapidKey: window.FIREBASE_CONFIG.vapidPublic,
                        serviceWorkerRegistration: registration
                    });

                    if (token) {
                        // Detect device type
                        const deviceInfo = detectDeviceInfo();
                        
                        // Register token via API (supports multiple devices)
                        const response = await fetch('/api/fcm-tokens', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                fcm_token: token,
                                device_name: deviceInfo.name,
                                device_type: deviceInfo.type
                            })
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            document.getElementById('fcmDeviceToken').value = token;
                            alert(`✅ Device registered: ${deviceInfo.name}\nYou'll receive notifications on this device!`);
                            
                            // Refresh registered devices list
                            loadRegisteredDevices();
                        } else {
                            alert('Failed to register token: ' + result.message);
                        }
                    } else {
                        alert('Failed to get Firebase token');
                    }
                } catch (error) {
                    console.error('Firebase token error:', error);
                    alert('Error getting Firebase token: ' + error.message);
                }
            }

            // Detect device information
            function detectDeviceInfo() {
                const ua = navigator.userAgent;
                let deviceType = 'desktop';
                let browser = 'Unknown';
                let os = 'Unknown';

                // Detect device type
                if (/Mobi|Android|iPhone|iPad|iPod/i.test(ua)) {
                    deviceType = /iPad/i.test(ua) ? 'tablet' : 'mobile';
                } else if (/Tablet|iPad/i.test(ua)) {
                    deviceType = 'tablet';
                }

                // Detect browser
                if (ua.indexOf('Firefox') > -1) browser = 'Firefox';
                else if (ua.indexOf('Chrome') > -1) browser = 'Chrome';
                else if (ua.indexOf('Safari') > -1) browser = 'Safari';
                else if (ua.indexOf('Edge') > -1) browser = 'Edge';

                // Detect OS
                if (ua.indexOf('Win') > -1) os = 'Windows';
                else if (ua.indexOf('Mac') > -1) os = 'macOS';
                else if (ua.indexOf('Linux') > -1) os = 'Linux';
                else if (ua.indexOf('Android') > -1) os = 'Android';
                else if (ua.indexOf('iOS') > -1 || ua.indexOf('iPhone') > -1) os = 'iOS';

                return {
                    name: `${browser} on ${os}`,
                    type: deviceType
                };
            }

            function saveSettings() {
                const chatId = document.getElementById('chatId').value.trim();
                const fcmDeviceToken = document.getElementById('fcmDeviceToken').value.trim();

                fetch('{{ route("notifications.update") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        bot_token: null,
                        chat_id: chatId,
                        enabled: isEnabled,
                        fcm_device_token: fcmDeviceToken,
                        firebase_enabled: isFirebaseEnabled
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Settings saved successfully!');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        alert(data.message || 'Failed to save settings');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving settings');
                });
            }


            async function sendTest() {
                const message = document.getElementById('testMessage').value.trim() || 'Hello from IoT Dashboard! 🚀';

                try {
                    const response = await fetch('{{ route("notifications.test") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ message: message })
                    });
                    
                    const data = await response.json();
                    const resultDiv = document.getElementById('testResult');
                    resultDiv.classList.remove('hidden');
                    
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                <p class="text-green-200 font-semibold">✅ ${data.message}</p>
                            </div>
                        `;
                        
                        // Trigger browser notification if Firebase is enabled
                        if (isFirebaseEnabled && Notification.permission === 'granted') {
                            try {
                                // Use Service Worker for mobile compatibility
                                const registration = await navigator.serviceWorker.ready;
                                await registration.showNotification('IoT Alert', {
                                    body: message,
                                    icon: '/favicon.ico',
                                    badge: '/favicon.ico',
                                    tag: 'test-notification',
                                    requireInteraction: false,
                                    vibrate: [200, 100, 200]
                                });
                            } catch (notifError) {
                                console.error('Notification error:', notifError);
                                // Fallback for desktop: use direct Notification API
                                try {
                                    new Notification('IoT Alert', {
                                        body: message,
                                        icon: '/favicon.ico'
                                    });
                                } catch (e) {
                                    console.error('Fallback notification error:', e);
                                }
                            }
                        }
                        
                        refreshLogs();
                    } else {
                        resultDiv.innerHTML = `
                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                <p class="text-red-200 font-semibold">❌ ${data.message}</p>
                            </div>
                        `;
                    }

                    setTimeout(() => resultDiv.classList.add('hidden'), 5000);
                } catch (error) {
                    console.error('Error:', error);
                    const resultDiv = document.getElementById('testResult');
                    resultDiv.classList.remove('hidden');
                    resultDiv.innerHTML = `
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-200 font-semibold">❌ Error: ${error.message}</p>
                        </div>
                    `;
                    setTimeout(() => resultDiv.classList.add('hidden'), 5000);
                }
            }

            function refreshLogs() {
                fetch('{{ route("notifications.logs") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('logsTableBody');
                        if (data.logs.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="py-8 text-center">
                                        <p class="text-gray-400">No notifications sent yet</p>
                                    </td>
                                </tr>
                            `;
                        } else {
                            tbody.innerHTML = data.logs.map(log => `
                                <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-2">
                                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-100 rounded-lg text-xs font-medium capitalize">
                                            ${log.type.replace('_', ' ')}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2">
                                        <p class="text-sm text-white truncate max-w-md">${log.message}</p>
                                    </td>
                                    <td class="py-3 px-2">
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold ${log.status === 'sent' ? 'bg-green-500/20 text-green-100' : 'bg-red-500/20 text-red-100'}">
                                            ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2">
                                        <span class="text-sm text-indigo-200">${log.sent_at}</span>
                                    </td>
                                </tr>
                            `).join('');
                        }
                    }
                })
                .catch(error => console.error('Error refreshing logs:', error));
            }

            // Load registered devices (placeholder for now)
            function loadRegisteredDevices() {
                // Will be implemented later - for now just log
                console.log('Loading registered devices...');
            }
        </script>
    </x-slot>

</x-layout>
