@extends('layouts.app')

@section('title', 'Your mock job offer')

@section('content')
@php
    $fellow = auth()->user();
    $first = explode(' ', trim($fellow->name))[0];
    $company = $experience?->company() ?? 'I-NNOVA Kickstarter';
    $manager = $experience?->manager();
    $strengths = collect($ready['taught'])->filter(fn ($s) => $s['p']['proof'] >= 0.75)->pluck('label')->take(6);
    $passed = collect($hiringLoop)->where('status', 'passed')->pluck('name');
@endphp
<style>
    .of-paper { position: relative; max-width: 760px; margin: 0 auto; background: #fdfcf7; color: #1f2937; border-radius: 6px; padding: 48px 56px; box-shadow: 0 30px 60px rgba(0,0,0,.45); overflow: hidden; }
    .of-paper::before { content: 'MOCK OFFER'; position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%) rotate(-24deg); font-size: 96px; font-weight: 900; color: rgba(13,148,136,.07); letter-spacing: .06em; white-space: nowrap; pointer-events: none; }
    .of-head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid var(--color-primary-600); padding-bottom: 14px; }
    .of-sig { font-family: 'Segoe Script', 'Brush Script MT', cursive; font-size: 26px; color: #1e3a8a; }
    .of-paper h2 { font-size: 20px; font-weight: 800; margin-top: 20px; }
    .of-paper li { margin: 3px 0; }
    .of-confetti i { position: fixed; top: -12px; width: 9px; height: 14px; border-radius: 2px; animation: of-fall linear forwards; z-index: 50; }
    @keyframes of-fall { to { transform: translateY(105vh) rotate(720deg); } }
    @media print { nav, header, aside, .of-noprint { display: none !important; } .of-paper { box-shadow: none; } }
    @media (max-width: 640px) { .of-paper { padding: 28px 20px; } .of-paper::before { font-size: 56px; } }
</style>
<div class="space-y-5" x-data x-init="(() => { const c = ['#14b8a6','#fbbf24','#f472b6','#60a5fa','#a78bfa']; for (let i = 0; i < 80; i++) { const p = document.createElement('i'); p.style.left = Math.random()*100+'vw'; p.style.background = c[i%5]; p.style.animationDuration = (2+Math.random()*2)+'s'; p.style.animationDelay = Math.random()*.6+'s'; $refs.c.appendChild(p); } setTimeout(() => $refs.c.innerHTML = '', 5000); })()">
    <div class="of-confetti of-noprint" x-ref="c" aria-hidden="true"></div>
    <div class="flex flex-wrap items-center justify-between gap-3 max-w-3xl mx-auto of-noprint">
        <a href="{{ route('career.index') }}" class="text-dark-400 hover:text-white">← Job Market</a>
        <button type="button" onclick="window.print()" class="btn-secondary text-sm">🖨️ Print / save as PDF</button>
    </div>

    <div class="of-paper">
        <div class="of-head">
            <div>
                <p style="font-size: 22px; font-weight: 800; color: var(--color-primary-700)">{{ $experience?->emblem() }} {{ $company }}</p>
                <p style="font-size: 12px; color: #6b7280">{{ $experience?->team() ?? $track->name }} · A training company of I-NNOVA Kickstarter</p>
            </div>
            <p style="font-size: 13px; color: #6b7280; text-align: right">{{ $enrolment->offer_unlocked_at->format('j F Y') }}<br>Ref. MOCK-{{ str_pad((string) $fellow->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <p style="margin-top: 22px">Dear {{ $first }},</p>
        <p style="margin-top: 12px">We are delighted to offer you the role of <strong>{{ $role['title'] }}</strong> in our {{ $experience?->team() ?? 'team' }}. This is a <strong>mock offer</strong>: it celebrates that you now have reviewed proof for <strong>{{ $ready['percent'] }}%</strong> of what real {{ $role['title'] }} adverts ask for, and that you passed the key stages of a real hiring process.</p>

        <h2>Why we would hire you</h2>
        <ul style="list-style: disc; padding-left: 20px">
            @foreach($strengths as $s)<li>{{ $s }}: proven with reviewed work</li>@endforeach
        </ul>

        <h2>Interview stages you passed</h2>
        <ul style="list-style: disc; padding-left: 20px">
            @foreach($passed as $p)<li>{{ $p }}</li>@endforeach
        </ul>

        <h2>Your next steps (the real ones)</h2>
        <ol style="list-style: decimal; padding-left: 20px">
            <li>Make your <a href="{{ route('public.profile.show', $fellow) }}" style="color: var(--color-primary-700); text-decoration: underline">portfolio</a> public and add its link to your CV and LinkedIn.</li>
            <li>Find 5 real junior adverts and check each with "You vs a job advert"; apply to the ones above 70%.</li>
            <li>Close the gaps that remain{{ $ready['gap'] ? ', starting with ' . $ready['gap']['label'] : '' }}.</li>
            <li>Ask the I-NNOVA team for a referral or a recruiter introduction.</li>
        </ol>

        <p style="margin-top: 22px">We are proud of how far you have come. Now go and get the real one.</p>
        <p style="margin-top: 18px">Warm regards,</p>
        <p class="of-sig">{{ $manager['name'] ?? 'The I-NNOVA team' }}</p>
        <p style="font-size: 13px; color: #6b7280">{{ $manager['title'] ?? 'I-NNOVA Kickstarter' }}</p>
        <p style="margin-top: 26px; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px">This is not an employment contract. {{ $company }} is the fictional company of the I-NNOVA Kickstarter {{ $track->name }} track; this letter recognises job-readiness, measured against real job adverts.</p>
    </div>
</div>
@endsection
