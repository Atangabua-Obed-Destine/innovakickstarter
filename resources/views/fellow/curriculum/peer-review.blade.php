@extends('layouts.app')

@section('title', 'Peer Review')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('curriculum.index') }}" class="hover:text-white transition">Curriculum</a>
            <span>/</span>
            <span class="text-primary-400">Peer Review</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Peer Review</h1>
        <p class="text-dark-400 mt-1">Review {{ $progress->fellow->name ?? 'your partner' }}'s submission</p>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400">{{ session('error') }}</div>
    @endif

    <!-- Submission Info -->
    <div class="card p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-lg font-bold">
                {{ strtoupper(substr($progress->fellow->name ?? 'P', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-white font-semibold">{{ $progress->fellow->name ?? 'Partner' }}</h3>
                <p class="text-dark-400 text-sm mt-0.5">{{ $progress->curriculumActivity->title }}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-dark-500">
                    <span>{{ $progress->curriculumActivity->type?->icon() }} {{ $progress->curriculumActivity->type?->label() }}</span>
                    <span>•</span>
                    <span>{{ $progress->curriculumActivity->difficulty_level?->icon() }} {{ $progress->curriculumActivity->difficulty_level?->label() }}</span>
                    <span>•</span>
                    <span>Submitted {{ $progress->submitted_at?->diffForHumans() ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Partner's Evidence -->
    <div class="card p-6">
        <h3 class="text-white font-semibold mb-4">Submitted Evidence</h3>

        @if($progress->evidence_url)
        <div class="mb-4">
            <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">URL</p>
            <a href="{{ $progress->evidence_url }}" target="_blank" rel="noopener"
               class="text-primary-400 hover:text-primary-300 text-sm break-all">
                {{ $progress->evidence_url }}
            </a>
        </div>
        @endif

        @if($progress->evidence_text)
        <div class="mb-4">
            <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">Written Evidence</p>
            <div class="bg-dark-800 rounded-lg p-4 text-dark-300 text-sm leading-relaxed">
                {!! nl2br(e($progress->evidence_text)) !!}
            </div>
        </div>
        @endif

        @if($progress->evidence_files && count($progress->evidence_files) > 0)
        <div class="mb-4">
            <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-2">Files</p>
            <div class="space-y-2">
                @foreach($progress->evidence_files as $file)
                <a href="{{ asset('storage/' . $file) }}" target="_blank"
                   class="flex items-center gap-2 text-primary-400 hover:text-primary-300 text-sm bg-dark-800 rounded-lg px-4 py-2">
                    📄 {{ basename($file) }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($progress->reflection)
        <div>
            <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">Reflection</p>
            <div class="bg-dark-800 rounded-lg p-4 text-dark-300 text-sm italic">
                {!! nl2br(e($progress->reflection)) !!}
            </div>
        </div>
        @endif
    </div>

    <!-- Review Form -->
    <form action="{{ route('curriculum.peer-review.submit', $progress) }}" method="POST" class="space-y-6">
        @csrf

        <div class="card p-6">
            <h3 class="text-white font-semibold mb-4">Your Review</h3>

            {{-- Star Rating --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-dark-300 mb-3">Rating *</label>
                <div class="flex items-center gap-2" id="star-rating">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" data-rating="{{ $i }}"
                            class="star-btn text-3xl transition hover:scale-110 {{ $i <= 3 ? 'text-amber-400' : 'text-dark-600' }}">
                        ★
                    </button>
                    @endfor
                    <span id="rating-label" class="text-dark-400 text-sm ml-3">Good</span>
                </div>
                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', 3) }}">
            </div>

            {{-- Feedback --}}
            <div>
                <label for="feedback" class="block text-sm font-medium text-dark-300 mb-2">Feedback</label>
                <textarea name="feedback" id="feedback" rows="5"
                          placeholder="Share constructive feedback: What did they do well? Any suggestions for improvement?"
                          class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('feedback') }}</textarea>
                <p class="text-dark-500 text-xs mt-1">Be constructive and specific. Your feedback helps your partner grow</p>
            </div>
        </div>

        {{-- Guidelines --}}
        <div class="card p-4 bg-dark-800/50">
            <div class="flex items-start gap-3">
                <span class="text-xl">💡</span>
                <div class="text-sm text-dark-400">
                    <p class="font-medium text-dark-300 mb-1">Review Guidelines</p>
                    <ul class="space-y-0.5 text-xs">
                        <li>• Focus on the quality of work and effort shown</li>
                        <li>• Be specific about what was done well</li>
                        <li>• Provide actionable suggestions for improvement</li>
                        <li>• Rating of 3+ means the work meets expectations</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('curriculum.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Submit Review</button>
        </div>
    </form>
</div>

<script>
const labels = { 1: 'Needs Work', 2: 'Below Average', 3: 'Good', 4: 'Great', 5: 'Excellent!' };
const ratingInput = document.getElementById('rating-input');
const ratingLabel = document.getElementById('rating-label');

document.querySelectorAll('.star-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        ratingInput.value = rating;
        ratingLabel.textContent = labels[rating];

        document.querySelectorAll('.star-btn').forEach((b, i) => {
            b.classList.toggle('text-amber-400', i < rating);
            b.classList.toggle('text-dark-600', i >= rating);
        });
    });
});
</script>
@endsection
