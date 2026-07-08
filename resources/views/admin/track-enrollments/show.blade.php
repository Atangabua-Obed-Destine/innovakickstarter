@extends('layouts.app')

@section('title', 'Track Enrollment: ' . ($enrollment->fellow?->name ?? ''))

@section('content')
@php
    $styles = [
        'pending'        => ['label' => 'Pending Review', 'class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-orange-600/20 text-orange-400 border-orange-500/30'],
        'approved'       => ['label' => 'Approved',       'class' => 'bg-green-600/20 text-green-400 border-green-500/30'],
        'rejected'       => ['label' => 'Rejected',       'class' => 'bg-red-600/20 text-red-400 border-red-500/30'],
    ];
    $s = $styles[$enrollment->status] ?? ['label' => ucfirst($enrollment->status), 'class' => 'bg-dark-700 text-dark-300'];
    $isTerminal = in_array($enrollment->status, ['approved', 'rejected']);
@endphp

<div class="space-y-6" x-data="{ action: null }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.track-enrollments.index') }}" class="hover:text-white">Track Enrollments</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $enrollment->fellow?->name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">Enrollment request</h1>
                <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
            </div>
        </div>
        <a href="{{ route('admin.track-enrollments.index') }}" class="btn btn-outline">Back to list</a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Fellow -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Fellow</h3>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-xl font-bold text-white">
                        {{ strtoupper(substr($enrollment->fellow?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold">{{ $enrollment->fellow?->name }}</p>
                        <p class="text-dark-400 text-sm">{{ $enrollment->fellow?->email }}</p>
                        @if($enrollment->fellow?->fellow_type)
                            <p class="text-dark-500 text-xs mt-1 capitalize">{{ str_replace('_', ' ', $enrollment->fellow->fellow_type->value) }} fellow</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.fellows.show', $enrollment->fellow) }}" class="btn btn-outline text-sm">Open profile</a>
                </div>
            </div>

            <!-- Track requested -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Requested track</h3>
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                         style="background: {{ ($enrollment->track?->color ?? '#1e293b') }}20; color: {{ $enrollment->track?->color ?? '#6366f1' }}">
                        <x-track-icon :icon="$enrollment->track?->icon" class="w-7 h-7" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xl font-bold text-white">{{ $enrollment->track?->name }}</p>
                        @if($enrollment->track?->description)
                            <p class="text-dark-400 text-sm mt-1">{{ $enrollment->track->description }}</p>
                        @endif
                        @if(is_array($enrollment->track?->skills))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach(array_slice($enrollment->track->skills, 0, 8) as $skill)
                                    <span class="px-2 py-1 text-xs bg-dark-700 text-dark-300 rounded-lg">{{ $skill }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Motivation -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Motivation</h3>
                @if($enrollment->motivation)
                    <p class="text-dark-200 whitespace-pre-line">{{ $enrollment->motivation }}</p>
                @else
                    <p class="text-dark-500 italic">The fellow didn't provide a reason.</p>
                @endif
            </div>

            <!-- Other tracks -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Other approved tracks</h3>
                @if($otherTracks->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach($otherTracks as $ft)
                            <li class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                                <div>
                                    <p class="text-dark-200 font-medium">{{ $ft->track?->name }}</p>
                                    <p class="text-dark-500 text-xs">{{ number_format($ft->score, 1) }}% · {{ ucfirst($ft->tier) }}</p>
                                </div>
                                @if($ft->is_primary)
                                    <span class="badge bg-primary-600/20 text-primary-400 border-primary-500/30 text-xs">Primary</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-dark-500 text-sm">No other approved tracks.</p>
                @endif
            </div>
        </div>

        <!-- Review sidebar -->
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Review status</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-dark-500">Current</span><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-500">Requested</span><span class="text-dark-200">{{ $enrollment->requested_at?->format('M j, Y H:i') ?? '—' }}</span></div>
                    @if($enrollment->reviewed_at)
                        <div class="flex justify-between"><span class="text-dark-500">Reviewed</span><span class="text-dark-200">{{ $enrollment->reviewed_at->format('M j, Y H:i') }}</span></div>
                        <div class="flex justify-between"><span class="text-dark-500">By</span><span class="text-dark-200">{{ $enrollment->reviewer?->name ?? '—' }}</span></div>
                    @endif
                </div>
                @if($enrollment->review_notes)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <p class="text-dark-500 text-xs uppercase tracking-wide mb-1">Last review notes</p>
                        <p class="text-dark-200 text-sm whitespace-pre-line">{{ $enrollment->review_notes }}</p>
                    </div>
                @endif
            </div>

            @unless($isTerminal)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>
                    <div class="flex flex-col gap-2">
                        <button type="button" @click="action = action === 'approve' ? null : 'approve'" class="btn btn-primary w-full">Approve enrollment</button>
                        <button type="button" @click="action = action === 'changes' ? null : 'changes'" class="btn btn-outline text-amber-400 border-amber-500/40 hover:bg-amber-500/10 w-full">Request changes</button>
                        <button type="button" @click="action = action === 'reject' ? null : 'reject'" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Reject</button>
                    </div>

                    <form x-show="action === 'approve'" x-cloak method="POST" action="{{ route('admin.track-enrollments.approve', $enrollment) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">Optional notes</span>
                            <textarea name="review_notes" rows="2" class="form-input w-full mt-1" placeholder="Anything the fellow should know"></textarea>
                        </label>
                        <button type="submit" class="btn btn-primary w-full">Confirm approval</button>
                    </form>

                    <form x-show="action === 'changes'" x-cloak method="POST" action="{{ route('admin.track-enrollments.request-changes', $enrollment) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">What needs to be revised? <span class="text-red-400">*</span></span>
                            <textarea name="review_notes" rows="4" required minlength="10" class="form-input w-full mt-1" placeholder="Explain what the fellow should provide or reconsider."></textarea>
                        </label>
                        <button type="submit" class="btn btn-primary w-full">Send revision request</button>
                    </form>

                    <form x-show="action === 'reject'" x-cloak method="POST" action="{{ route('admin.track-enrollments.reject', $enrollment) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4"
                          onsubmit="return confirm('Reject this enrollment? The fellow will be notified.')">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">Reason for rejection <span class="text-red-400">*</span></span>
                            <textarea name="review_notes" rows="4" required minlength="10" class="form-input w-full mt-1" placeholder="Why is this being rejected?"></textarea>
                        </label>
                        <button type="submit" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Confirm rejection</button>
                    </form>
                </div>
            @else
                <div class="card p-6 text-dark-400 text-sm">This enrollment has already been {{ $enrollment->status }}. No further review actions available.</div>
            @endunless
        </div>
    </div>
</div>
@endsection
