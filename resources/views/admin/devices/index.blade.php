<x-layout>
    <x-slot:title>Manage Devices</x-slot:title>

    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Manage Devices</h1>
            <a href="{{ route('admin.devices.create') }}" 
               class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all">
                + Add New Device
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-100">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-100">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-4">
            <form method="GET" action="{{ route('admin.devices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search device..." 
                       class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                
                <!-- Type Filter -->
                <select name="type" class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                    <option value="" style="background-color: #1e1b4b; color: white;">All Types</option>
                    @foreach(\App\Domain\Entities\DeviceType::cases() as $type)
                        <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select name="status" class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                    <option value="" style="background-color: #1e1b4b; color: white;">All Statuses</option>
                    @foreach(\App\Domain\Entities\DeviceStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <!-- User Filter -->
                <select name="user_id" class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                    <option value="" style="background-color: #1e1b4b; color: white;">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Button -->
                <button type="submit" class="px-4 py-2 bg-indigo-500/20 text-indigo-100 rounded-lg hover:bg-indigo-500/30 transition">
                    Apply Filters
                </button>
            </form>
        </div>

        <!-- Devices Table -->
        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/5 border-b border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200">Device ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200">Type</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200">Owner</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200">Status</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-indigo-200">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($devices as $device)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-white font-mono text-sm">{{ $device->device_id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-white font-medium">{{ $device->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-500/20 text-blue-100 rounded-lg text-xs font-medium">
                                    {{ \App\Domain\Entities\DeviceType::from($device->type)->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-indigo-200">{{ $device->user?->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $status = \App\Domain\Entities\DeviceStatus::from($device->status);
                                @endphp
                                <span class="px-3 py-1 {{ $status->badgeClass() }} rounded-lg text-xs font-medium">
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.devices.show', $device) }}" 
                                   class="inline-block px-3 py-1 bg-green-500/20 text-green-100 rounded hover:bg-green-500/30 transition">
                                    View
                                </a>
                                <a href="{{ route('admin.devices.edit', $device) }}" 
                                   class="inline-block px-3 py-1 bg-indigo-500/20 text-indigo-100 rounded hover:bg-indigo-500/30 transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this device? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 bg-red-500/20 text-red-100 rounded hover:bg-red-500/30 transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-indigo-300">
                                No devices found. Create your first device!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $devices->links() }}
        </div>
    </div>
</x-layout>
