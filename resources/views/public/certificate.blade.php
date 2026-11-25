<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->skill_label }} certificate · {{ $certificate->fellow->name }} · I-NNOVA Kickstarter</title>
    <meta property="og:title" content="{{ $certificate->fellow->name }} earned the {{ $certificate->skill_label }} certificate">
    <meta property="og:description" content="{{ $certificate->track?->name }} track · I-NNOVA Kickstarter · Certificate ID {{ $certificate->code() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $experience?->themeStyle() }}
    <style>
        .cert { position: relative; max-width: 860px; margin: 0 auto; padding: 48px 40px; border-radius: 18px; text-align: center; color: #1c2230;
            background: #fffdf8; border: 10px solid var(--color-primary-600); box-shadow: 0 30px 70px rgba(0,0,0,.45); }
        .cert::before { content: ""; position: absolute; inset: 10px; border: 2px solid color-mix(in srgb, var(--color-primary-500) 50%, transparent); border-radius: 10px; pointer-events: none; }
        .cert h1 { font-family: "Playfair Display", Georgia, serif; font-size: 34px; color: #10151f; margin: 6px 0 2px; }
        .cert .name { font-family: "Playfair Display", Georgia, serif; font-size: 40px; color: var(--color-primary-800); margin: 18px 0 6px; }
        .cert .skill { font-size: 22px; font-weight: 700; color: #10151f; }
        .cert .muted { color: #5b6475; font-size: 14px; }
        .cert .seal { width: 84px; height: 84px; margin: 22px auto 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 34px;
            background: radial-gradient(circle, var(--color-primary-400), var(--color-primary-700)); color: #fff; }
        @media (max-width: 640px) { .cert { padding: 32px 18px; } .cert .name { font-size: 30px; } .cert h1 { font-size: 26px; } }
        @media print { body { background: #fff !important; } .no-print { display: none !important; } .cert { box-shadow: none; } }
    </style>
</head>
<body class="bg-dark-900 antialiased">
    <main class="px-4 py-10 space-y-6">
        <div class="cert">
            <p class="muted" style="letter-spacing: .2em; text-transform: uppercase;">I-NNOVA Kickstarter</p>
            <h1>Certificate of Skill</h1>
            <p class="muted">This certifies that</p>
            <p class="name">{{ $certificate->fellow->name }}</p>
            <p class="muted">has completed and passed every reviewed activity for the skill</p>
            <p class="skill" style="margin-top: 8px;">{{ $certificate->skill_label }}</p>
            <p class="muted" style="margin-top: 4px;">{{ $certificate->track?->name }} track · {{ $certificate->activities_completed }} activities approved by the I-NNOVA team</p>
            <div class="seal" aria-hidden="true">{{ $experience?->emblem() ?? '🎓' }}</div>
            <p class="muted" style="margin-top: 18px;">Issued {{ $certificate->issued_at->format('j F Y') }} · Certificate ID <strong>{{ $certificate->code() }}</strong></p>
            <p class="muted" style="font-size: 12px;">Verify at {{ route('certificates.show', $certificate) }}</p>
        </div>

        <div class="no-print max-w-3xl mx-auto text-center space-y-3">
            @if($certificate->revoked_at)
                <p class="text-red-400 font-semibold">This certificate was revoked on {{ $certificate->revoked_at->format('j F Y') }}.</p>
            @else
                <p class="text-green-400 font-semibold">✓ Valid certificate, issued by I-NNOVA Kickstarter.</p>
            @endif
            <div class="flex flex-wrap items-center justify-center gap-3">
                @if(auth()->id() === $certificate->fellow_id && !$certificate->revoked_at)
                    <a href="{{ $certificate->linkedInUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Add to LinkedIn</a>
                @endif
                <button type="button" onclick="window.print()" class="btn btn-secondary">Print or save as PDF</button>
                @if($certificate->fellow->is_public)
                    <a href="{{ route('public.profile.show', $certificate->fellow) }}" class="btn btn-secondary">View {{ explode(' ', trim($certificate->fellow->name))[0] }}'s portfolio</a>
                @endif
            </div>
        </div>
    </main>
</body>
</html>
