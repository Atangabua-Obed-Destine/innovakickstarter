@extends('layouts.app')

@php
    $editing = $presentation->exists;
    $links = $presentation->links ?? [];
    $input = 'w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
@endphp

@section('title', $editing ? 'Edit Presentation' : 'Submit a Presentation')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('presentations.index') }}" class="hover:text-white transition">Presentations</a>
            <span>/</span>
            <span class="text-primary-400">{{ $editing ? 'Edit' : 'Submit' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-white">{{ $editing ? 'Edit presentation' : 'Submit a presentation' }}</h1>
        <p class="text-dark-400 mt-1">A project demo, workshop or talk you gave. The I-NNOVA team reviews it and decides how much it adds to your Career Capital.</p>
    </div>

    @if($editing && $presentation->status === \App\Models\Presentation::STATUS_CHANGES_REQUESTED && $presentation->review_feedback)
        <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl">
            <p class="text-amber-400 font-semibold text-sm">Changes requested by the I-NNOVA team</p>
            <p class="text-dark-200 text-sm mt-1 whitespace-pre-line">{{ $presentation->review_feedback }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400">{{ session('error') }}</div>
    @endif

    <form action="{{ $editing ? route('presentations.update', $presentation) : route('presentations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="card p-6 space-y-4">
            <h3 class="text-white font-semibold">The presentation</h3>
            <div>
                <label class="block text-dark-300 text-sm mb-1">Title <span class="text-red-400">*</span></label>
                <input type="text" name="title" maxlength="200" required value="{{ old('title', $presentation->title) }}"
                       placeholder="e.g. Cleaning Nkwen Traders' sales data with pandas" class="{{ $input }}">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Kind <span class="text-red-400">*</span></label>
                    <select name="kind" required class="{{ $input }}">
                        @foreach(\App\Models\Presentation::KINDS as $value => $label)
                            <option value="{{ $value }}" @selected(old('kind', $presentation->kind) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Track <span class="text-red-400">*</span></label>
                    <select name="track_id" required class="{{ $input }}">
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" @selected(old('track_id', $defaultTrackId) === $track->id)>{{ $track->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Event <span class="text-red-400">*</span></label>
                    <input type="text" name="event_name" maxlength="200" required value="{{ old('event_name', $presentation->event_name) }}"
                           placeholder="e.g. I-NNOVA Presentation Day, November 2026" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Date given <span class="text-red-400">*</span></label>
                    <input type="date" name="presented_at" required max="{{ now()->toDateString() }}"
                           value="{{ old('presented_at', $presentation->presented_at?->toDateString()) }}" class="{{ $input }}">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Audience</label>
                    <input type="text" name="audience" maxlength="200" value="{{ old('audience', $presentation->audience) }}"
                           placeholder="e.g. About 40 fellows and 3 mentors" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-dark-300 text-sm mb-1">Co-presenters</label>
                    <input type="text" name="co_presenters" maxlength="500" value="{{ old('co_presenters', $presentation->co_presenters) }}"
                           placeholder="Names, if you presented as a team" class="{{ $input }}">
                    <p class="text-dark-500 text-xs mt-1">Each team member submits their own entry.</p>
                </div>
            </div>
            <div>
                <label class="block text-dark-300 text-sm mb-1">What you presented <span class="text-red-400">*</span></label>
                <textarea name="summary" rows="6" minlength="50" maxlength="5000" required class="{{ $input }}"
                          placeholder="What was it about? What did you show or teach, how did you structure it, what questions came up?">{{ old('summary', $presentation->summary) }}</textarea>
            </div>
            <div>
                <label class="block text-dark-300 text-sm mb-1">What you learned</label>
                <textarea name="learnings" rows="3" maxlength="3000" class="{{ $input }}"
                          placeholder="From preparing and giving it: what went well, what you would change.">{{ old('learnings', $presentation->learnings) }}</textarea>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <div>
                <h3 class="text-white font-semibold">Proof <span class="text-red-400">*</span></h3>
                <p class="text-dark-400 text-sm mt-1">At least one link or file, so the team can see the presentation: slides, a recording, the project repository, or a photo of you presenting.</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach(['slides_url' => ['slides', 'Slides', 'https://docs.google.com/presentation/...'], 'recording_url' => ['recording', 'Recording', 'https://youtube.com/...'], 'repository_url' => ['repository', 'Repository / project', 'https://github.com/...'], 'other_url' => ['other', 'Other link', 'https://...']] as $name => [$key, $label, $placeholder])
                    <div>
                        <label class="block text-dark-300 text-sm mb-1">{{ $label }}</label>
                        <input type="url" name="{{ $name }}" maxlength="500" value="{{ old($name, $links[$key] ?? '') }}" placeholder="{{ $placeholder }}" class="{{ $input }}">
                    </div>
                @endforeach
            </div>

            @if($editing && !empty($presentation->files))
                <div>
                    <p class="text-dark-300 text-sm mb-2">Files already attached (tick to remove)</p>
                    <div class="space-y-2">
                        @foreach($presentation->files as $i => $path)
                            <label class="flex items-center gap-3 text-sm text-dark-200 bg-dark-800 rounded-lg px-4 py-2">
                                <input type="checkbox" name="remove_files[]" value="{{ $path }}" class="accent-primary-500">
                                <span>{{ basename($path) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-dark-300 text-sm mb-1">Upload files</label>
                <input type="file" name="files[]" multiple accept=".pdf,.ppt,.pptx,.key,.odp,.jpg,.jpeg,.png,.webp,.mp4,.mov,.zip"
                       class="block w-full text-sm text-dark-300">
                <p class="text-dark-500 text-xs mt-1">Slides (PDF, PowerPoint), photos, short videos or a ZIP. Max 10MB each, up to 5 files in total.</p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <a href="{{ $editing ? route('presentations.show', $presentation) : route('presentations.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save and send for review' : 'Submit for review' }}</button>
        </div>
    </form>
</div>
@endsection
