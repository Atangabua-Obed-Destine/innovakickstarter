{{-- This week's typing boards: fastest per league, most improved, most race wins. --}}
<div class="grid md:grid-cols-3 gap-5">
    <div class="card p-6">
        <h3 class="text-white font-semibold">⚡ Fastest this week</h3>
        @forelse($leagues as $key => $l)
            @if(!empty($boards['fastest'][$key]))
                <p class="text-dark-400 text-xs uppercase tracking-wider mt-4">{{ $l['icon'] }} {{ $l['label'] }}</p>
                <ol class="mt-1 space-y-1">
                    @foreach($boards['fastest'][$key] as $i => $r)
                        <li class="flex justify-between text-sm {{ $r['id'] === auth()->id() ? 'text-primary-300 font-semibold' : 'text-dark-200' }}"><span>{{ $i + 1 }}. {{ $r['name'] }}</span><span>{{ $r['wpm'] }} wpm</span></li>
                    @endforeach
                </ol>
            @endif
        @empty
        @endforelse
        @if(empty(array_filter($boards['fastest'] ?? [])))<p class="text-dark-500 text-sm mt-3">Nobody has typed this week yet. Be the first!</p>@endif
    </div>
    <div class="card p-6">
        <h3 class="text-white font-semibold">📈 Most improved</h3>
        <p class="text-dark-500 text-xs">Best this week compared with where you started.</p>
        <ol class="mt-3 space-y-1">
            @forelse($boards['improved'] as $i => $r)
                <li class="flex justify-between text-sm {{ $r['id'] === auth()->id() ? 'text-primary-300 font-semibold' : 'text-dark-200' }}"><span>{{ $i + 1 }}. {{ $r['name'] }}</span><span>+{{ $r['gain'] }} wpm</span></li>
            @empty
                <li class="text-dark-500 text-sm">Practise this week to appear here. Every fellow can win this one.</li>
            @endforelse
        </ol>
    </div>
    <div class="card p-6">
        <h3 class="text-white font-semibold">🏆 Race wins this week</h3>
        <ol class="mt-3 space-y-1">
            @forelse($boards['wins'] as $i => $r)
                <li class="flex justify-between text-sm {{ $r['id'] === auth()->id() ? 'text-primary-300 font-semibold' : 'text-dark-200' }}"><span>{{ $i + 1 }}. {{ $r['name'] }}</span><span>{{ $r['wins'] }} {{ Str::plural('win', $r['wins']) }}</span></li>
            @empty
                <li class="text-dark-500 text-sm">No races won yet this week.</li>
            @endforelse
        </ol>
    </div>
</div>
