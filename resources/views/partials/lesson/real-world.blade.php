{{--
    "Why employers care": the job-market skill this lesson builds, with a real advert line and a documented real-world example.
    @param array $why  from JobMarketService::whyItMatters()
--}}
@php $s = $why['skill']; @endphp
<style>
    .rw-card { border-radius: 16px; padding: 18px 20px; background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 22%, #0f141d), #0f141d); border: 1px solid color-mix(in srgb, var(--color-primary-500) 30%, transparent); }
    .rw-quote { border-left: 3px solid color-mix(in srgb, var(--color-primary-400) 70%, transparent); padding: 4px 10px; font-style: italic; color: rgb(203 213 225); font-size: 14px; }
</style>
<div class="rw-card">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">💼 Why employers care</p>
        <a href="{{ route('career.index') }}" class="text-primary-300 text-xs underline">See the whole job market →</a>
    </div>
    <p class="text-white font-semibold mt-1">This lesson builds <span class="text-primary-200">{{ $s['label'] }}</span>, asked for in <strong>{{ (int) round($s['frequency'] * 100) }}%</strong> of the {{ $why['postings'] }} entry-level data adverts we studied{{ $why['also'] ? ' (plus ' . implode(', ', $why['also']) . ')' : '' }}.</p>
    <div class="grid md:grid-cols-2 gap-4 mt-3">
        @if($why['quote'])
            <div>
                <p class="text-dark-400 text-xs uppercase tracking-wider mb-1">A real job advert says</p>
                <p class="rw-quote">"{{ $why['quote']['text'] }}"</p>
                <p class="text-dark-500 text-xs mt-1">— <a href="{{ $why['quote']['url'] }}" target="_blank" rel="noopener nofollow" class="underline">{{ $why['quote']['company'] }}</a>, {{ $why['quote']['market'] }}</p>
            </div>
        @endif
        @if($why['example'])
            <div>
                <p class="text-dark-400 text-xs uppercase tracking-wider mb-1">In the real world</p>
                <p class="text-dark-200 text-sm"><strong class="text-white">{{ $why['example']['company'] }}:</strong> {{ $why['example']['example'] }}</p>
                <a href="{{ $why['example']['source_url'] }}" target="_blank" rel="noopener nofollow" class="text-primary-400 text-xs underline">{{ \Illuminate\Support\Str::limit($why['example']['source_title'], 70) }}</a>
            </div>
        @endif
    </div>
</div>
