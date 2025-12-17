<x-layout>
    <x-slot:title>Edit User</x-slot:title>

    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-white mb-6">Edit User: {{ $user->name }}</h1>

        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-indigo-200 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-indigo-200 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-indigo-200 mb-2">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Enter new password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-indigo-200 mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Confirm new password">
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role" class="block text-sm font-medium text-indigo-200 mb-2">Role</label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;"
                            {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">User</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }} style="background-color: #1e1b4b; color: white;">Admin</option>
                    </select>
                    @if(auth()->id() === $user->id)
                        <p class="mt-1 text-sm text-yellow-400">You cannot change your own role.</p>
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif
                    @error('role')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-4 py-2 bg-white/10 text-indigo-200 rounded-lg hover:bg-white/20 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
