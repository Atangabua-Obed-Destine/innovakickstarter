@extends('layouts.app')

@section('title', "Edit Milestone — {$milestone->title}")

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('admin.curriculum.index', $track) }}" class="hover:text-white transition">{{ $track->name }}</a>
            <span>/</span>
            <span class="text-primary-400">Edit Milestone</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Edit: {{ $milestone->title }}</h1>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.curriculum.milestones.update', [$track, $milestone]) }}" method="POST" class="card p-6 space-y-6">
        @csrf @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium text-dark-300 mb-2">Milestone Title *</label>
            <input type="text" name="title" id="title" value="{{ old('title', $milestone->title) }}" required
                   class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('description', $milestone->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="order" class="block text-sm font-medium text-dark-300 mb-2">Order</label>
                <input type="number" name="order" id="order" value="{{ old('order', $milestone->order) }}" min="1"
                       class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
            <div>
                <label for="estimated_duration_days" class="block text-sm font-medium text-dark-300 mb-2">Estimated Duration (days)</label>
                <input type="number" name="estimated_duration_days" id="estimated_duration_days" value="{{ old('estimated_duration_days', $milestone->estimated_duration_days) }}" min="1"
                       class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
        </div>

        <div>
            <label for="unlock_after_milestone_id" class="block text-sm font-medium text-dark-300 mb-2">Prerequisite Milestone</label>
            <select name="unlock_after_milestone_id" id="unlock_after_milestone_id"
                    class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <option value="">None</option>
                @foreach($existingMilestones as $ms)
                    <option value="{{ $ms->id }}" {{ old('unlock_after_milestone_id', $milestone->unlock_after_milestone_id) == $ms->id ? 'selected' : '' }}>
                        M{{ $ms->order }}: {{ $ms->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1" {{ old('is_required', $milestone->is_required) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                <span class="text-dark-300 text-sm">Required</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $milestone->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                <span class="text-dark-300 text-sm">Active</span>
            </label>
        </div>

        <div class="border-t border-dark-700 pt-6">
            <h3 class="text-white font-medium mb-4">Completion Badge</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="badge_name" class="block text-sm font-medium text-dark-300 mb-2">Badge Name</label>
                    <input type="text" name="badge_name" id="badge_name" value="{{ old('badge_name', $milestone->badge_name) }}"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label for="badge_icon" class="block text-sm font-medium text-dark-300 mb-2">Badge Icon</label>
                    <input type="text" name="badge_icon" id="badge_icon" value="{{ old('badge_icon', $milestone->badge_icon ?? '🏅') }}" maxlength="10"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-center text-2xl focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label for="badge_color" class="block text-sm font-medium text-dark-300 mb-2">Badge Color</label>
                    <input type="color" name="badge_color" id="badge_color" value="{{ old('badge_color', $milestone->badge_color ?? '#8B5CF6') }}"
                           class="w-full h-[42px] bg-dark-800 border border-dark-600 rounded-lg px-2 cursor-pointer">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.curriculum.index', $track) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Milestone</button>
        </div>
    </form>
</div>
@endsection
