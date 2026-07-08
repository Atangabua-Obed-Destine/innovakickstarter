@extends('layouts.app')

@section('title', 'Internship Dossier · ' . ($profile->fellow?->name ?? ''))

@section('content')
@php
    $fellow = $profile->fellow;
    $skills = is_array($fellow?->skills) ? $fellow->skills : [];
    $goals = $skills['goals'] ?? [];
    $primaryGoal = $skills['primary_goal'] ?? null;
    $timeline = $skills['goal_timeline'] ?? null;
    $vision = $skills['goal_success_vision'] ?? null;

    $goalLabels = [
        'first_internship' => ['🎓', 'Land my first internship'],
        'first_job'        => ['💼', 'Land my first full-time job'],
        'career_switch'    => ['🔄', 'Switch to tech (or a new track)'],
        'promotion'        => ['🚀', 'Grow in my current job'],
        'freelance'        => ['💵', 'Freelance & earn on the side'],
        'startup'          => ['🛠️', 'Launch my own product'],
        'portfolio'        => ['🏗️', 'Build a portfolio recruiters trust'],
        'network'          => ['🌐', 'Grow my network in tech'],
    ];
    $timelineLabels = [
        '3_months'  => 'Ready in 3 months',
        '6_months'  => 'Ready in 6 months',
        '12_months' => 'Ready within a year',
        'exploring' => 'Still exploring',
    ];

    $statusStyles = [
        'pending'        => ['label' => 'Pending Review', 'class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-orange-600/20 text-orange-400 border-orange-500/30'],
        'approved'       => ['label' => 'Approved',       'class' => 'bg-green-600/20 text-green-400 border-green-500/30'],
        'rejected'       => ['label' => 'Rejected',       'class' => 'bg-red-600/20 text-red-400 border-red-500/30'],
        'active'         => ['label' => 'Active',         'class' => 'bg-blue-600/20 text-blue-400 border-blue-500/30'],
        'completed'      => ['label' => 'Completed',      'class' => 'bg-primary-600/20 text-primary-400 border-primary-500/30'],
        'withdrawn'      => ['label' => 'Withdrawn',      'class' => 'bg-dark-600/40 text-dark-300 border-dark-500/30'],
    ];
    $s = $statusStyles[$profile->status] ?? ['label' => ucfirst($profile->status), 'class' => 'bg-dark-700 text-dark-300'];
    $isTerminal = in_array($profile->status, ['approved', 'rejected']);
@endphp

