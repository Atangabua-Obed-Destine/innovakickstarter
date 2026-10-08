@extends('layouts.app')

@section('title', 'Attestation Settings')

@section('content')
@php
    $text = fn (string $key) => old($key, $values[$key] ?? '');
@endphp

<div class="space-y-6 max-w-4xl">
    <div>
        <nav class="text-sm text-dark-400 mb-2">
            <a href="{{ route('admin.attestations.index') }}" class="hover:text-white">Attestations</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Settings</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">Attestation settings</h1>
        <p class="text-dark-400">Applied to attestations issued from now on. Documents already issued are never changed.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-400 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.attestations.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <section class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Signatory</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block md:col-span-2">
                    <span class="text-dark-300 text-sm">Name</span>
                    <input type="text" name="attestation_signatory_name" value="{{ $text('attestation_signatory_name') }}" required class="form-input w-full mt-1">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Title (English)</span>
                    <input type="text" name="attestation_signatory_title" value="{{ $text('attestation_signatory_title') }}" required class="form-input w-full mt-1">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Title (French)</span>
                    <input type="text" name="attestation_signatory_title_fr" value="{{ $text('attestation_signatory_title_fr') }}" required class="form-input w-full mt-1">
                </label>
            </div>
        </section>

        <section class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-1">Images</h3>
            <p class="text-dark-400 text-sm mb-4">PNG or JPG, up to 1 MB each. Use a transparent PNG for the signature and stamp.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['logo' => 'Logo', 'signature' => 'Signature', 'stamp' => 'Stamp'] as $field => $label)
                    <label class="block">
                        <span class="text-dark-300 text-sm">{{ $label }}</span>
                        <input type="file" name="{{ $field }}" accept="image/png,image/jpeg" class="form-input w-full mt-1 text-sm">
                        <span class="text-xs mt-1 block {{ !empty($values['attestation_' . $field . '_path']) ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ !empty($values['attestation_' . $field . '_path']) ? 'Uploaded. Choose a file to replace it.' : 'Not uploaded yet.' }}
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Organisation details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-dark-300 text-sm">Place of issue</span>
                    <input type="text" name="attestation_issue_place" value="{{ $text('attestation_issue_place') }}" required class="form-input w-full mt-1">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Address</span>
                    <input type="text" name="attestation_org_address" value="{{ $text('attestation_org_address') }}" required class="form-input w-full mt-1">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Email</span>
                    <input type="email" name="attestation_org_email" value="{{ $text('attestation_org_email') }}" class="form-input w-full mt-1">
                </label>
                <label class="block">
                    <span class="text-dark-300 text-sm">Website</span>
                    <input type="text" name="attestation_org_website" value="{{ $text('attestation_org_website') }}" class="form-input w-full mt-1">
                </label>
            </div>
        </section>

        <section class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-1">Suggested mention thresholds</h3>
            <p class="text-dark-400 text-sm mb-4">
                The suggestion averages the curriculum score, the share of required activities completed, the attendance rate and the mock-interview average.
                Below the "Good" threshold the suggestion is Satisfactory / Passable. The admin always makes the final choice.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['excellent' => 'Excellent', 'very_good' => 'Very Good / Très Bien', 'good' => 'Good / Bien'] as $key => $label)
                    <label class="block">
                        <span class="text-dark-300 text-sm">{{ $label }}: minimum %</span>
                        <input type="number" min="1" max="100" name="attestation_mention_{{ $key }}_min" value="{{ $text('attestation_mention_' . $key . '_min') }}" required class="form-input w-full mt-1">
                    </label>
                @endforeach
            </div>
        </section>

        <button type="submit" class="btn btn-primary">Save settings</button>
    </form>
</div>
@endsection
