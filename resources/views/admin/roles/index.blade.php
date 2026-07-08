@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Roles & Permissions</h1>
        <p class="text-dark-400 mt-1">Manage system roles and their specific access rights.</p>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($roles as $role)
            <div class="card p-6 flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-white capitalize">{{ $role->name }}</h2>
                        <p class="text-sm text-dark-400 mt-1">
                            {{ $role->permissions->count() }} permission{{ $role->permissions->count() !== 1 ? 's' : '' }} assigned
                        </p>
                    </div>
                    <span class="p-2 rounded-lg 
                        @switch($role->name)
                            @case('admin') bg-primary-600/20 text-primary-400 @break
                            @case('fellow') bg-teal-600/20 text-teal-400 @break
                            @case('mentor') bg-indigo-600/20 text-indigo-400 @break
                            @case('recruiter') bg-amber-600/20 text-amber-400 @break
                            @default bg-dark-600/20 text-dark-400
                        @endswitch">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                </div>

                <div class="flex-1 mb-6">
                    @if($role->name === 'admin')
                        <div class="p-3 rounded-lg bg-primary-600/10 border border-primary-500/20 text-sm text-primary-300">
                            Super Administrators have full access to all platform features and settings by default.
                        </div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @forelse($role->permissions->take(8) as $permission)
                                <span class="px-2 py-1 bg-dark-700 rounded text-xs text-dark-300">{{ $permission->name }}</span>
                            @empty
                                <span class="text-sm text-dark-500 italic">No specific permissions assigned.</span>
                            @endforelse
                            
                            @if($role->permissions->count() > 8)
                                <span class="px-2 py-1 bg-dark-800 rounded text-xs text-dark-400">+{{ $role->permissions->count() - 8 }} more</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-dark-700 mt-auto">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm font-medium text-primary-400 hover:text-primary-300 flex items-center gap-1 transition-colors">
                        Edit Permissions
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
