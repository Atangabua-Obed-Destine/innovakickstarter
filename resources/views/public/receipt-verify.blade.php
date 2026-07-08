<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Receipt {{ $payment->receipt_number }} | I-NNOVA CMR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-900 text-white min-h-screen flex flex-col font-sans antialiased">
    
    {{-- Top Bar --}}
    <header class="border-b border-dark-700 bg-dark-800/80 backdrop-blur sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-600 rounded flex items-center justify-center font-bold text-white tracking-widest text-xs">
                    IKS
                </div>
                <span class="font-bold tracking-wider hidden sm:block">I-NNOVA KICKSTARTER</span>
            </div>
            <span class="text-dark-400 text-sm font-medium tracking-widest uppercase">Receipt Verification</span>
        </div>
    </header>

    <main class="flex-1 py-12 px-4 flex justify-center">
        <div class="w-full max-w-2xl">
            
            <div class="text-center mb-8">
                <div class="w-20 h-20 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Verified Receipt</h1>
                <p class="text-dark-400 text-lg">This document is an authentic I-NNOVA CMR payment record.</p>
            </div>

            <div class="card p-0 overflow-hidden relative">
                {{-- Decorative background --}}
                <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>

                {{-- Header inside card --}}
                <div class="p-6 md:p-8 border-b border-dark-700 bg-dark-800/50">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Receipt Number</p>
                            <p class="text-2xl font-mono text-white tracking-widest">{{ $payment->receipt_number }}</p>
                        </div>
                        <div class="md:text-right">
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Date Issued</p>
                            <p class="text-white font-medium">{{ $payment->verified_at->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 md:p-8 space-y-8">
                    
                    <div>
                        <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-3">Received From</p>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-dark-700 flex items-center justify-center text-xl font-bold overflow-hidden border border-dark-600">
                                @if($payment->fee->fellow->avatar)
                                    <img src="{{ $payment->fee->fellow->avatar }}" alt="{{ $payment->fee->fellow->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($payment->fee->fellow->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <p class="text-xl font-bold text-white">{{ $payment->fee->fellow->name }}</p>
                                <p class="text-dark-400">{{ $payment->fee->fellow->username }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-dark-700">
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Payment Method</p>
                            <p class="text-white font-medium">{{ $payment->method_label }}</p>
                        </div>
                        <div>
                            <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-1">Transaction Ref</p>
                            <p class="text-white font-mono">{{ $payment->reference ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="bg-dark-900 rounded-xl p-5 border border-dark-700">
                        <p class="text-dark-400 text-sm font-medium tracking-wider uppercase mb-3">Payment For</p>
                        <p class="text-lg font-medium text-white">{{ $payment->fee->title }}</p>
                        @if($payment->installment)
                            <p class="text-dark-400 mt-1">Applying to: {{ $payment->installment->label }}</p>
                        @endif
                        <div class="mt-4 pt-4 border-t border-dark-700 flex justify-between items-center">
                            <span class="text-dark-300 font-medium">Amount Received</span>
                            <span class="text-2xl font-bold text-green-400">{{ $payment->formatted_amount }}</span>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="p-6 bg-dark-800 border-t border-dark-700 text-center text-dark-400 text-sm">
                    <p>Verified digitally by the I-NNOVA CMR System.</p>
                </div>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300 font-medium">
                    &larr; Return to I-NNOVA CMR
                </a>
            </div>

        </div>
    </main>

</body>
</html>
