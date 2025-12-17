<x-layout>
    <x-slot:title>Edit Device</x-slot:title>

    <div class="p-6 max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-white mb-6">Edit Device: {{ $device->name }}</h1>

        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6">
            <form action="{{ route('admin.devices.update', $device) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Device ID -->
                <div class="mb-4">
                    <label for="device_id" class="block text-sm font-medium text-indigo-200 mb-2">Device ID *</label>
                    <input type="text" name="device_id" id="device_id" value="{{ old('device_id', $device->device_id) }}" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('device_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-indigo-200 mb-2">Device Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-indigo-200 mb-2">Device Type *</label>
                    <select name="type" id="type" required
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(\App\Domain\Entities\DeviceType::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('type', $device->type) === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-indigo-200 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $device->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assign to User -->
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-indigo-200 mb-2">Assign to User</label>
                    <select name="user_id" id="user_id"
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $device->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-indigo-200 mb-2">Status *</label>
                    <select name="status" id="status" required
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(\App\Domain\Entities\DeviceStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('status', $device->status) === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.devices.index') }}" 
                       class="px-4 py-2 bg-white/10 text-indigo-200 rounded-lg hover:bg-white/20 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition">
                        Update Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
