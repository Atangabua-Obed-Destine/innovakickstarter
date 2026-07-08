@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            Back to Users
        </a>
    </div>

    <!-- Form Card -->
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input w-full">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input w-full">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">System Role <span class="text-red-400">*</span></label>
                    <select name="role" required class="form-input w-full" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ old('role', $user->role->value) === $role->value ? 'selected' : '' }}>
                                {{ ucfirst($role->value) }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role->value }}">
                        <p class="text-xs text-amber-400 mt-1">You cannot change your own role.</p>
                    @endif
                    @error('role') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">New Password</label>
                    <input type="password" name="password" minlength="8" class="form-input w-full" placeholder="Leave blank to keep current">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Status -->
            <div class="pt-4 border-t border-dark-700">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="sr-only peer" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-dark-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-white">Active Account</span>
                        <p class="text-xs text-dark-400">If unchecked, the user will not be able to log in.</p>
                        @if($user->id === auth()->id())
                            <input type="hidden" name="is_active" value="1">
                            <p class="text-xs text-amber-400 mt-1">You cannot deactivate your own account.</p>
                        @endif
                    </div>
                </label>
            </div>

            <div class="pt-6 border-t border-dark-700 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
