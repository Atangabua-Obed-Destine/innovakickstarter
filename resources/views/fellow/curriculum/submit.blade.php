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
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h3 class="text-white font-semibold">📝 Written Evidence</h3>
                <button type="button" class="btn-secondary text-xs" style="padding: .35rem .7rem;" onclick="useStructure()">✍️ Use the 4-part structure</button>
            </div>
            <details class="mb-3 text-sm">
                <summary class="cursor-pointer text-primary-300">How to explain your work so anyone understands it</summary>
                <ul class="mt-2 space-y-1 text-dark-300" style="list-style: disc; padding-left: 1.1rem;">
                    <li><b class="text-white">Start with the answer.</b> "Profit was highest in Q2" comes before how you found it.</li>
                    <li><b class="text-white">Use numbers with units.</b> "Sales rose 12% (from 1.2M to 1.35M FCFA)", not "sales rose a lot".</li>
                    <li><b class="text-white">Explain each technical word once,</b> in a short sentence: "the median (the middle value when sorted)".</li>
                    <li><b class="text-white">Short sentences.</b> One idea per sentence. If you would not say it aloud, rewrite it.</li>
                    <li><b class="text-white">Say what it means.</b> Finish with a decision someone could make, and what you would check next.</li>
                </ul>
            </details>
            <textarea name="evidence_text" id="evidence_text" rows="8" placeholder="Describe your work, approach, and what you learned..."
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

        {{-- Backend projects: API contract check --}}
        @if(!empty($progress->curriculumActivity->api_contract['checks']))
            @include('partials.lesson.api-contract', ['progress' => $progress])
        @endif

        {{-- Show & Tell video (public YouTube link) --}}
        @if($progress->curriculumActivity->requires_video)
        <div class="card p-6" id="show-tell" style="border-color: rgba(239,68,68,.35);" x-data="showTell('{{ route('curriculum.video-check', $progress) }}', '{{ csrf_token() }}', @js(old('video_url', '')))" x-init="if (url) check()">
            <h3 class="text-white font-semibold mb-1">🎬 Show &amp; Tell video <span class="text-red-400">*</span></h3>
            <p class="text-dark-400 text-sm mb-4">This is a chapter project, so explain it on video: record your screen and your voice, publish it <b class="text-white">Public</b> on your own YouTube channel, then paste the link. There are no video uploads on the IKS portal.</p>

            <details class="mb-4 rounded-lg bg-dark-800/60 p-4" {{ old('video_url') ? '' : 'open' }}>
                <summary class="cursor-pointer text-white font-medium">How to make and publish your video</summary>
                <div class="mt-4">@include('partials.lesson.show-tell-guide', ['activity' => $progress->curriculumActivity])</div>
            </details>

            <label class="block text-dark-300 text-sm mb-1" for="video_url">YouTube link</label>
            <div class="flex gap-2">
                <input type="url" name="video_url" id="video_url" x-model="url" @change="check()" required maxlength="500"
                       placeholder="https://youtu.be/... or https://www.youtube.com/watch?v=..."
                       class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <button type="button" class="btn-secondary flex-shrink-0" @click="check()" :disabled="busy || !url" x-text="busy ? 'Checking...' : 'Check link'"></button>
            </div>
            @error('video_url')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
            <p class="text-amber-300 text-sm mt-2" x-show="error" x-text="error" x-cloak></p>
            <template x-if="info">
                <div class="mt-3 flex gap-3 items-center p-3 rounded-lg bg-dark-800">
                    <img :src="'https://i.ytimg.com/vi/' + info.id + '/mqdefault.jpg'" alt="" style="width: 120px; border-radius: .4rem; flex-shrink: 0;">
                    <div class="min-w-0 text-sm">
                        <p class="text-green-400 font-semibold" x-text="info.verified ? '✅ YouTube found your video' : '✅ Link looks right (YouTube could not be reached to confirm; your reviewer will open it)'"></p>
                        <p class="text-white truncate" x-show="info.title" x-text="info.title"></p>
                        <p class="text-dark-400 text-xs" x-show="info.channel" x-text="'Channel: ' + info.channel"></p>
                    </div>
                </div>
            </template>

            <label class="flex items-start gap-2 mt-4 cursor-pointer">
                <input type="checkbox" name="video_public" value="1" required {{ old('video_public') ? 'checked' : '' }} class="mt-1 accent-primary-500">
                <span class="text-dark-200 text-sm">My video is <b class="text-white">Public</b> on my own YouTube channel, it is my voice, and it explains my own work. (YouTube treats Public and Unlisted links the same, so your reviewer will check.)</span>
            </label>
            @error('video_public')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
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
                    <p class="text-dark-400 text-sm mt-1 max-w-xl">Before you submit, the AI compares what you're about to send with this activity's "What to submit", self-check and grading criteria, points out anything missing, and tells you how clearly you explain your work. It doesn't grade you, and it can't open links, so it only checks that a link looks right.</p>
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
                    <template x-if="result.clarity">
                        <div class="mt-3 pt-3 border-t border-dark-700 text-sm">
                            <p class="font-semibold" :class="{ 'text-green-400': result.clarity.rating === 'clear', 'text-amber-300': result.clarity.rating === 'okay', 'text-red-400': result.clarity.rating === 'unclear' }"
                               x-text="'🗣️ How clearly you explain it: ' + ({ clear: 'Clear', okay: 'Getting there', unclear: 'Hard to follow' }[result.clarity.rating] || 'Getting there')"></p>
                            <p class="text-dark-300 mt-1" x-show="result.clarity.jargon && result.clarity.jargon.length">
                                Words to explain in plain English the first time you use them:
                                <template x-for="w in result.clarity.jargon" :key="w"><span class="inline-block px-2 py-0.5 rounded-full bg-dark-700 text-dark-100 text-xs mr-1 mt-1" x-text="w"></span></template>
                            </p>
                            <p class="text-dark-200 mt-1" x-show="result.clarity.tip"><span class="text-primary-300">Say it better:</span> <span x-text="result.clarity.tip"></span></p>
                        </div>
                    </template>
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
                    body: JSON.stringify({ url: val('evidence_url'), text: val('evidence_text'), reflection: val('reflection'), video: val('video_url'), files }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.result = d; }
                else { this.error = d.error || (res.status === 429 ? 'Too many checks in a minute. Wait a moment.' : 'Could not check right now. Try again.'); }
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
function showTell(checkUrl, csrf, initial) {
    return {
        url: initial || '', busy: false, error: '', info: null, last: '',
        async check() {
            const u = (this.url || '').trim();
            if (!u || u === this.last) return;
            this.busy = true; this.error = ''; this.info = null;
            try {
                const res = await fetch(checkUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ url: u }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.info = d; this.last = u; }
                else { this.error = d.error || (res.status === 429 ? 'Too many checks in a minute. Wait a moment.' : 'Could not check the link right now.'); }
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
function useStructure() {
    const t = document.getElementById('evidence_text');
    if (!t) return;
    const parts = [
        ['WHAT I DID', '(The steps you took, in plain words. Name the tools you used.)'],
        ['WHAT I FOUND', '(Two or three findings, each with a real number and its unit.)'],
        ['WHAT IT MEANS', '(Who should care and what they could decide because of it. Explain any technical word the first time.)'],
        ['WHAT I WOULD DO NEXT', '(One improvement, or one question you would check with more time or data.)'],
    ];
    const missing = parts.filter(p => !t.value.includes(p[0]));
    if (!missing.length) { t.focus(); return; }
    t.value = (t.value.trim() ? t.value.trim() + '\n\n' : '') + missing.map(p => p[0] + '\n' + p[1] + '\n').join('\n');
    const start = t.value.indexOf(missing[0][1]);
    t.focus();
    t.setSelectionRange(start, start + missing[0][1].length);
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
