<div x-data="{ open: false }" class="card overflow-hidden mb-6">
    <button @click="open = !open" class="w-full p-4 flex items-center justify-between bg-dark-800 hover:bg-dark-700 transition text-left">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center text-xl flex-shrink-0">
                {{ $activity->type?->icon() ?? '📋' }}
            </div>
            <div>
                <h2 class="text-white font-semibold flex items-center gap-2">
                    {{ $activity->title }}
                    <span class="px-2 py-0.5 bg-dark-600 text-dark-300 text-xs rounded-full">Activity Details</span>
                </h2>
                <p class="text-dark-400 text-xs mt-0.5">Click to view description, resources, and grading rubric</p>
            </div>
        </div>
        <div class="text-dark-400" :class="{ 'rotate-180': open }">
            <svg class="w-5 h-5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open" x-collapse x-cloak class="border-t border-dark-700 p-6 space-y-6 bg-dark-900/50">
        {{-- Activity Tags --}}
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-dark-800 rounded-lg text-sm border border-dark-700">
                <span>{{ $activity->type?->icon() ?? '' }}</span>
                <span class="text-dark-300">{{ $activity->type?->label() ?? 'Activity' }}</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-dark-800 rounded-lg text-sm border border-dark-700">
                <span>{{ $activity->difficulty_level?->icon() ?? '' }}</span>
                <span class="text-dark-300">{{ $activity->difficulty_level?->label() ?? '' }}</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary-500/10 border border-primary-500/20 rounded-lg text-sm text-primary-400 font-medium">
                🏆 {{ $activity->points }} pts
            </span>
            @if($activity->is_required)
                <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full font-medium border border-red-500/30">Required</span>
            @endif
        </div>

        {{-- Description --}}
        <div class="space-y-3">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-primary-400">📖</span> Description
            </h3>
            <div class="text-dark-300 text-sm leading-relaxed prose prose-invert max-w-none border-l-2 border-dark-700 pl-4">
                {!! nl2br(e($activity->description)) !!}
            </div>
        </div>

        {{-- Instructions --}}
        @if($activity->instructions)
        <div class="space-y-3 pt-4 border-t border-dark-700/50">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-blue-400">📋</span> Step-by-Step Instructions
            </h3>
            <div class="text-dark-300 text-sm leading-relaxed prose prose-invert max-w-none border-l-2 border-dark-700 pl-4">
                {!! nl2br(e($activity->instructions)) !!}
            </div>
        </div>
        @endif

        {{-- Learning Resources --}}
        @if(!empty($activity->resources) && is_array($activity->resources))
        <div class="space-y-3 pt-4 border-t border-dark-700/50">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-indigo-400">📚</span> Learning Resources
            </h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($activity->resources as $resource)
                    @php
                        $res = is_array($resource) ? $resource : ['type' => 'link', 'title' => 'Resource', 'content' => $resource];
                        $type = $res['type'] ?? 'link';
                        $title = $res['title'] ?? 'Resource';
                        $content = $res['content'] ?? '';
                        
                        if ($type === 'link' && (str_contains($content, 'youtube.com') || str_contains($content, 'youtu.be'))) {
                            $type = 'youtube';
                        }
                    @endphp

                    @if($type === 'youtube')
                        @php
                            // Extract video ID for embed
                            $videoId = '';
                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $content, $match)) {
                                $videoId = $match[1];
                            }
                        @endphp
                        @if($videoId)
                            <style>
                                @keyframes watermarkFloat {
                                    0% { top: 10%; left: 5%; transform: translate(0, 0) rotate(-10deg); }
                                    25% { top: 80%; left: 70%; transform: translate(-100%, -100%) rotate(-10deg); }
                                    50% { top: 15%; left: 80%; transform: translate(-100%, 0) rotate(-10deg); }
                                    75% { top: 75%; left: 10%; transform: translate(0, -100%) rotate(-10deg); }
                                    100% { top: 10%; left: 5%; transform: translate(0, 0) rotate(-10deg); }
                                }
                                .video-watermark {
                                    position: absolute;
                                    pointer-events: none;
                                    z-index: 10;
                                    color: rgba(255, 255, 255, 0.12);
                                    font-size: clamp(1rem, 3vw, 2.5rem);
                                    font-weight: 900;
                                    text-transform: uppercase;
                                    text-align: center;
                                    line-height: 1.1;
                                    white-space: nowrap;
                                    animation: watermarkFloat 35s linear infinite;
                                    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
                                    mix-blend-mode: overlay;
                                }
                            </style>
                            <div class="rounded-lg overflow-hidden border border-dark-700 bg-black w-full relative group" style="padding-top: 56.25%;">
                                <iframe src="https://www.youtube.com/embed/{{ $videoId }}?rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="absolute top-0 left-0 w-full h-full z-0"></iframe>
                                
                                {{-- Watermark Overlay --}}
                                <div class="video-watermark pointer-events-none">
                                    I-NNOVA KICKSTARTER<br>ACCELERATOR
                                </div>
                            </div>
                        @else
                            <a href="{{ $content }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-red-500/50 hover:bg-dark-800 transition group">
                                <span class="w-10 h-10 rounded bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0">▶️</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                                    <p class="text-dark-500 text-xs truncate">YouTube Video</p>
                                </div>
                            </a>
                        @endif
                    @elseif($type === 'file')
                        <a href="{{ asset(ltrim($content, '/')) }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-green-500/50 hover:bg-dark-800 transition group">
                            <span class="w-10 h-10 rounded bg-green-500/10 text-green-400 flex items-center justify-center flex-shrink-0">📄</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                                <p class="text-dark-500 text-xs truncate">File Attachment</p>
                            </div>
                        </a>
                    @else
                        <a href="{{ $content }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-blue-500/50 hover:bg-dark-800 transition group">
                            <span class="w-10 h-10 rounded bg-blue-500/10 text-blue-400 flex items-center justify-center flex-shrink-0">🔗</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                                <p class="text-dark-500 text-xs truncate">{{ $content }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- What to Submit --}}
        @if($activity->evidence_requirements && count($activity->evidence_requirements) > 0)
        <div class="space-y-3 pt-4 border-t border-dark-700/50">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-green-400">📎</span> Evidence Requirements
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($activity->evidence_requirements as $evType)
                @php $evEnum = \App\Enums\EvidenceType::tryFrom($evType); @endphp
                <div class="flex items-center gap-3 text-dark-300 text-sm bg-dark-800 rounded-lg px-4 py-3 border border-dark-700">
                    <span class="text-lg">{{ $evEnum?->icon() ?? '📎' }}</span>
                    <span class="font-medium">{{ $evEnum?->label() ?? $evType }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Evaluation Criteria --}}
        @if($activity->evaluation_rubric && count($activity->evaluation_rubric) > 0)
        <div class="space-y-3 pt-4 border-t border-dark-700/50">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-amber-400">⚖️</span> Evaluation Rubric
            </h3>
            <div class="grid gap-3">
                @foreach($activity->evaluation_rubric as $criteria)
                <div class="flex items-start gap-3 bg-dark-800 rounded-lg p-4 border border-dark-700">
                    <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400 text-xs font-bold flex-shrink-0 border border-amber-500/30">
                        {{ $criteria['weight'] ?? 25 }}%
                    </div>
                    <div>
                        <p class="text-white text-sm font-medium">{{ $criteria['criterion'] ?? 'Criterion' }}</p>
                        @if(!empty($criteria['description']))
                        <p class="text-dark-400 text-sm mt-1">{{ $criteria['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        {{-- Deadline & Policies --}}
        @if($activity->deadline_days || $activity->late_penalty_percent || $activity->grace_period_days)
        <div class="space-y-3 pt-4 border-t border-dark-700/50">
            <h3 class="text-white font-semibold flex items-center gap-2 text-lg">
                <span class="text-orange-400">⏰</span> Policies
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @if($activity->deadline_days)
                <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                    <p class="text-2xl font-bold text-white">{{ $activity->deadline_days }}</p>
                    <p class="text-dark-400 text-xs mt-1">days to complete</p>
                </div>
                @endif
                @if($activity->grace_period_days)
                <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                    <p class="text-2xl font-bold text-amber-400">+{{ $activity->grace_period_days }}</p>
                    <p class="text-dark-400 text-xs mt-1">grace period (days)</p>
                </div>
                @endif
                @if($activity->late_penalty_percent)
                <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                    <p class="text-2xl font-bold text-red-400">-{{ $activity->late_penalty_percent }}%</p>
                    <p class="text-dark-400 text-xs mt-1">late penalty</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
