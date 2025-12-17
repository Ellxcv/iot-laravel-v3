<x-layout>
    <x-slot:title>Device Details</x-slot:title>

    <div class="p-6 max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Device Details</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.devices.edit', $device) }}" 
                   class="inline-block px-4 py-2 bg-indigo-500/20 text-indigo-100 rounded-lg hover:bg-indigo-500/30 transition">
                    Edit Device
                </a>
                <a href="{{ route('admin.devices.index') }}" 
                   class="inline-block px-4 py-2 bg-white/10 text-indigo-200 rounded-lg hover:bg-white/20 transition">
                    Back to List
                </a>
            </div>
        </div>

        <!-- Device Information -->
        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6 mb-6">
            <h2 class="text-xl font-semibold text-white mb-4">Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Device ID -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Device ID</label>
                    <p class="text-white font-mono">{{ $device->device_id }}</p>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Name</label>
                    <p class="text-white font-medium">{{ $device->name }}</p>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Type</label>
                    <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-100 rounded-lg text-sm">
                        {{ \App\Domain\Entities\DeviceType::from($device->type)->label() }}
                    </span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Status</label>
                    @php
                        $status = \App\Domain\Entities\DeviceStatus::from($device->status);
                    @endphp
                    <span class="inline-block px-3 py-1 {{ $status->badgeClass() }} rounded-lg text-sm">
                        {{ $status->label() }}
                    </span>
                </div>

                <!-- Owner -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Assigned To</label>
                    <p class="text-white">{{ $device->user?->name ?? 'Unassigned' }}</p>
                </div>

                <!-- Created -->
                <div>
                    <label class="block text-sm text-indigo-300 mb-1">Created</label>
                    <p class="text-white">{{ $device->created_at->format('M d, Y H:i') }}</p>
                </div>

                <!-- Description -->
                @if($device->description)
                    <div class="md:col-span-2">
                        <label class="block text-sm text-indigo-300 mb-1">Description</label>
                        <p class="text-white">{{ $device->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Sensor Data -->
        @if($device->sensorData && $device->sensorData->count() > 0)
            <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6">
                <h2 class="text-xl font-semibold text-white mb-4">Recent Sensor Data</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-white/5 border-b border-white/10">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm text-indigo-200">Timestamp</th>
                                <th class="px-4 py-2 text-left text-sm text-indigo-200">Temperature</th>
                                <th class="px-4 py-2 text-left text-sm text-indigo-200">Humidity</th>
                                <th class="px-4 py-2 text-left text-sm text-indigo-200">CO₂</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($device->sensorData as $data)
                                <tr class="hover:bg-white/5 transition">
                                    <td class="px-4 py-2 text-white text-sm">{{ $data->created_at->format('M d, H:i') }}</td>
                                    <td class="px-4 py-2 text-indigo-200">{{ $data->temperature ?? '-' }}°C</td>
                                    <td class="px-4 py-2 text-indigo-200">{{ $data->humidity ?? '-' }}%</td>
                                    <td class="px-4 py-2 text-indigo-200">{{ $data->co2_ppm ?? '-' }} ppm</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6">
                <p class="text-center text-indigo-300">No sensor data available yet</p>
            </div>
        @endif
    </div>
</x-layout>
