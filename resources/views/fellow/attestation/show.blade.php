@extends('layouts.app')

@section('title', 'My Internship Attestation')

@section('content')
@php
    $ended = $eligibility['checks'][0]['passed'] ?? false;
    $feesClear = $eligibility['checks'][1]['passed'] ?? false;
    $steps = [
        ['Internship period ended', $ended, $eligibility['checks'][0]['detail'] ?? ''],
        ['Fees settled', $ended && $feesClear, $eligibility['checks'][1]['detail'] ?? ''],
        ['Reviewed by I-NNOVA CMR', (bool) $issued, $issued ? 'Reviewed and signed' : 'An admin reviews your record and awards a mention'],
        ['Attestation issued', (bool) $issued, $issued ? 'Issued ' . $issued->issued_at->format('M j, Y') : 'You will be notified here'],
    ];
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">My internship attestation</h1>
        <p class="text-dark-400">A bilingual, verifiable attestation of the work you completed during your internship.</p>
    </div>

    @if(!$profile)
        <div class="card p-6 text-dark-300">You have no internship on record, so there is no attestation to prepare.</div>
    @else
        @if($issued)
            <section class="card p-6 border-green-500/40">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-green-400 text-sm font-medium">Issued</p>
                        <p class="text-xl font-bold text-white font-mono mt-1">{{ $issued->reference }}</p>
                        <p class="text-dark-300 text-sm mt-1">Mention: {{ $issued->mention_en }} / {{ $issued->mention_fr }}</p>
                    </div>
                    <a href="{{ route('fellow.attestation.download') }}" class="btn btn-primary">Download PDF</a>
                </div>

                <div class="mt-5 pt-5 border-t border-dark-700" x-data="{ copied: false }">
                    <p class="text-dark-400 text-sm">Verification link. Share it with your school or an employer; it shows the same record as the QR code on the document.</p>
                    <div class="flex gap-2 mt-2">
                        <input type="text" readonly value="{{ $issued->verify_url }}" class="form-input flex-1 text-sm font-mono">
                        <button type="button" class="btn btn-outline"
                                @click="navigator.clipboard.writeText('{{ $issued->verify_url }}'); copied = true; setTimeout(() => copied = false, 2000)">
                            <span x-text="copied ? 'Copied' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
            </section>
        @elseif($current && $current->isRevoked())
            <section class="card p-6 border-red-500/40">
                <p class="text-red-400 font-medium">Attestation {{ $current->reference }} was revoked</p>
                <p class="text-dark-300 text-sm mt-2">{{ $current->revocation_reason }}</p>
                <p class="text-dark-400 text-sm mt-2">Contact the I-NNOVA CMR team if you believe this needs to be corrected.</p>
            </section>
        @endif

        <section class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Progress</h3>
            <ol class="space-y-4">
                @foreach($steps as [$label, $done, $detail])
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0 {{ $done ? 'bg-emerald-500/20 text-emerald-400' : 'bg-dark-700 text-dark-400' }}">
                            {{ $done ? '✓' : $loop->iteration }}
                        </span>
                        <span>
                            <span class="{{ $done ? 'text-white' : 'text-dark-300' }} font-medium">{{ $label }}</span><br>
                            <span class="text-dark-500 text-sm">{{ $detail }}</span>
                        </span>
                    </li>
                @endforeach
            </ol>

            @if($ended && !$feesClear)
                <div class="mt-5 p-4 rounded-lg bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-amber-300 text-sm">Your attestation is on hold until your outstanding fees are settled.</p>
                    <a href="{{ route('fees.index') }}" class="btn btn-primary btn-sm">Go to my fees</a>
                </div>
            @elseif(!$ended)
                <p class="mt-5 text-dark-400 text-sm">
                    Your attestation is prepared automatically once your internship period ends
                    @if($profile->approved_end_date) on {{ $profile->approved_end_date->format('M j, Y') }}@endif.
                </p>
            @endif
        </section>
    @endif
</div>
@endsection
