<x-layout>
    <x-slot:title>Manage Users</x-slot:title>

    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Manage Users</h1>
            <a href="{{ route('admin.users.create') }}" 
               class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all">
                + Add New User
            </a>
        </div>

        <!-- Success Message -->
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

        <!-- Users Table -->
        <div class="bg-white/5 backdrop-blur-md rounded-xl border border-white/10 overflow-hidden">
            <!-- Horizontal scroll wrapper for mobile -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/5 border-b border-white/10">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200 whitespace-nowrap">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200 whitespace-nowrap">Email</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200 whitespace-nowrap">Role</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-indigo-200 whitespace-nowrap">Created</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-indigo-200 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($users as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-white font-medium">{{ $user->name }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-indigo-200">{{ $user->email }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->isAdmin())
                                        <span class="px-3 py-1 bg-purple-500/20 text-purple-100 rounded-lg text-xs font-medium">Admin</span>
                                    @else
                                        <span class="px-3 py-1 bg-blue-500/20 text-blue-100 rounded-lg text-xs font-medium">User</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-indigo-200 text-sm">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="inline-block px-3 py-1 bg-indigo-500/20 text-indigo-100 rounded hover:bg-indigo-500/30 transition">
                                        Edit
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-3 py-1 bg-red-500/20 text-red-100 rounded hover:bg-red-500/30 transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-indigo-300">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-layout>
