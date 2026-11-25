@extends('layouts.app')

@section('title', 'Integrations')

@section('content')
@php
    $sourceBadge = [
        'dashboard' => ['Saved here', 'bg-primary-500/10 text-primary-300 border-primary-500/30'],
        'env' => ['From .env', 'bg-dark-700 text-dark-300 border-dark-600'],
        'none' => ['Not set', 'bg-amber-500/10 text-amber-300 border-amber-500/30'],
    ];
@endphp
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold text-white">Integrations</h1>
        <p class="text-dark-400 mt-1">
            Keys and settings for the outside services the platform uses. A value saved here replaces the one in <code class="text-dark-300">.env</code>;
            remove it to go back to <code class="text-dark-300">.env</code>. Use <strong class="text-dark-200">Test</strong> to check what is in the form before you save it.
        </p>
        <p class="text-dark-500 text-sm mt-2">
            Keys are stored encrypted and are never shown again after saving, only their last four characters.
            They are encrypted with the server's <code>APP_KEY</code>: if that key is ever changed, enter them again here.
        </p>
    </div>

    <nav class="flex flex-wrap gap-2">
        @foreach($groups as $group => $meta)
            @php
                $active = $meta['rows']['ai_provider']['value'] ?? null;
                $missing = collect($meta['rows'])->first(fn ($r) => !empty($r['secret']) && !$r['is_set'] && $group !== 'mail'
                    && (empty($r['provider']) || $r['provider'] === $active));
            @endphp
            <a href="#{{ $group }}" class="px-3 py-1.5 rounded-lg bg-dark-800 border border-dark-700 text-sm text-dark-200 hover:border-primary-500/50">
                {{ $meta['title'] }}
                @if($missing)<span class="ml-1 text-amber-400" title="Not configured">●</span>@endif
            </a>
        @endforeach
    </nav>

    @foreach($groups as $group => $meta)
        <section id="{{ $group }}" class="card p-6"
                 x-data="Object.assign(integrationTest(@js(route('admin.integrations.test', $group))), { provider: @js($meta['rows']['ai_provider']['value'] ?? null), backup: @js($meta['rows']['ai_backup_provider']['value'] ?? null) })">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-5">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ $meta['title'] }}</h2>
                    <p class="text-dark-400 text-sm">{{ $meta['used_for'] }}</p>
                </div>
            </div>

            <form x-ref="form" method="POST" action="{{ route('admin.integrations.update', $group) }}" class="space-y-5" autocomplete="off">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($meta['rows'] as $key => $row)
                        @php
                            [$badgeText, $badgeClass] = $sourceBadge[$row['source']];
                            $wide = in_array($key, ['openai_api_key', 'groq_api_key', 'gemini_api_key', 'github_token'], true);
                            $error = $errors->first("values.{$key}");
                        @endphp
                        <div class="{{ $wide ? 'sm:col-span-2' : '' }}"
                             @if(!empty($row['provider'])) x-show="[provider, backup].includes(@js($row['provider']))"
                                 @if(!in_array($row['provider'], [$meta['rows']['ai_provider']['value'] ?? null, $meta['rows']['ai_backup_provider']['value'] ?? null], true)) style="display: none" @endif @endif>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label for="f-{{ $key }}" class="text-dark-200 text-sm font-medium">{{ $row['label'] }}</label>
                                <span class="text-[11px] px-2 py-0.5 rounded-full border {{ $badgeClass }}"
                                      title="{{ $row['source'] === 'dashboard' && $row['updated_at'] ? 'Saved ' . $row['updated_at']->diffForHumans() . ($row['updated_by'] ? ' by ' . $row['updated_by'] : '') : $row['env'] }}">
                                    {{ $badgeText }}
                                </span>
                            </div>

                            @if(!empty($row['secret']))
                                {{-- type=password + new-password stops browsers from autofilling the admin's own login password here --}}
                                <input id="f-{{ $key }}" type="password" name="values[{{ $key }}]" value=""
                                       autocomplete="new-password" spellcheck="false"
                                       placeholder="{{ $row['is_set'] ? 'Current: ' . $row['masked'] . '  (leave blank to keep it)' : ($row['placeholder'] ?? '') }}"
                                       class="form-input w-full font-mono text-sm">
                                @if($row['source'] === 'dashboard')
                                    <label class="inline-flex items-center gap-2 mt-1.5 text-xs text-dark-400">
                                        <input type="checkbox" name="remove[{{ $key }}]" value="1" class="rounded border-dark-600 bg-dark-800">
                                        Remove the saved key{{ $row['env'] ? ' (go back to ' . $row['env'] . ' in .env)' : '' }}
                                    </label>
                                @endif
                            @elseif(!empty($row['options']))
                                <select id="f-{{ $key }}" name="values[{{ $key }}]" class="form-input w-full" @if($key === 'ai_provider') x-model="provider" @elseif($key === 'ai_backup_provider') x-model="backup" @endif>
                                    @foreach($row['options'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old("values.{$key}", $row['value']) === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="f-{{ $key }}" type="{{ ($row['cast'] ?? null) === 'int' ? 'number' : 'text' }}" name="values[{{ $key }}]"
                                       value="{{ old("values.{$key}", $row['value']) }}" placeholder="{{ $row['placeholder'] ?? '' }}"
                                       spellcheck="false" class="form-input w-full">
                            @endif

                            @if($error)
                                <p class="text-red-400 text-xs mt-1">{{ $error }}</p>
                            @elseif(!empty($row['help']))
                                <p class="text-dark-500 text-xs mt-1">{{ $row['help'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($group === 'mail')
                    <div class="pt-1">
                        <label for="f-test-to" class="text-dark-200 text-sm font-medium">Send the test email to</label>
                        <input id="f-test-to" type="email" name="test_to" value="{{ auth()->user()->email }}" class="form-input w-full sm:w-80 mt-1">
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-dark-700/60">
                    <button type="submit" class="btn btn-primary mt-4">Save</button>
                    <button type="button" class="btn btn-secondary mt-4" @click="run()" :disabled="busy">
                        <span x-text="busy ? 'Testing…' : '{{ $group === 'mail' ? 'Send test email' : 'Test' }}'">{{ $group === 'mail' ? 'Send test email' : 'Test' }}</span>
                    </button>
                    <span class="text-dark-500 text-xs mt-4">Tests use what is in the form; nothing is saved until you click Save.</span>
                </div>
            </form>

            <div x-show="result" style="display: none" class="mt-4 rounded-lg border p-4 text-sm"
                 :class="{
                    'border-green-500/30 bg-green-500/5 text-green-300': result?.status === 'ok',
                    'border-amber-500/40 bg-amber-500/5 text-amber-200': result?.status === 'warning',
                    'border-red-500/40 bg-red-500/5 text-red-300': result?.status === 'error',
                 }">
                <p class="font-medium">
                    <span x-text="{ ok: '✓', warning: '!', error: '✗' }[result?.status]"></span>
                    <span x-text="result?.message"></span>
                </p>
                <ul class="mt-2 space-y-0.5 text-dark-300 text-xs" x-show="result?.details?.length">
                    <template x-for="line in (result?.details || [])">
                        <li x-text="'• ' + line"></li>
                    </template>
                </ul>
            </div>
        </section>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function integrationTest(url) {
        return {
            busy: false,
            result: null,
            async run() {
                this.busy = true;
                this.result = null;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(this.$refs.form),
                    });
                    const json = await response.json().catch(() => ({}));

                    if (response.ok) {
                        this.result = json;
                    } else if (response.status === 422) {
                        this.result = { status: 'error', message: Object.values(json.errors || {}).flat().join(' ') || json.message, details: [] };
                    } else if (response.status === 429) {
                        this.result = { status: 'error', message: 'Too many tests in a minute. Wait a moment and try again.', details: [] };
                    } else if (response.status === 419) {
                        this.result = { status: 'error', message: 'Your session expired. Reload the page and try again.', details: [] };
                    } else {
                        this.result = { status: 'error', message: 'The test itself failed on the server (HTTP ' + response.status + '). Check storage/logs.', details: [] };
                    }
                } catch (e) {
                    this.result = { status: 'error', message: 'Could not reach the server: ' + e.message, details: [] };
                }
                this.busy = false;
            },
        };
    }
</script>
@endpush
