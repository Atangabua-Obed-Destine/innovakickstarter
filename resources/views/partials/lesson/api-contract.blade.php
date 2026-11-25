{{-- Backend projects: check a deployed API against the activity's contract. Expects $progress (submit form) or $result (review display). --}}
@if(isset($result))
    @php $r = $result; @endphp
    <div class="ac-box">
        <p class="text-white text-sm font-medium">🔌 API contract check: <span class="{{ $r['passed'] === $r['total'] ? 'text-green-400' : 'text-amber-300' }}">{{ $r['passed'] }} of {{ $r['total'] }} passed</span></p>
        <p class="text-dark-400 text-xs">Checked {{ \Illuminate\Support\Carbon::parse($r['checked_at'])->diffForHumans() }} against <a href="{{ $r['base'] }}" target="_blank" rel="noopener nofollow" class="text-primary-400">{{ $r['base'] }}</a></p>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach($r['results'] as $c)
                <li class="flex gap-2"><span>{{ $c['ok'] ? '✅' : '❌' }}</span><span class="text-dark-200"><b class="text-white">{{ $c['name'] }}</b> <span class="text-dark-500 font-mono text-xs">{{ $c['request'] ?? '' }}{{ isset($c['status']) ? ' → ' . $c['status'] : '' }}</span>@unless($c['ok']) <span class="text-amber-300">{{ $c['message'] }}</span>@endunless</span></li>
            @endforeach
        </ul>
    </div>
@else
<div class="card p-6" x-data="apiContract('{{ route('curriculum.contract-check', $progress) }}', '{{ csrf_token() }}', @js(old('api_base_url', '')))">
    <h3 class="text-white font-semibold mb-1">🔌 Your deployed API <span class="text-red-400">*</span></h3>
    <p class="text-dark-400 text-sm mb-3">IKS will call your live API and check that it behaves as this project's contract says ({{ count($progress->curriculumActivity->api_contract['checks'] ?? []) }} checks). Any language is fine: Laravel, Node, Django, Spring... Run the check as often as you like before you submit; it runs once more when you submit.</p>
    <div class="flex gap-2">
        <input type="url" name="api_base_url" x-model="url" required maxlength="300" placeholder="https://your-api.example.com"
               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        <button type="button" class="btn-secondary flex-shrink-0" @click="run()" :disabled="busy || !url" x-text="busy ? 'Checking...' : 'Check my API'"></button>
    </div>
    @error('api_base_url')<p class="text-red-400 text-xs mt-2">{{ $message }}</p>@enderror
    <p class="text-amber-300 text-sm mt-2" x-show="error" x-text="error" x-cloak></p>
    <template x-if="result">
        <div class="ac-box mt-3">
            <p class="text-sm font-medium" :class="result.passed === result.total ? 'text-green-400' : 'text-amber-300'" x-text="result.passed + ' of ' + result.total + ' checks passed' + (result.passed === result.total ? ' 🎉' : '')"></p>
            <ul class="mt-2 space-y-1 text-sm">
                <template x-for="(c, i) in result.results" :key="i">
                    <li class="flex gap-2"><span x-text="c.ok ? '✅' : '❌'"></span><span class="text-dark-200"><b class="text-white" x-text="c.name"></b> <span class="text-dark-500 font-mono text-xs" x-text="(c.request || '') + (c.status ? ' → ' + c.status : '')"></span> <span class="text-amber-300" x-show="!c.ok" x-text="c.message"></span></span></li>
                </template>
            </ul>
            <p class="text-dark-500 text-xs mt-2" x-show="result.passed < result.total">You can still submit: your reviewer sees these results. Fixing them first usually earns more points.</p>
        </div>
    </template>
</div>
<script>
function apiContract(endpoint, csrf, initial) {
    return {
        url: initial, busy: false, error: '', result: null,
        async run() {
            this.busy = true; this.error = ''; this.result = null;
            try {
                const res = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ url: this.url }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.result = d; } else { this.error = d.error || (res.status === 429 ? 'Too many checks in a minute. Wait a moment.' : 'Could not run the check right now.'); }
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endif
@once
<style>.ac-box { padding: .8rem 1rem; border-radius: .75rem; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); }</style>
@endonce
