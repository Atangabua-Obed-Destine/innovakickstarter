<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $firstName = explode(' ', trim($fellow->name))[0];
        $description = trim(($fellow->headline ? $fellow->headline . '. ' : '') . ($levelTitle ? $levelTitle . ' in the ' . ($track?->name ?? '') . ' track of I-NNOVA Kickstarter.' : ''));
    @endphp
    <title>{{ $fellow->name }} · Portfolio · I-NNOVA Kickstarter</title>
    <meta name="description" content="{{ $description }}">
    <meta property="og:title" content="{{ $fellow->name }} · {{ $levelTitle }}">
    <meta property="og:description" content="{{ $description }} Every project here was reviewed by the I-NNOVA team.">
    <meta property="og:type" content="profile">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $experience?->themeStyle() }}
    <style>
        .pf-section h2 { color: #fff; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px; }
        .pf-item { padding: 14px 16px; border-radius: 12px; background: rgba(15, 20, 29, 0.55); border: 1px solid rgba(148, 163, 184, 0.12); }
        .pf-cert { padding: 14px 16px; border-radius: 12px; border: 1px solid color-mix(in srgb, var(--color-primary-500) 40%, transparent);
            background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-600) 20%, #0f141d), #0f141d); }
        @media print { nav, .pf-noprint { display: none !important; } }
    </style>
</head>
<body class="bg-dark-900 text-dark-300 antialiased">
    @include('partials.track.styles')
    <nav class="border-b border-dark-700 pf-noprint">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-white font-semibold">I-NNOVA Kickstarter</a>
            <span class="text-dark-400 text-sm">Verified portfolio</span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 space-y-6">
        <div class="tx-hero">
            <div class="relative flex flex-col sm:flex-row sm:items-center gap-5" style="z-index: 1;">
                <div class="tx-avatar" style="width: 72px; height: 72px; font-size: 26px;">{{ strtoupper(mb_substr($fellow->name, 0, 1)) }}{{ strtoupper(mb_substr(explode(' ', trim($fellow->name))[1] ?? '', 0, 1)) }}</div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-white">{{ $fellow->name }}</h1>
                    @if($fellow->headline)<p class="text-dark-200 mt-1">{{ $fellow->headline }}</p>@endif
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        @if($track)
                            <span class="tx-chip">{{ $experience?->emblem() }} {{ $levelTitle }} · {{ $track->name }}</span>
                        @endif
                        @if($fellow->location)<span class="text-dark-400 text-sm">📍 {{ $fellow->location }}</span>@endif
                        @foreach($links as $label => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-primary-300 hover:text-white text-sm underline">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                @if($fellowTrack)
                    <div class="text-center">
                        <p class="text-4xl font-bold text-white">{{ rtrim(rtrim(number_format((float) $fellowTrack->score, 1), '0'), '.') }}</p>
                        <p class="text-dark-400 text-xs">Career Capital score<br>out of 100</p>
                    </div>
                @endif
            </div>
        </div>

        @if($fellow->bio)
            <div class="card p-6 pf-section"><h2>About</h2><p class="text-dark-200 whitespace-pre-line">{{ $fellow->bio }}</p></div>
        @endif

        @if($certificates->isNotEmpty())
            <div class="pf-section">
                <h2>🎓 Certificates</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach($certificates as $certificate)
                        <a href="{{ route('certificates.show', $certificate) }}" class="pf-cert block">
                            <p class="text-white font-semibold">{{ $certificate->skill_label }}</p>
                            <p class="text-dark-400 text-sm">{{ $certificate->track?->name }} · issued {{ $certificate->issued_at->format('M Y') }} · ID {{ $certificate->code() }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(count($skills) >= 3)
            @include('partials.track.skill-map', ['skills' => $skills, 'experience' => $experience, 'heading' => $firstName . "'s " . ($experience?->role() ?? 'track') . ' skills', 'subheading' => 'How much of each skill has been completed and approved by the I-NNOVA team.'])
        @endif

        @if($projects->isNotEmpty())
            <div class="pf-section">
                <h2>🛠️ Projects <span class="text-dark-500 text-sm font-normal">reviewed and approved by the I-NNOVA team</span></h2>
                <div class="grid md:grid-cols-2 gap-3">
                    @foreach($projects as $p)
                        <div class="pf-item">
                            <p class="text-white font-medium">{{ $p['title'] }}</p>
                            <p class="text-dark-500 text-xs mt-0.5">{{ $p['chapter'] }} · {{ $p['date']?->format('M Y') }}</p>
                            <p class="text-dark-300 text-sm mt-2">{{ $p['summary'] }}</p>
                            @if($p['url'])<a href="{{ $p['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="text-primary-400 hover:text-primary-300 text-sm mt-2 inline-block">View the work →</a>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($briefs->isNotEmpty())
            <div class="pf-section">
                <h2>💼 Employer challenges</h2>
                <div class="space-y-2">
                    @foreach($briefs as $b)
                        <div class="pf-item flex flex-wrap items-center justify-between gap-2">
                            <span class="text-dark-200">{{ $b->status === 'winner' ? '🏆 Winner' : '⭐ Shortlisted' }}: <span class="text-white">{{ $b->brief?->title }}</span> <span class="text-dark-500">· {{ $b->brief?->company_name }}</span></span>
                            <a href="{{ $b->url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-primary-400 hover:text-primary-300 text-sm">See the solution →</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($presentations->isNotEmpty())
            <div class="pf-section">
                <h2>🎤 Presentations</h2>
                <div class="space-y-2">
                    @foreach($presentations as $presentation)
                        <div class="pf-item">
                            <p class="text-white font-medium">{{ $presentation->title }}</p>
                            <p class="text-dark-400 text-sm">{{ $presentation->kindLabel() }} · {{ $presentation->event_name }} · {{ $presentation->presented_at?->format('M Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($posts->isNotEmpty())
            <div class="pf-section">
                <h2>📣 Writing &amp; posts</h2>
                <div class="space-y-2">
                    @foreach($posts as $p)
                        <a href="{{ $p['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="pf-item flex items-center justify-between gap-3">
                            <span class="text-dark-200">{{ $p['title'] }}</span><span class="text-primary-400 text-sm flex-shrink-0">Read →</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($practice && $practice['solved'] > 0)
            <div class="card p-5 flex flex-wrap items-center gap-6">
                <p class="text-white font-semibold">Practice Lab</p>
                <p class="text-dark-300 text-sm">{{ $practice['solved'] }} hands-on challenges solved (SQL, Python, statistics)</p>
                <p class="text-dark-300 text-sm">{{ $practice['xp'] }} practice XP</p>
            </div>
        @endif

        <p class="text-dark-500 text-xs text-center pt-4">
            Everything on this page comes from I-NNOVA Kickstarter: projects and presentations were reviewed by the I-NNOVA team, and certificates can be verified by their ID.
            Recruiters: contact {{ $firstName }} through LinkedIn or the I-NNOVA recruiter platform.
        </p>
    </main>
</body>
</html>
