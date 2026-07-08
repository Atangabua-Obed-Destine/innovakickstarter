<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background-color: white !important; color: black !important; }
            .no-print { display: none !important; }
            .print-border { border: 1px solid #e5e7eb !important; }
            .print-text-dark { color: #111827 !important; }
            .print-text-gray { color: #4b5563 !important; }
            .print-bg-light { background-color: #f9fafb !important; }
            .print-shadow-none { box-shadow: none !important; }
            @page { margin: 1cm; }
        }
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        .receipt-container { max-w-3xl mx-auto my-8 bg-white p-10 rounded-xl shadow-lg print-shadow-none }
    </style>
</head>
<body class="bg-gray-100 py-8 text-gray-800">

    <div class="max-w-3xl mx-auto bg-white p-10 shadow-lg print-shadow-none print-border relative overflow-hidden">
        
        {{-- Print Buttons (Hidden when printing) --}}
        <div class="absolute top-6 right-6 flex gap-2 no-print z-50">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.close()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium">
                Close
            </button>
        </div>

        {{-- Background Stamp --}}
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-5 pointer-events-none transform -rotate-12">
            <h1 class="text-9xl font-black uppercase text-center tracking-widest leading-none">
                I-NNOVA<br>CMR
            </h1>
        </div>

        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-gray-200 pb-8 mb-8 relative z-10 pt-10">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">I-NNOVA CMR</h1>
                <p class="text-sm text-gray-500 mt-1 font-medium tracking-widest uppercase">Career Capital Platform</p>
                
                <div class="mt-4 text-sm text-gray-600 space-y-1">
                    <p>Cameroon</p>
                    <p>contact@innovacmr.com</p>
                    <p>www.innovacmr.com</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black text-gray-200 tracking-widest uppercase mb-2 mt-4">RECEIPT</h2>
                <p class="text-sm text-gray-500 font-medium">Date: <span class="text-gray-900 font-semibold">{{ $payment->payment_date->format('M j, Y') }}</span></p>
                <p class="text-sm text-gray-500 font-medium mt-1">Receipt #: <span class="text-gray-900 font-semibold font-mono">{{ $payment->receipt_number }}</span></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-10 relative z-10">
            {{-- Billed To --}}
            <div>
                <p class="text-xs font-bold tracking-widest text-gray-400 uppercase mb-3">Received From</p>
                <h3 class="text-lg font-bold text-gray-900">{{ $payment->fee->fellow->name }}</h3>
                <div class="text-sm text-gray-600 mt-2 space-y-1">
                    <p>Email: {{ $payment->fee->fellow->email }}</p>
                    <p>ID: {{ $payment->fee->fellow->username }}</p>
                    <p>Program: {{ $payment->fee->billable_label }}</p>
                    @if($payment->fee->fellow->activeTrack())
                        <p>Track: {{ $payment->fee->fellow->activeTrack()->track->name }}</p>
                    @endif
                </div>
            </div>

            {{-- Payment Details --}}
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                <p class="text-xs font-bold tracking-widest text-gray-400 uppercase mb-3">Payment Info</p>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method:</span>
                        <span class="font-medium text-gray-900">{{ $payment->method_label }}</span>
                    </div>
                    @if($payment->reference)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Reference:</span>
                        <span class="font-medium text-gray-900 font-mono">{{ $payment->reference }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-3 border-t border-gray-200">
                        <span class="text-gray-500">Amount Received:</span>
                        <span class="font-bold text-lg text-gray-900">{{ number_format($payment->amount, 2) }} CFA</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Itemized --}}
        <div class="mb-10 relative z-10">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="py-3 text-sm font-bold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="py-3 text-sm font-bold text-gray-500 uppercase tracking-wider text-right">Fee Total</th>
                        <th class="py-3 text-sm font-bold text-gray-500 uppercase tracking-wider text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-4">
                            <p class="font-bold text-gray-900">{{ $payment->fee->title }}</p>
                            @if($payment->installment)
                                <p class="text-gray-500 mt-1">Payment for: {{ $payment->installment->label }}</p>
                            @elseif($payment->notes)
                                <p class="text-gray-500 mt-1">{{ $payment->notes }}</p>
                            @endif
                        </td>
                        <td class="py-4 text-right font-medium text-gray-600">{{ number_format($payment->fee->amount_total, 2) }}</td>
                        <td class="py-4 text-right font-bold text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Footer summary --}}
        <div class="flex justify-between items-end border-t border-gray-200 pt-8 relative z-10">
            <div class="text-sm text-gray-500 max-w-sm">
                <p class="font-semibold text-gray-700 mb-1">Official Receipt</p>
                <p>This document verifies payment to I-NNOVA CMR. Keep this receipt for your records.</p>
                <p class="mt-2">Verified by: {{ $payment->verifier?->name ?? 'System' }}</p>
            </div>
            
            <div class="text-center">
                <div class="w-24 h-24 bg-white border border-gray-200 mx-auto flex items-center justify-center mb-2 p-1">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode(route('receipt.verify', $payment->receipt_number)) }}" alt="QR Code" width="90" height="90" />
                </div>
                <p class="text-xs text-gray-400">Scan to Verify</p>
            </div>
        </div>

    </div>

</body>
</html>
