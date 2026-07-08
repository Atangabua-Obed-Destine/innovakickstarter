@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Add New User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            Back to Users
        </a>
    </div>

    <!-- Form Card -->
    <div class="card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input w-full" placeholder="John Doe">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input w-full" placeholder="john@example.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">System Role <span class="text-red-400">*</span></label>
                    <select name="role" required class="form-input w-full">
                        <option value="">Select a role...</option>
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                                {{ ucfirst($role->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password" required minlength="8" class="form-input w-full" placeholder="••••••••">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Status -->
            <div class="pt-4 border-t border-dark-700">
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-dark-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-white">Active Account</span>
                        <p class="text-xs text-dark-400">If unchecked, the user will not be able to log in.</p>
                    </div>
                </label>
            </div>

            <div class="pt-6 border-t border-dark-700 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection
