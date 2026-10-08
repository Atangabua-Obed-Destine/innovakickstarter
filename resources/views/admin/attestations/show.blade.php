@extends('layouts.app')

@section('title', 'Attestation · ' . ($attestation->fellow?->name ?? ''))

@section('content')
@php
    $profile = $attestation->internshipProfile;
    $fellow = $attestation->fellow;
    $selectedMention = old('mention', $attestation->mention ?? $attestation->suggested_mention);
@endphp

<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.attestations.index') }}" class="hover:text-white">Attestations</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $fellow?->name }}</span>
            </nav>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-white">
                    {{ $attestation->reference ?? 'Attestation draft' }}
                </h1>
                <span class="badge {{ $attestation->status_badge_class }}">{{ $attestation->status_label }}</span>
                @if($attestation->version > 1)
                    <span class="badge bg-dark-600/40 text-dark-300 border-dark-500/30">Version {{ $attestation->version }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('admin.internships.show', $profile) }}" class="btn btn-outline">Internship dossier</a>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-400 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($attestation->isSuperseded() && $attestation->supersededBy)
        <div class="bg-dark-700/40 border border-dark-600 rounded-lg p-4 text-dark-300 text-sm">
            This version was replaced by
            <a href="{{ route('admin.attestations.show', $attestation->supersededBy) }}" class="text-primary-400 hover:underline">{{ $attestation->supersededBy->reference }}</a>.
        </div>
    @endif

    @if($attestation->isDraft() && $attestation->supersedes)
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-blue-300 text-sm">
            This draft will replace {{ $attestation->supersedes->reference }} when you issue it. Until then the earlier version keeps its current status.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ═══════ MAIN COLUMN ═══════ --}}
        <div class="lg:col-span-2 space-y-6">
            <section class="card p-6">
                <h2 class="text-xl font-bold text-white">{{ $record['holder']['name'] ?? $fellow?->name }}</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mt-4">
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Institution</dt>
                        <dd class="text-dark-200 mt-1">{{ $record['internship']['institution'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Track</dt>
                        <dd class="text-dark-200 mt-1">{{ collect($record['tracks'] ?? [])->pluck('name')->join(', ') ?: 'No approved track' }}</dd>
                    </div>
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Attested period</dt>
                        <dd class="text-dark-200 mt-1">
                            {{ $profile->approved_start_date?->format('M j, Y') }} – {{ $profile->approved_end_date?->format('M j, Y') }}
                            ({{ $record['internship']['weeks'] ?? '—' }} weeks)
                        </dd>
                    </div>
                    <div>
                        <dt class="text-dark-500 text-xs uppercase tracking-wide">Record compiled</dt>
                        <dd class="text-dark-200 mt-1">{{ isset($record['compiled_at']) ? \Carbon\Carbon::parse($record['compiled_at'])->format('M j, Y H:i') : '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="card p-6">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="text-lg font-semibold text-white">Internship record</h3>
                    @if($attestation->isDraft())
                        <form method="POST" action="{{ route('admin.attestations.refresh', $attestation) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm">Refresh data</button>
                        </form>
                    @endif
                </div>
                @if($attestation->isDraft())
                    <p class="text-dark-400 text-xs mb-4">
                        This is what will be printed in the annex. It is recompiled one last time at the moment you issue, then frozen.
                    </p>
                @endif
                @include('attestations._record', ['record' => $record])
            </section>
        </div>

        {{-- ═══════ SIDEBAR ═══════ --}}
        <aside class="space-y-6">

            @if($attestation->isDraft())
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Requirements</h3>
                    <ul class="space-y-3 text-sm">
                        @foreach($eligibility['checks'] as $check)
                            <li class="flex items-start gap-2">
                                <span class="{{ $check['passed'] ? 'text-emerald-400' : 'text-amber-400' }}">{{ $check['passed'] ? '✓' : '•' }}</span>
                                <span>
                                    <span class="text-dark-200">{{ $check['label'] }}</span><br>
                                    <span class="text-dark-500 text-xs">{{ $check['detail'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Award &amp; issue</h3>
                    <form method="POST" action="{{ route('admin.attestations.issue', $attestation) }}" class="space-y-4"
                          onsubmit="return confirm('Issue this attestation? The record is frozen and the fellow is notified.')">
                        @csrf
                        <label class="block">
                            <span class="text-dark-300 text-sm">Mention <span class="text-red-400">*</span></span>
                            <select name="mention" required class="form-input w-full mt-1">
                                <option value="">Choose a mention</option>
                                @foreach($mentions as $key => [$en, $fr])
                                    <option value="{{ $key }}" {{ $selectedMention === $key ? 'selected' : '' }}>
                                        {{ $en }} / {{ $fr }}{{ $attestation->suggested_mention === $key ? ' (suggested)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-dark-500 text-xs mt-1 block">
                                @if($attestation->suggested_mention)
                                    Suggested from curriculum results, required-activity completion, attendance and interviews. The decision is yours.
                                @else
                                    No suggestion: there is no scored work in this internship window.
                                @endif
                            </span>
                        </label>
                        <label class="block">
                            <span class="text-dark-300 text-sm">Appreciation (English)</span>
                            <textarea name="remarks_en" rows="3" maxlength="1200" class="form-input w-full mt-1" placeholder="Optional. Printed in the annex.">{{ old('remarks_en', $attestation->remarks_en) }}</textarea>
                        </label>
                        <label class="block">
                            <span class="text-dark-300 text-sm">Appréciation (French)</span>
                            <textarea name="remarks_fr" rows="3" maxlength="1200" class="form-input w-full mt-1" placeholder="Optional. Printed in the annex.">{{ old('remarks_fr', $attestation->remarks_fr) }}</textarea>
                        </label>

                        <div class="flex flex-col gap-2 pt-2">
                            <button type="button" class="btn btn-outline w-full"
                                    onclick="const p = new URLSearchParams(new FormData(this.form)); p.delete('_token'); window.open('{{ route('admin.attestations.preview', $attestation) }}?' + p.toString(), '_blank');">
                                Preview PDF
                            </button>
                            <button type="submit" class="btn btn-primary w-full" {{ $eligibility['eligible'] ? '' : 'disabled' }}>
                                Issue attestation
                            </button>
                            @unless($eligibility['eligible'])
                                <p class="text-amber-400 text-xs">Issuing is locked until every requirement above is met.</p>
                            @endunless
                        </div>
                    </form>
                </div>
            @else
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Issued document</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-dark-500">Mention</dt><dd class="text-dark-200">{{ $attestation->mention_en }} / {{ $attestation->mention_fr }}</dd></div>
                        <div class="flex justify-between"><dt class="text-dark-500">Issued</dt><dd class="text-dark-200">{{ $attestation->issued_at?->format('M j, Y H:i') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-dark-500">By</dt><dd class="text-dark-200">{{ $attestation->issuer?->name ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-dark-500">Signatory</dt><dd class="text-dark-200">{{ $attestation->signatory_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-dark-500">Verification code</dt><dd class="text-dark-200 font-mono">{{ $attestation->verification_code }}</dd></div>
                        <div class="flex justify-between"><dt class="text-dark-500">Fingerprint</dt><dd class="text-dark-200 font-mono text-xs">{{ $attestation->fingerprint }}</dd></div>
                    </dl>

                    @if($attestation->isRevoked())
                        <div class="mt-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                            Revoked {{ $attestation->revoked_at?->format('M j, Y') }} by {{ $attestation->revoker?->name ?? '—' }}.<br>
                            {{ $attestation->revocation_reason }}
                        </div>
                    @endif

                    <div class="flex flex-col gap-2 mt-4 pt-4 border-t border-dark-700">
                        <a href="{{ route('admin.attestations.download', $attestation) }}" class="btn btn-primary w-full">Download PDF</a>
                        <a href="{{ $attestation->verify_url }}" target="_blank" rel="noopener" class="btn btn-outline w-full">Open verification page</a>
                    </div>
                </div>

                @if($attestation->isIssued() || $attestation->isRevoked())
                    <div class="card p-6" x-data="{ action: null }">
                        <h3 class="text-lg font-semibold text-white mb-4">Corrections</h3>
                        <div class="flex flex-col gap-2">
                            <form method="POST" action="{{ route('admin.attestations.reissue', $attestation) }}"
                                  onsubmit="return confirm('Open a new draft version? The current one stays as it is until the new version is issued.')">
                                @csrf
                                <button type="submit" class="btn btn-outline w-full">Reissue with updated data</button>
                            </form>
                            @if($attestation->isIssued())
                                <button type="button" @click="action = action === 'revoke' ? null : 'revoke'" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Revoke</button>
                            @endif
                        </div>

                        <form x-show="action === 'revoke'" x-cloak method="POST" action="{{ route('admin.attestations.revoke', $attestation) }}" class="mt-4 space-y-3 border-t border-dark-700 pt-4"
                              onsubmit="return confirm('Revoke this attestation? Anyone scanning its QR code will see that it is no longer valid.')">
                            @csrf
                            <label class="block">
                                <span class="text-dark-300 text-sm">Reason for revocation <span class="text-red-400">*</span></span>
                                <textarea name="revocation_reason" rows="3" required minlength="10" class="form-input w-full mt-1" placeholder="Shown to the fellow. Not shown publicly."></textarea>
                            </label>
                            <button type="submit" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10 w-full">Confirm revocation</button>
                        </form>
                    </div>
                @endif
            @endif
        </aside>
    </div>
</div>
@endsection
