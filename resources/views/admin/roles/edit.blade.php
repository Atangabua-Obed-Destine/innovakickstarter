@extends('layouts.app')

@section('title', 'Edit Role Permissions: ' . ucfirst($role->name))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white capitalize">{{ $role->name }} Role</h1>
            <p class="text-dark-400 mt-1">Configure what users with this role can do.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            Back to Roles
        </a>
    </div>

    <!-- Form Card -->
    <div class="card p-6">
        @if($role->name === 'admin')
            <div class="p-4 mb-6 rounded-lg bg-primary-600/10 border border-primary-500/20 text-primary-300">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>The <strong>Admin</strong> role automatically bypasses all permission checks. While you can assign specific permissions here, they are functionally redundant for Super Administrators.</p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                @foreach($permissions as $group => $groupPermissions)
                    <div>
                        <h3 class="text-lg font-semibold text-white capitalize border-b border-dark-700 pb-2 mb-4">
                            {{ str_replace('_', ' ', $group) }} Permissions
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($groupPermissions as $permission)
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-dark-700 hover:bg-dark-800/50 cursor-pointer transition-colors group">
                                    <div class="pt-0.5">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                            {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500 focus:ring-offset-dark-900">
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white group-hover:text-primary-300 transition-colors">{{ $permission->name }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-8 mt-8 border-t border-dark-700 flex justify-end gap-3">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Permissions</button>
            </div>
        </form>
    </div>
</div>
@endsection
