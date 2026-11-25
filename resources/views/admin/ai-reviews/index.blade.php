@extends('layouts.app')

@section('title', 'AI Review')

@section('content')
@php
    $pct = fn ($v) => $v === null ? '—' : $v . '%';
    $modes = \App\Models\AiReview::MODES;
@endphp
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">AI Review of Submissions</h1>
            <p class="text-dark-400 mt-1 max-w-3xl">
                The AI reviews curriculum submissions against their rubric, checks copying and where GitHub work came from, and recommends
                <span class="text-green-400">approve</span>, <span class="text-red-400">return for revision</span> or <span class="text-amber-400">a human decides</span>.
                In this phase a human takes every decision; this page shows how often the AI agrees.
            </p>
        </div>
        <a href="{{ route('admin.curriculum.reviews') }}" class="btn btn-outline">Review queue</a>
    </div>

    @if(!$configured)
        <div class="card p-4 border border-red-500/40 text-sm text-red-300">OpenAI is not configured, so reviews fail and go to humans. <a href="{{ route('admin.integrations.index') }}#openai" class="underline">Add a key on the Integrations page</a>.</div>
    @endif
    @if(!$githubToken)
        <div class="card p-4 border border-amber-500/30 text-sm text-amber-200">No GitHub token: GitHub allows only 60 requests an hour without one, which is about 10 repository reviews. <a href="{{ route('admin.integrations.index') }}#github" class="underline">Add a token on the Integrations page</a>.</div>
    @endif
    <p class="text-dark-500 text-xs">Is the key working? Use <a href="{{ route('admin.integrations.index') }}#openai" class="underline">Integrations → OpenAI → Test</a>, which also shows whether the account has credits.</p>

    {{-- Overall --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Agreement with humans</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $pct($overall['agreement']) }}</p>
            <p class="text-dark-500 text-xs mt-1">on {{ $overall['compared'] }} comparable decisions</p>
        </div>
        <div class="card p-4 {{ $overall['false_approvals'] ? 'border border-red-500/50' : '' }}">
            <p class="text-dark-400 text-sm">Wrong approvals</p>
            <p class="text-2xl font-bold {{ $overall['false_approvals'] ? 'text-red-400' : 'text-white' }} mt-1">{{ $overall['false_approvals'] }}</p>
            <p class="text-dark-500 text-xs mt-1">AI said approve, human rejected</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Too strict</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $overall['missed_approvals'] }}</p>
            <p class="text-dark-500 text-xs mt-1">AI said revise, human approved</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Score gap</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $overall['avg_score_gap'] === null ? '—' : $overall['avg_score_gap'] . ' pts' }}</p>
            <p class="text-dark-500 text-xs mt-1">average, on approvals</p>
        </div>
        <div class="card p-4">
            <p class="text-dark-400 text-sm">Reviewed</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $overall['completed'] }}</p>
            <p class="text-dark-500 text-xs mt-1">{{ $overall['failed'] }} failed · {{ $overall['pending'] }} pending</p>
        </div>
    </div>

    <p class="text-dark-500 text-sm">
        Recommendations so far: {{ $overall['approve'] }} approve · {{ $overall['revise'] }} revise · {{ $overall['escalate'] }} sent to a human.
        Reviews where the reviewer revealed the AI verdict before deciding are left out of the agreement figures.
    </p>

    {{-- By activity type --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-dark-700">
            <h2 class="text-lg font-semibold text-white">By activity type</h2>
            <p class="text-dark-400 text-sm">An activity type is ready for automatic decisions (phase 2) after at least {{ $readyMinCases }} comparable decisions, {{ $readyMinAgreement }}% agreement and no wrong approvals.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-dark-800">
                    <tr class="text-left text-dark-400 text-xs uppercase tracking-wider">
                        <th class="py-3 px-6">Type</th>
                        <th class="py-3 px-4">Reviewed</th>
                        <th class="py-3 px-4">Approve / Revise / Human</th>
                        <th class="py-3 px-4">Compared</th>
                        <th class="py-3 px-4">Agreement</th>
                        <th class="py-3 px-4">Wrong approvals</th>
                        <th class="py-3 px-4">Ready</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($byType as $row)
                        <tr>
                            <td class="py-3 px-6 text-white">{{ ucwords(str_replace('_', ' ', $row['type'])) }}</td>
                            <td class="py-3 px-4 text-dark-300">{{ $row['completed'] }}</td>
                            <td class="py-3 px-4 text-dark-300">{{ $row['approve'] }} / {{ $row['revise'] }} / {{ $row['escalate'] }}</td>
                            <td class="py-3 px-4 text-dark-300">{{ $row['compared'] }}</td>
                            <td class="py-3 px-4 text-dark-300">{{ $pct($row['agreement']) }}</td>
                            <td class="py-3 px-4 {{ $row['false_approvals'] ? 'text-red-400 font-semibold' : 'text-dark-300' }}">{{ $row['false_approvals'] }}</td>
                            <td class="py-3 px-4">{!! $row['ready'] ? '<span class="text-green-400">Yes</span>' : '<span class="text-dark-500">Not yet</span>' !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-dark-400">No AI reviews yet. They start with the next submission.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Recent --}}
        <div class="card overflow-hidden xl:col-span-2">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-lg font-semibold text-white">Recent reviews</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-dark-800">
                        <tr class="text-left text-dark-400 text-xs uppercase tracking-wider">
                            <th class="py-3 px-6">Submission</th>
                            <th class="py-3 px-4">AI</th>
                            <th class="py-3 px-4">Human</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        @forelse($recent as $review)
                            <tr>
                                <td class="py-3 px-6">
                                    <a href="{{ route('admin.curriculum.reviews.show', $review->progress_id) }}" class="text-white hover:text-primary-400">{{ $review->curriculumActivity?->title ?? 'Activity' }}</a>
                                    <span class="block text-dark-500 text-xs">{{ $review->fellow?->name }} · attempt {{ $review->attempt_number }} · {{ $review->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($review->recommended_decision)
                                        <span class="px-2 py-0.5 rounded-full text-xs border {{ $review->decision_badge_class }}">{{ $review->decision_label }}</span>
                                        @if($review->weighted_score !== null)<span class="text-dark-400 text-xs ml-1">{{ number_format($review->weighted_score, 0) }}</span>@endif
                                    @else
                                        <span class="text-dark-500 text-xs">{{ ucfirst($review->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if($review->human_decision)
                                        <span class="{{ $review->agrees === false ? 'text-red-400' : 'text-dark-300' }}">{{ ucfirst($review->human_decision) }}{{ $review->human_score !== null ? ' · ' . $review->human_score : '' }}</span>
                                        @if($review->revealed_before_decision)<span class="block text-dark-500">saw AI first</span>@endif
                                    @else
                                        <span class="text-dark-500">Waiting</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-dark-400">Nothing yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Settings --}}
        <form method="POST" action="{{ route('admin.ai-reviews.settings') }}" class="card p-6 space-y-4 h-fit">
            @csrf
            <h2 class="text-lg font-semibold text-white">Settings</h2>
            @if($errors->any())
                <div class="text-sm text-red-300">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
            @endif
            <div>
                <label class="block text-dark-300 text-sm mb-1">Mode</label>
                <select name="ai_review_mode" class="form-input w-full">
                    @foreach($modes as $value => $label)
                        <option value="{{ $value }}" @selected(old('ai_review_mode', $settings['mode']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-dark-500 text-xs mt-1">Activities can override this on their edit page.</p>
            </div>
            @foreach([
                'ai_review_pass_score' => ['Recommend approval from score', 'pass_score', 'Weighted rubric score out of 100.'],
                'ai_review_criterion_floor' => ['…and every criterion at least', 'criterion_floor', 'No single criterion may be this weak.'],
                'ai_review_revise_below' => ['Recommend revision below score', 'revise_below', 'Between this and the approval score, a human decides.'],
                'ai_review_similarity_threshold' => ['Flag copying from text overlap (%)', 'similarity_threshold', 'Share of the text found in another fellow\'s submission.'],
                'ai_review_human_from_points' => ['Always a human from (points)', 'human_from_points', 'High-value activities; 0 turns this off.'],
            ] as $name => [$label, $key, $hint])
                <div>
                    <label class="block text-dark-300 text-sm mb-1">{{ $label }}</label>
                    <input type="number" name="{{ $name }}" value="{{ old($name, $settings[$key]) }}" class="form-input w-full" required>
                    <p class="text-dark-500 text-xs mt-1">{{ $hint }}</p>
                </div>
            @endforeach
            <button type="submit" class="btn btn-primary w-full">Save settings</button>
            <p class="text-dark-500 text-xs">Model: {{ $model }} · {{ number_format($overall['tokens']) }} tokens used so far.</p>
        </form>
    </div>
</div>
@endsection
