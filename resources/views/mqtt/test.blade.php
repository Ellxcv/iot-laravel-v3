<x-layout title="MQTT Test" active="">

    {{-- Main Content --}}
    
    {{-- Quick Test Button --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-4">Quick MQTT Test</h2>
            <p class="text-indigo-200 mb-6">Click button below to run all MQTT tests. Check console and logs for results.</p>
            
            <button onclick="runQuickTest()" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Run Quick Test
            </button>
            
            <div id="quickTestResult" class="mt-4 hidden"></div>
        </div>
    </div>

    {{-- Custom MQTT Publish --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6">Custom MQTT Publish</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Topic (will be prefixed with "iot/devices/")</label>
                    <input type="text" id="topic" value="ESP32_001/commands" placeholder="ESP32_001/commands" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-indigo-400 mt-1">Full topic: iot/devices/<span id="topicPreview">ESP32_001/commands</span></p>
                </div>
                
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Message (JSON or plain text)</label>
                    <textarea id="message" rows="6" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{"type":"custom","command":"status","timestamp":"2025-01-14T18:00:00+07:00"}</textarea>
                </div>
                
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Quality of Service (QoS)</label>
                    <select id="qos" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="0">0 - At most once</option>
                        <option value="1" selected>1 - At least once (default)</option>
                        <option value="2">2 - Exactly once</option>
                    </select>
                </div>
                
                <button onclick="publishCustom()" class="px-6 py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">
                    Publish Message
                </button>
            </div>
            
            <div id="customResult" class="mt-4 hidden"></div>
        </div>
    </div>

    {{-- Pre-defined Test Commands --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6">Pre-defined Test Commands</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button onclick="testFeederOpen()" class="px-4 py-3 bg-green-500/20 hover:bg-green-500/30 border border-green-500/50 text-green-100 rounded-lg transition-all duration-300 text-left">
                    <div class="font-semibold">Feeder: Open</div>
                    <div class="text-xs mt-1 opacity-70">{"action":"open"}</div>
                </button>
                
                <button onclick="testFeederClose()" class="px-4 py-3 bg-red-500/20 hover:bg-red-500/30 border border-red-500/50 text-red-100 rounded-lg transition-all duration-300 text-left">
                    <div class="font-semibold">Feeder: Close</div>
                    <div class="text-xs mt-1 opacity-70">{"action":"close"}</div>
                </button>
                
                <button onclick="testFeederFeed()" class="px-4 py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 text-left">
                    <div class="font-semibold">Feeder: Feed (3 cycles)</div>
                    <div class="text-xs mt-1 opacity-70">{"action":"feed","amount":3}</div>
                </button>
                
                <button onclick="testFan()" class="px-4 py-3 bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/50 text-cyan-100 rounded-lg transition-all duration-300 text-left">
                    <div class="font-semibold">Actuator: Fan 75%</div>
                    <div class="text-xs mt-1 opacity-70">{"actuator":"fan","duty":75}</div>
                </button>
            </div>
            
            <div id="predefinedResult" class="mt-4 hidden"></div>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="mb-8">
        <div class="bg-orange-500/10 backdrop-blur-xl rounded-2xl p-6 border border-orange-500/30">
            <h3 class="text-lg font-bold text-white mb-4">📝 How to Monitor MQTT Messages:</h3>
            <div class="space-y-3 text-sm text-orange-200">
                <div class="flex items-start space-x-2">
                    <span class="text-orange-400 font-bold">1.</span>
                    <p><strong>Laravel Logs:</strong> Check <code class="bg-white/10 px-2 py-1 rounded">storage/logs/laravel.log</code></p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-orange-400 font-bold">2.</span>
                    <p><strong>MQTT Client:</strong> Use MQTT Explorer or mosquitto_sub to subscribe to <code class="bg-white/10 px-2 py-1 rounded">iot/devices/+/commands</code></p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-orange-400 font-bold">3.</span>
                    <p><strong>ESP32:</strong> Program your ESP32 to subscribe and print received messages</p>
                </div>
                <div class="flex items-start space-x-2">
                    <span class="text-orange-400 font-bold">4.</span>
                    <p><strong>HiveMQ Cloud:</strong> Login to HiveMQ Cloud dashboard to see connection status</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Update topic preview
            document.getElementById('topic').addEventListener('input', (e) => {
                document.getElementById('topicPreview').textContent = e.target.value;
            });

            async function runQuickTest() {
                const resultDiv = document.getElementById('quickTestResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = '<div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4"><p class="text-blue-200">Running tests...</p></div>';

                try {
                    const response = await fetch('/mqtt/test');
                    const data = await response.json();

                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                <p class="text-green-200 font-semibold mb-2">✅ ${data.message}</p>
                                <div class="text-sm text-green-100 space-y-1">
                                    <div>Simple Publish: ${data.results.simple_publish ? '✅' : '❌'}</div>
                                    <div>JSON Publish: ${data.results.json_publish ? '✅' : '❌'}</div>
                                    <div>Device Command: ${data.results.device_command ? '✅' : '❌'}</div>
                                    <div>Feeder Command: ${data.results.feeder_command ? '✅' : '❌'}</div>
                                    <div>Actuator Command: ${data.results.actuator_command ? '✅' : '❌'}</div>
                                </div>
                                <p class="text-xs text-green-300 mt-3">${data.note}</p>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                <p class="text-red-200 font-semibold">❌ Test failed: ${data.error}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-200 font-semibold">❌ Error: ${error.message}</p>
                        </div>
                    `;
                }
            }

            async function publishCustom() {
                const topic = document.getElementById('topic').value;
                const message = document.getElementById('message').value;
                const qos = document.getElementById('qos').value;
                const resultDiv = document.getElementById('customResult');

                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = '<div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4"><p class="text-blue-200">Publishing...</p></div>';

                try {
                    const response = await fetch('/mqtt/publish', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ topic, message, qos })
                    });

                    const data = await response.json();

                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                <p class="text-green-200 font-semibold">✅ ${data.message}</p>
                                <div class="text-xs text-green-100 mt-2">
                                    <div>Topic: ${data.topic}</div>
                                    <div>Payload: ${data.payload}</div>
                                </div>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                <p class="text-red-200 font-semibold">❌ ${data.error}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-200 font-semibold">❌ Error: ${error.message}</p>
                        </div>
                    `;
                }
            }

            async function testCommand(endpoint, data, name) {
                const resultDiv = document.getElementById('predefinedResult');
                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = `<div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4"><p class="text-blue-200">Sending ${name}...</p></div>`;

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        resultDiv.innerHTML = `
                            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                                <p class="text-green-200 font-semibold">✅ ${name} sent successfully</p>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                                <p class="text-red-200 font-semibold">❌ ${result.message || 'Failed'}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-200 font-semibold">❌ Error: ${error.message}</p>
                        </div>
                    `;
                }
            }

            function testFeederOpen() {
                testCommand('/iot/control-feeder', {
                    device_id: 'ESP32_001',
                    action: 'open'
                }, 'Feeder Open');
            }

            function testFeederClose() {
                testCommand('/iot/control-feeder', {
                    device_id: 'ESP32_001',
                    action: 'close'
                }, 'Feeder Close');
            }

            function testFeederFeed() {
                testCommand('/iot/control-feeder', {
                    device_id: 'ESP32_001',
                    action: 'feed',
                    amount: 3
                }, 'Feeder Feed (3 cycles)');
            }

            function testFan() {
                testCommand('/iot/update-actuator', {
                    device_id: 'ESP32_001',
                    actuator: 'fan',
                    duty: 75
                }, 'Fan 75%');
            }
        </script>
    </x-slot>

</x-layout>
