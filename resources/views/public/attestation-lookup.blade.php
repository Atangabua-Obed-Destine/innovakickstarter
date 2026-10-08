<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify an Attestation | I-NNOVA CMR</title>
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
        <div class="w-full max-w-md">
            <h1 class="text-2xl font-bold text-white mb-2">Verify an internship attestation</h1>
            <p class="text-dark-400 mb-6">
                Enter the reference and the verification code printed beside the QR code on the document.
                <span class="block mt-1 text-dark-500">Saisissez la référence et le code de vérification imprimés sur l'attestation.</span>
            </p>

            <form method="POST" action="{{ route('attestation.find') }}" class="card p-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-dark-300 text-sm">Reference / Référence</span>
                    <input type="text" name="reference" value="{{ old('reference') }}" required placeholder="IKS-ATT-2026-0001" class="form-input w-full mt-1 font-mono uppercase">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Verification code / Code de vérification</span>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="XXXX-XXXX" class="form-input w-full mt-1 font-mono uppercase">
                </label>
                @error('reference')
                    <p class="text-red-400 text-sm">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn btn-primary w-full">Verify / Vérifier</button>
            </form>
        </div>
    </main>

</body>
</html>
