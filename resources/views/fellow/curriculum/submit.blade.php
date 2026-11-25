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

        {{-- AI use disclosure --}}
        <div class="card p-6" x-data="{ used: @js(old('ai_used')) }">
            <h3 class="text-white font-semibold mb-1">🤖 Did you use AI tools for this work? <span class="text-red-400">*</span></h3>
            <p class="text-dark-400 text-sm mb-4">
                AI tools (ChatGPT, Copilot, Gemini…) are allowed at IKS when you say how you used them. Honest answers are never penalised.
                What matters is that the work reflects your own understanding: you may be asked to explain it.
            </p>
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dark-600 cursor-pointer" :class="used === 'no' ? 'border-primary-500 bg-primary-500/10' : ''">
                    <input type="radio" name="ai_used" value="no" x-model="used" required class="accent-primary-500">
                    <span class="text-dark-200 text-sm">No</span>
                </label>
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg border border-dark-600 cursor-pointer" :class="used === 'yes' ? 'border-primary-500 bg-primary-500/10' : ''">
                    <input type="radio" name="ai_used" value="yes" x-model="used" class="accent-primary-500">
                    <span class="text-dark-200 text-sm">Yes</span>
                </label>
            </div>
            @error('ai_used')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
            <div x-show="used === 'yes'" x-cloak class="mt-4">
                <label class="block text-dark-300 text-sm mb-1">How did you use them?</label>
                <textarea name="ai_used_how" rows="3" maxlength="1000"
                          placeholder="e.g. I asked ChatGPT to explain media queries, then wrote the CSS myself. Copilot suggested the grid template."
                          class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('ai_used_how') }}</textarea>
                @error('ai_used_how')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Pre-submit check --}}
        <div class="card p-6" x-data="precheck('{{ route('curriculum.precheck', $progress) }}', '{{ csrf_token() }}')">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-white font-semibold">🔍 Check my work first</h3>
                    <p class="text-dark-400 text-sm mt-1 max-w-xl">Before you submit, the AI compares what you're about to send with this activity's "What to submit", self-check and grading criteria, and points out anything missing. It doesn't grade you, and it can't open links, so it only checks that a link looks right.</p>
                </div>
                <button type="button" class="btn-secondary flex-shrink-0" @click="run()" :disabled="busy" x-text="busy ? 'Checking...' : 'Check my work'"></button>
            </div>
            <p class="text-amber-300 text-sm mt-3" x-show="error" x-text="error" x-cloak></p>
            <template x-if="result">
                <div class="mt-4 p-4 rounded-lg bg-dark-800 space-y-2">
                    <p class="font-semibold" :class="result.ready ? 'text-green-400' : 'text-amber-300'"
                       x-text="(result.ready ? '✅ Looks ready to submit. ' : '⚠️ A few things to look at first. ') + (result.summary || '')"></p>
                    <ul class="space-y-1.5">
                        <template x-for="(item, i) in result.items" :key="i">
                            <li class="text-sm text-dark-200 flex gap-2">
                                <span x-text="item.status === 'ok' ? '✅' : (item.status === 'missing' ? '❌' : '🔎')"></span>
                                <span x-text="item.text"></span>
                            </li>
                        </template>
                    </ul>
                    <p class="text-sm text-dark-200" x-show="result.tip"><span class="text-primary-300">Next step:</span> <span x-text="result.tip"></span></p>
                    <p class="text-dark-500 text-xs" x-text="'This check is only a helper: the team makes the real review. ' + (result.remaining ?? '') + ' checks left today.'"></p>
                </div>
            </template>
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
function precheck(url, csrf) {
    return {
        busy: false, error: '', result: null,
        async run() {
            const form = this.$el.closest('form');
            const val = (n) => (form.querySelector('[name="' + n + '"]') || {}).value || '';
            const fileInput = form.querySelector('input[type=file]');
            const files = fileInput ? Array.from(fileInput.files).map(f => f.name) : [];
            this.busy = true; this.error = '';
            try {
                const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ url: val('evidence_url'), text: val('evidence_text'), reflection: val('reflection'), files }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.result = d; }
                else { this.error = d.error || (res.status === 429 ? 'Too many checks in a minute. Wait a moment.' : 'Could not check right now. Try again.'); }
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
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