<style>
    @media print {
        aside.sidebar, header.topbar, .no-print { display: none !important; }
        .card { break-inside: avoid; page-break-inside: avoid; box-shadow: none !important; border-color: #e5e7eb !important; }
        body { background: white !important; color: black !important; }
    }
</style>

<div class="space-y-6" x-data="{ action: null }">

    {{-- ── Header ── --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 no-print">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.internships.index') }}" class="hover:text-white">Internships</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $fellow?->name }}</span>
            </nav>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-white">Internship Dossier</h1>
                <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                @if(!$fellow?->onboarding_completed_at)
                    <span class="badge bg-dark-600/40 text-dark-300 border-dark-500/30">Draft · onboarding incomplete</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('admin.internships.index') }}" class="btn btn-outline">Back to list</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400 no-print">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ═══════ MAIN COLUMN ═══════ --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ── §1 Fellow snapshot ── --}}
            <section class="card p-6">
                <div class="flex items-start gap-5">
                    @if($fellow?->avatar_url)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($fellow->avatar_url) }}"
                             alt="{{ $fellow->name }}"
                             class="w-20 h-20 rounded-2xl object-cover">
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-2xl font-bold text-white">
                            {{ strtoupper(substr($fellow?->name ?? '?', 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-bold text-white">{{ $fellow?->name }}</h2>
                        @if($fellow?->headline)
                            <p class="text-primary-400 text-sm mt-0.5">{{ $fellow->headline }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm">
                            <a href="mailto:{{ $fellow?->email }}" class="text-dark-300 hover:text-white flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $fellow?->email }}
                            </a>
                            @if($fellow?->phone)
                                <span class="text-dark-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $fellow->phone }}
                                </span>
                            @endif
                            @if($fellow?->location)
                                <span class="text-dark-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $fellow->location }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right text-xs text-dark-500 space-y-1 hidden sm:block">
                        <div><span class="uppercase tracking-wide">Joined</span><br><span class="text-dark-300 text-sm">{{ $fellow?->created_at?->format('M j, Y') }}</span></div>
                        <div class="pt-2"><span class="uppercase tracking-wide">Fellow type</span><br><span class="text-dark-300 text-sm capitalize">{{ str_replace('_', ' ', $fellow?->fellow_type?->value ?? '—') }}</span></div>
                    </div>
                </div>

                @if($fellow?->bio)
                    <div class="mt-5 pt-4 border-t border-dark-700">
                        <p class="text-dark-500 text-xs uppercase tracking-wide mb-1">Bio</p>
                        <p class="text-dark-200 text-sm whitespace-pre-line">{{ $fellow->bio }}</p>
                    </div>
                @endif

                @if($fellow?->linkedin_url || $fellow?->github_url || $fellow?->portfolio_url || $fellow?->resume_url)
                    <div class="mt-4 pt-4 border-t border-dark-700 flex flex-wrap gap-2">
                        @if($fellow->linkedin_url)
                            <a href="{{ $fellow->linkedin_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-blue-600/20 text-blue-300 hover:bg-blue-600/30">
                                LinkedIn ↗
                            </a>
                        @endif
                        @if($fellow->github_url)
                            <a href="{{ $fellow->github_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-dark-700 text-dark-200 hover:bg-dark-600">
                                GitHub ↗
                            </a>
                        @endif
                        @if($fellow->portfolio_url)
                            <a href="{{ $fellow->portfolio_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-purple-600/20 text-purple-300 hover:bg-purple-600/30">
                                Portfolio ↗
                            </a>
                        @endif
                        @if($fellow->resume_url)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($fellow->resume_url) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600/30">
                                Resume ↗
                            </a>
                        @endif
                    </div>
                @endif

                @if(!empty($fellow?->skills) && !empty(array_filter(is_array($fellow->skills) ? $fellow->skills : [], fn ($v, $k) => !in_array($k, ['goals','primary_goal','goal_timeline','goal_success_vision']), ARRAY_FILTER_USE_BOTH)))
                    @php
                        $rawSkills = collect($fellow->skills)
                            ->reject(fn ($v, $k) => in_array($k, ['goals','primary_goal','goal_timeline','goal_success_vision']))
                            ->flatten()
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    @if($rawSkills->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-dark-700">
                            <p class="text-dark-500 text-xs uppercase tracking-wide mb-2">Declared skills</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($rawSkills as $skill)
                                    <span class="px-2 py-1 text-xs bg-dark-700 text-dark-300 rounded-lg">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </section>

            {{-- ── §2 Career goals ── --}}
            @if(!empty($goals) || $vision || $timeline)
                <section class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <span>🎯</span> Career goals
                    </h3>

                    @if($primaryGoal && isset($goalLabels[$primaryGoal]))
                        <div class="mb-4 p-3 rounded-lg bg-primary-500/10 border border-primary-500/30">
                            <p class="text-primary-300 text-xs uppercase tracking-wide">Top priority</p>
                            <p class="text-white font-semibold mt-1">
                                <span>{{ $goalLabels[$primaryGoal][0] }}</span>
                                {{ $goalLabels[$primaryGoal][1] }}
                            </p>
                        </div>
                    @endif

                    @if(!empty($goals))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                            @foreach($goals as $g)
                                @php $meta = $goalLabels[$g] ?? [null, ucfirst(str_replace('_', ' ', $g))]; @endphp
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-dark-800/60 border border-dark-700">
                                    <span class="text-lg">{{ $meta[0] }}</span>
                                    <span class="text-dark-200 text-sm">{{ $meta[1] }}</span>
                                    @if($g === $primaryGoal)
                                        <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded bg-primary-500/20 text-primary-300 uppercase tracking-wide">Primary</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        @if($timeline)
                            <div>
                                <dt class="text-dark-500 text-xs uppercase tracking-wide">Target timeline</dt>
                                <dd class="text-dark-200 mt-1">{{ $timelineLabels[$timeline] ?? ucfirst($timeline) }}</dd>
                            </div>
                        @endif
                        @if($fellow?->availability)
                            <div>
                                <dt class="text-dark-500 text-xs uppercase tracking-wide">Availability</dt>
                                <dd class="text-dark-200 mt-1 capitalize">{{ str_replace('_', ' ', $fellow->availability) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($vision)
                        <div class="mt-4 pt-4 border-t border-dark-700">
                            <dt class="text-dark-500 text-xs uppercase tracking-wide mb-1">Success vision (6 months)</dt>
                            <p class="text-dark-200 italic whitespace-pre-line">"{{ $vision }}"</p>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ── §3 Institution ── --}}
            <section class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>🏛️</span> Institution
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Type</dt>
                        <dd class="text-dark-200 mt-1">{{ ucfirst($profile->type) }} internship</dd>
                    </div>
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Name</dt>
                        <dd class="text-dark-200 mt-1">{{ $profile->institution_name }}</dd>
                    </div>
                    @if($profile->department)
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Department / Faculty</dt>
                            <dd class="text-dark-200 mt-1">{{ $profile->department }}</dd>
                        </div>
                    @endif
                    @if($profile->is_academic)
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Academic level</dt>
                            <dd class="text-dark-200 mt-1">{{ \App\Models\InternshipProfile::ACADEMIC_LEVELS[$profile->academic_level] ?? $profile->academic_level ?? '—' }}</dd>
                        </div>
                        @if($profile->student_id)
                            <div>
                                <dt class="text-dark-500 text-xs uppercase tracking-wide">Student / Matriculation ID</dt>
                                <dd class="text-dark-200 mt-1 font-mono">{{ $profile->student_id }}</dd>
                            </div>
                        @endif
                    @endif
                </dl>
            </section>

            {{-- ── §4 Supervisor ── --}}
            <section class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>👤</span> Supervisor
                </h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Name</dt>
                        <dd class="text-dark-200 mt-1">{{ $profile->supervisor_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Email</dt>
                        <dd class="text-dark-200 mt-1">
                            <a href="mailto:{{ $profile->supervisor_email }}" class="text-primary-400 hover:underline">{{ $profile->supervisor_email }}</a>
                            <span class="ml-2 text-[11px] text-amber-500">(not yet verified)</span>
                        </dd>
                    </div>
                    @if($profile->supervisor_phone)
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Phone</dt>
                            <dd class="text-dark-200 mt-1">{{ $profile->supervisor_phone }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- ── §5 Duration (proposed vs approved) ── --}}
            <section class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📅</span> Duration
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg border border-dark-700 bg-dark-800/40">
                        <p class="text-dark-500 text-xs uppercase tracking-wide mb-2">Proposed by fellow</p>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-dark-500">Type</dt><dd class="text-dark-200">{{ ucfirst($profile->duration_type ?? '—') }}</dd></div>
                            <div class="flex justify-between"><dt class="text-dark-500">Length</dt><dd class="text-dark-200">{{ $profile->duration_label }}</dd></div>
                            @if($profile->start_date)
                                <div class="flex justify-between"><dt class="text-dark-500">Start</dt><dd class="text-dark-200">{{ $profile->start_date->format('M j, Y') }}</dd></div>
                            @endif
                            @if($profile->end_date)
                                <div class="flex justify-between"><dt class="text-dark-500">End</dt><dd class="text-dark-200">{{ $profile->end_date->format('M j, Y') }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    <div class="p-4 rounded-lg border border-primary-500/40 bg-primary-500/5">
                        <p class="text-primary-300 text-xs uppercase tracking-wide mb-2">Admin-confirmed</p>
                        @if($profile->approved_start_date && $profile->approved_end_date)
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-dark-500">Duration</dt><dd class="text-white font-medium">{{ $profile->total_days }} days</dd></div>
                                <div class="flex justify-between"><dt class="text-dark-500">Start</dt><dd class="text-dark-200">{{ $profile->approved_start_date->format('M j, Y') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-dark-500">End</dt><dd class="text-dark-200">{{ $profile->approved_end_date->format('M j, Y') }}</dd></div>
                            </dl>
                        @else
                            <p class="text-dark-400 text-sm italic">Not yet confirmed. Set the official window when you approve.</p>
                        @endif
                    </div>
                </div>

                @if($profile->approved_start_date && $profile->approved_end_date)
                    @php
                        $progress = $profile->progress_percent ?? 0;
                        $barColor = $profile->is_expired ? 'from-dark-600 to-dark-500'
                                  : ($progress >= 80 ? 'from-amber-500 to-red-500'
                                  : ($progress >= 40 ? 'from-teal-500 to-primary-500'
                                  : 'from-primary-500 to-blue-500'));
                    @endphp
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <div class="h-3 bg-dark-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-2 text-dark-400">
                            <span>{{ $profile->days_elapsed ?? 0 }} of {{ $profile->total_days ?? 0 }} days elapsed</span>
                            <span>
                                @if($profile->is_expired)
                                    Ended {{ $profile->approved_end_date->diffForHumans() }}
                                @else
                                    {{ $profile->days_remaining ?? 0 }} days remaining
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                @if($profile->notes)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <p class="text-dark-500 text-xs uppercase tracking-wide mb-1">Fellow's notes</p>
                        <p class="text-dark-200 text-sm whitespace-pre-line">{{ $profile->notes }}</p>
                    </div>
                @endif
            </section>

            {{-- ── §6 Documentation ── --}}
            <section class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📎</span> Internship letter / convention
                </h3>
                @if($profile->internship_letter_path)
                    <a href="{{ route('admin.internships.letter', $profile) }}" target="_blank"
                       class="inline-flex items-center gap-2 btn btn-outline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V17a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        Download {{ basename($profile->internship_letter_path) }}
                    </a>
                @else
                    <p class="text-amber-400 text-sm">⚠️ No letter uploaded. Consider requesting revision.</p>
                @endif
            </section>
        </div>

        {{-- ═══════ SIDEBAR ═══════ --}}
        <aside class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Review status</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-dark-500">Current</span><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-500">Submitted</span><span class="text-dark-200">{{ $profile->created_at?->format('M j, Y H:i') }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-500">Last updated</span><span class="text-dark-200">{{ $profile->updated_at?->diffForHumans() }}</span></div>
                    @if($profile->reviewed_at)
                        <div class="flex justify-between"><span class="text-dark-500">Reviewed</span><span class="text-dark-200">{{ $profile->reviewed_at->format('M j, Y H:i') }}</span></div>
                        <div class="flex justify-between"><span class="text-dark-500">By</span><span class="text-dark-200">{{ $profile->reviewer?->name ?? '—' }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-dark-500">Onboarding</span>
                        <span class="{{ $fellow?->onboarding_completed_at ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $fellow?->onboarding_completed_at ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>
                    <div class="flex justify-between"><span class="text-dark-500">Profile</span>
                        <span class="{{ $fellow?->profile_completed_at ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $fellow?->profile_completed_at ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>
                </div>

                @if($profile->review_notes)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <p class="text-dark-500 text-xs uppercase tracking-wide mb-1">Last review notes</p>
                        <p class="text-dark-200 text-sm whitespace-pre-line">{{ $profile->review_notes }}</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-dark-700">
                    <a href="{{ route('admin.fellows.show', $fellow) }}" class="text-primary-400 hover:text-primary-300 text-sm">Open fellow profile →</a>
                </div>
            </div>

            @unless($isTerminal)
                <div class="card p-6 no-print">
                    <h3 class="text-lg font-semibold text-white mb-4">Actions</h3>

                    @if(!$fellow?->onboarding_completed_at)
                        <div class="mb-4 p-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
                            ⚠️ The fellow hasn't finished onboarding yet. Consider waiting for a complete submission before approving.
                        </div>
                    @endif

                    <div class="flex flex-col gap-2">
                        <button type="button" @click="action = action === 'approve' ? null : 'approve'" class="btn btn-primary w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve
                        </button>
                        <button type="button" @click="action = action === 'changes' ? null : 'changes'" class="btn btn-outline text-amber-400 border-amber-500/40 hover:bg-amber-500/10 w-full">Request changes</button>
                        <button type="button" @click="action = action === 'reject' ? null : 'reject'" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Reject</button>
                    </div>

                    <form x-data="{ assignFee: false }" x-show="action === 'approve'" x-cloak method="POST" action="{{ route('admin.internships.approve', $profile) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4">
                        @csrf
                        <p class="text-dark-400 text-xs">Confirm the official window. The fellow will only have platform access between these dates.</p>
                        @php
                            $proposedStart = $profile->approved_start_date?->toDateString()
                                ?? $profile->start_date?->toDateString()
                                ?? now()->toDateString();
                            $proposedEnd = $profile->approved_end_date?->toDateString()
                                ?? $profile->end_date?->toDateString()
                                ?? ($profile->predefined_duration_months
                                    ? now()->addMonths($profile->predefined_duration_months)->toDateString()
                                    : now()->addMonths(3)->toDateString());
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-dark-300 text-xs">Start date <span class="text-red-400">*</span></span>
                                <input type="date" name="approved_start_date" required value="{{ $proposedStart }}" class="form-input w-full mt-1 text-sm">
                            </label>
                            <label class="block">
                                <span class="text-dark-300 text-xs">End date <span class="text-red-400">*</span></span>
                                <input type="date" name="approved_end_date" required value="{{ $proposedEnd }}" class="form-input w-full mt-1 text-sm">
                            </label>
                        </div>
                        <label class="block">
                            <span class="text-dark-300 text-sm">Optional notes</span>
                            <textarea name="review_notes" rows="2" class="form-input w-full mt-1" placeholder="Anything the fellow should know"></textarea>
                        </label>

                        {{-- Fee Assignment section --}}
                        <div class="pt-2 border-t border-dark-700 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer mb-3">
                                <input type="checkbox" name="assign_fee" value="1" x-model="assignFee" class="form-checkbox text-primary-500 rounded border-dark-600 bg-dark-800 focus:ring-primary-500/50">
                                <span class="text-sm text-white font-medium">Assign a fee with this approval</span>
                            </label>

                            <div x-show="assignFee" x-transition.opacity class="space-y-3 bg-dark-800/50 p-3 rounded-lg border border-dark-700">
                                <label class="block">
                                    <span class="text-dark-300 text-xs">Fee Title <span class="text-red-400">*</span></span>
                                    <input type="text" name="fee_title" class="form-input w-full mt-1 text-sm" placeholder="e.g. Internship Processing Fee" :required="assignFee">
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="block">
                                        <span class="text-dark-300 text-xs">Amount (CFA) <span class="text-red-400">*</span></span>
                                        <input type="number" name="fee_amount" min="1" step="1" class="form-input w-full mt-1 text-sm" :required="assignFee">
                                    </label>
                                    <label class="block">
                                        <span class="text-dark-300 text-xs">Payment Plan <span class="text-red-400">*</span></span>
                                        <select name="fee_plan_type" class="form-input w-full mt-1 text-sm" :required="assignFee">
                                            <option value="full">Full Payment</option>
                                            <option value="installments">Installments (3 splits)</option>
                                        </select>
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="text-dark-300 text-xs">First Due Date <span class="text-red-400">*</span></span>
                                    <input type="date" name="fee_due_date" value="{{ date('Y-m-d') }}" class="form-input w-full mt-1 text-sm" :required="assignFee">
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full mt-4">Confirm approval</button>
                    </form>

                    <form x-show="action === 'changes'" x-cloak method="POST" action="{{ route('admin.internships.request-changes', $profile) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">What needs to be revised? <span class="text-red-400">*</span></span>
                            <textarea name="review_notes" rows="4" required minlength="10" class="form-input w-full mt-1" placeholder="Explain clearly what the fellow must update or re-upload."></textarea>
                        </label>
                        <button type="submit" class="btn btn-primary w-full">Send revision request</button>
                    </form>

                    <form x-show="action === 'reject'" x-cloak method="POST" action="{{ route('admin.internships.reject', $profile) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4"
                          onsubmit="return confirm('Reject this internship profile? The fellow will be notified.')">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">Reason for rejection <span class="text-red-400">*</span></span>
                            <textarea name="review_notes" rows="4" required minlength="10" class="form-input w-full mt-1" placeholder="Why is this being rejected?"></textarea>
                        </label>
                        <button type="submit" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Confirm rejection</button>
                    </form>
                </div>
            @else
                <div class="card p-6 text-dark-400 text-sm no-print">
                    This profile has already been {{ $profile->status }}. No further review actions available.
                </div>
            @endunless
        </aside>
    </div>
</div>
@endsection
