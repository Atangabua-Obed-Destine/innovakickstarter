@extends('layouts.app')

@section('title', 'Submit — ' . $progress->curriculumActivity->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('curriculum.index') }}" class="hover:text-white transition">Curriculum</a>
            <span>/</span>
            <a href="{{ route('curriculum.activity.show', $progress->curriculumActivity) }}" class="hover:text-white transition">
                {{ Str::limit($progress->curriculumActivity->title, 30) }}
            </a>
            <span>/</span>
            <span class="text-primary-400">Submit</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Submit: {{ $progress->curriculumActivity->title }}</h1>
        <p class="text-dark-400 mt-1">
            {{ $progress->curriculumActivity->milestone->title ?? '' }}
            · {{ $progress->curriculumActivity->type?->label() ?? '' }}
        </p>
    </div>

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

    <!-- Deadline Warning -->
    @if($progress->deadline_at)
    <div class="p-4 rounded-lg border
        {{ $progress->isPastDeadline ? 'bg-red-500/10 border-red-500/30' : 'bg-dark-800 border-dark-700' }}">
        <div class="flex items-center gap-2">
            <span class="{{ $progress->isPastDeadline ? 'text-red-400' : 'text-dark-400' }}">
                {{ $progress->isPastDeadline ? '⚠️' : '⏰' }}
                Deadline: {{ $progress->deadline_at->format('M d, Y g:i A') }}
            </span>
            @if($progress->isPastDeadline)
                <span class="text-red-400 text-sm font-medium ml-auto">
                    Late submissions lose {{ $progress->curriculumActivity->late_penalty_percent ?? 20 }}% points
                </span>
            @else
                <span class="text-dark-400 text-sm ml-auto">{{ $progress->daysRemaining ?? '' }} days remaining</span>
            @endif
        </div>
    </div>
    @endif

    <form action="{{ route('curriculum.submit', $progress) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Evidence Sections -->
        {{-- URL --}}
        @if(empty($evidenceTypes) || in_array('url', $evidenceTypes) || in_array('github_repo', $evidenceTypes) || in_array('github_commit', $evidenceTypes))
        <div class="card p-6">
            <h3 class="text-white font-semibold mb-3">🔗 Link / URL</h3>
            <input type="url" name="evidence_url" value="{{ old('evidence_url') }}"
                   placeholder="https://github.com/your-repo or project URL"
                   class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            <p class="text-dark-500 text-xs mt-1">GitHub repo, deployed project, or relevant link</p>
        </div>
        @endif

        {{-- Text Evidence --}}
        @if(empty($evidenceTypes) || in_array('text', $evidenceTypes))
        <div class="card p-6">
            <h3 class="text-white font-semibold mb-3">📝 Written Evidence</h3>
            <textarea name="evidence_text" rows="6" placeholder="Describe your work, approach, and what you learned..."
                      class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('evidence_text') }}</textarea>
            <p class="text-dark-500 text-xs mt-1">Explain your approach, challenges faced, and key learnings (max 5000 chars)</p>
        </div>
        @endif

        {{-- File Upload --}}
        @if(empty($evidenceTypes) || in_array('file_upload', $evidenceTypes) || in_array('screenshot', $evidenceTypes) || in_array('video', $evidenceTypes))
        <div class="card p-6">
            <h3 class="text-white font-semibold mb-3">📎 File Uploads</h3>
            <div class="border-2 border-dashed border-dark-600 rounded-lg p-8 text-center hover:border-dark-500 transition">
                <input type="file" name="evidence_files[]" multiple id="evidence_files" class="hidden"
                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip,.mp4,.mov">
                <label for="evidence_files" class="cursor-pointer">
                    <div class="text-3xl mb-2">📁</div>
                    <p class="text-white text-sm font-medium">Click to upload files</p>
                    <p class="text-dark-500 text-xs mt-1">JPG, PNG, PDF, DOC, ZIP, MP4. Max 10MB each, up to 5 files</p>
                </label>
            </div>
            <div id="file-list" class="mt-3 space-y-1"></div>
        </div>
        @endif

        {{-- Reflection --}}
        <div class="card p-6">
            <h3 class="text-white font-semibold mb-3">💭 Reflection</h3>
            <textarea name="reflection" rows="4"
                      placeholder="What did you learn? What would you do differently? How does this connect to your career goals?"
                      class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('reflection') }}</textarea>
            <p class="text-dark-500 text-xs mt-1">A short reflection helps reviewers understand your growth</p>
        </div>

        {{-- Review Info --}}
        <div class="card p-4 bg-dark-800/50">
            <div class="flex items-center gap-3 text-sm">
                <span class="text-dark-400">📋 This activity will be reviewed by:</span>
                @if($progress->curriculumActivity->requires_peer_review)
                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded-full">Peer Review</span>
                @endif
                <span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 text-xs rounded-full">Admin/Mentor Review</span>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-3">
            <a href="{{ route('curriculum.activity.show', $progress->curriculumActivity) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">🚀 Submit for Review</button>
        </div>
    </form>
</div>

<script>
document.getElementById('evidence_files')?.addEventListener('change', function(e) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 text-sm text-dark-300 bg-dark-800 rounded-lg px-3 py-2';
        div.innerHTML = `<span>📄</span> ${file.name} <span class="text-dark-500 ml-auto">${(file.size / 1024 / 1024).toFixed(1)} MB</span>`;
        list.appendChild(div);
    });
});
</script>
@endsection
