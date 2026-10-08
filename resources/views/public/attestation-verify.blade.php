@php
    $internship = $record['internship'];
    $valid = $attestation->isIssued();
    $state = match (true) {
        $valid => ['Verified Attestation', 'This is an authentic internship attestation issued by I-NNOVA CMR.', 'green'],
        $attestation->isRevoked() => ['Revoked Attestation', 'This attestation was issued by I-NNOVA CMR and later revoked. It is no longer valid.', 'red'],
        default => ['Superseded Attestation', 'This attestation was replaced by a newer version and is no longer the current one.', 'amber'],
    };
    $palette = [
        'green' => 'bg-green-500/20 border-green-500/30 text-green-400',
        'red' => 'bg-red-500/20 border-red-500/30 text-red-400',
        'amber' => 'bg-amber-500/20 border-amber-500/30 text-amber-400',
    ][$state[2]];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Verify Attestation {{ $attestation->reference }} | I-NNOVA CMR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-900 text-white min-h-screen flex flex-col font-sans antialiased">

    <header class="border-b border-dark-700 bg-dark-800/80 backdrop-blur sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-600 rounded flex items-center justify-center font-bold text-white tracking-widest text-xs">IKS</div>
                <span class="font-bold tracking-wider hidden sm:block">I-NNOVA KICKSTARTER</span>
            </div>
            <span class="text-dark-400 text-sm font-medium tracking-widest uppercase">Attestation Verification</span>
        </div>
    </header>

    <main class="flex-1 py-12 px-4 flex justify-center">
        <div class="w-full max-w-2xl">

            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full border flex items-center justify-center mx-auto mb-4 {{ $palette }}">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($valid)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ $state[0] }}</h1>
                <p class="text-dark-400 text-lg">{{ $state[1] }}</p>

                @if($attestation->isRevoked())
                    <p class="text-dark-500 text-sm mt-2">Revoked on {{ $attestation->revoked_at?->format('F j, Y') }}.</p>
                @elseif($attestation->isSuperseded() && $attestation->supersededBy?->isIssued())
                    <a href="{{ route('attestation.verify', $attestation->supersededBy->uuid) }}" class="inline-block mt-3 text-primary-400 hover:text-primary-300 font-medium">
                        View the current version ({{ $attestation->supersededBy->reference }}) &rarr;
                    </a>
                @endif
            </div>

            <div class="card p-0 overflow-hidden">
                <div class="p-6 md:p-8 border-b border-dark-700 bg-dark-800/50">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Reference</p>
                            <p class="text-2xl font-mono text-white tracking-widest">{{ $attestation->reference }}</p>
                        </div>
                        <div class="md:text-right">
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Date Issued</p>
                            <p class="text-white font-medium">{{ $attestation->issued_at?->format('F j, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-6">
                    <div>
                        <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Issued to</p>
                        <p class="text-2xl font-bold text-white">{{ $record['holder']['name'] }}</p>
                        <p class="text-dark-400">{{ $record['holder']['fellow_type_label'] }} &middot; {{ $internship['institution'] }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-dark-700">
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Internship in</p>
                            <p class="text-white font-medium">{{ collect($record['tracks'])->pluck('name')->join(', ') ?: 'INNOVA Kickstarter programme' }}</p>
                        </div>
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Period</p>
                            <p class="text-white font-medium">
                                {{ \Carbon\Carbon::parse($internship['start_date'])->format('M j, Y') }} – {{ \Carbon\Carbon::parse($internship['end_date'])->format('M j, Y') }}
                                <span class="text-dark-400">({{ $internship['weeks'] }} weeks)</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Mention</p>
                            <p class="text-white font-medium">{{ $award['mention_en'] ?? '—' }} / {{ $award['mention_fr'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Signed by</p>
                            <p class="text-white font-medium">{{ $award['signatory_name'] ?? '—' }}</p>
                            <p class="text-dark-400 text-sm">{{ $award['signatory_title'] ?? '' }}</p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-dark-700">
                        <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-4">Internship record</p>
                        @include('attestations._record', ['record' => $record])
                    </div>
                </div>

                <div class="p-6 bg-dark-800 border-t border-dark-700 text-center text-dark-400 text-sm">
                    <p>Compare this page with the document in hand: the name, reference, dates and mention must match.</p>
                    <p class="mt-1">Document fingerprint: <span class="font-mono text-dark-300">{{ $attestation->fingerprint }}</span></p>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300 font-medium">&larr; Return to I-NNOVA CMR</a>
            </div>
        </div>
    </main>

</body>
</html>
