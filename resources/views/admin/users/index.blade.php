@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">User Management</h1>
            <p class="text-dark-400 mt-1">Manage platform users, roles, and access.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New User
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Total Users</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Admins</p>
            <p class="text-2xl font-bold text-primary-400 mt-1">{{ number_format($stats['admins']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Fellows</p>
            <p class="text-2xl font-bold text-teal-400 mt-1">{{ number_format($stats['fellows']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Mentors</p>
            <p class="text-2xl font-bold text-indigo-400 mt-1">{{ number_format($stats['mentors']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Recruiters</p>
            <p class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($stats['recruiters']) }}</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="form-input w-full">
            </div>
            <div class="sm:w-48">
                <select name="role" class="form-input w-full" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach(\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                            {{ ucfirst($role->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary w-full sm:w-auto">Filter</button>
            </div>
            @if(request()->anyFilled(['search', 'role']))
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-dark w-full sm:w-auto">Clear</a>
                </div>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-dark-800/50 border-b border-dark-700">
                    <tr>
                        <th class="px-6 py-4 font-medium text-dark-300">User</th>
                        <th class="px-6 py-4 font-medium text-dark-300">Role</th>
                        <th class="px-6 py-4 font-medium text-dark-300">Status</th>
                        <th class="px-6 py-4 font-medium text-dark-300">Joined</th>
                        <th class="px-6 py-4 font-medium text-dark-300 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-dark-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                                    <div>
                                        <p class="font-medium text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-dark-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium 
                                    @switch($user->role->value)
                                        @case('admin') bg-primary-600/20 text-primary-400 @break
                                        @case('fellow') bg-teal-600/20 text-teal-400 @break
                                        @case('mentor') bg-indigo-600/20 text-indigo-400 @break
                                        @case('recruiter') bg-amber-600/20 text-amber-400 @break
                                        @default bg-dark-600/20 text-dark-400
                                    @endswitch">
                                    {{ ucfirst($user->role->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-green-400">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-dark-400">
                                        <span class="w-2 h-2 rounded-full bg-dark-500"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-dark-400">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-dark-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-dark-400">
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="p-4 border-t border-dark-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
