{{--
    Skill map: radar + bars.
    @param array $skills  from SkillMapService::forFellow
    @param \App\Support\TrackExperience $experience
--}}
@include('partials.track.styles')
@php
    $n = count($skills);
    $cx = 160; $cy = 150; $r = 105;
    $point = function (int $i, float $fraction) use ($n, $cx, $cy, $r) {
        $angle = deg2rad(-90 + $i * 360 / max(1, $n));
        return [round($cx + cos($angle) * $r * $fraction, 1), round($cy + sin($angle) * $r * $fraction, 1)];
    };
    $polygon = collect($skills)->map(fn ($s, $i) => implode(',', $point($i, max(0.03, $s['percent'] / 100))))->implode(' ');
    $next = collect($skills)->filter(fn ($s) => $s['total'] > 0 && $s['percent'] < 100)->sortBy('percent')->first();
@endphp
@if($n >= 3)
<div class="card p-6">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <div>
            <h3 class="text-white font-semibold text-lg">My {{ $experience->role() }} skills</h3>
            <p class="text-dark-400 text-sm">Each skill fills up as your work on it is approved.</p>
        </div>
        @if($next)
            <span class="tx-chip">Next to grow: {{ $next['label'] }}</span>
        @endif
    </div>
    <div class="grid md:grid-cols-2 gap-6 items-center">
        <svg viewBox="-60 -5 440 310" class="tx-radar w-full" role="img" aria-label="Skill map: {{ collect($skills)->map(fn ($s) => $s['label'] . ' ' . $s['percent'] . '%')->implode(', ') }}">
            @foreach([0.25, 0.5, 0.75, 1] as $ring)
                <polygon points="{{ collect(range(0, $n - 1))->map(fn ($i) => implode(',', $point($i, $ring)))->implode(' ') }}"
                         fill="none" stroke="rgba(148,163,184,{{ $ring == 1 ? '0.35' : '0.15' }})" stroke-width="1"/>
            @endforeach
            @foreach($skills as $i => $s)
                @php [$x, $y] = $point($i, 1); [$lx, $ly] = $point($i, 1.2); @endphp
                <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $x }}" y2="{{ $y }}" stroke="rgba(148,163,184,0.15)"/>
                <text x="{{ $lx }}" y="{{ $ly }}" text-anchor="{{ $lx < $cx - 5 ? 'end' : ($lx > $cx + 5 ? 'start' : 'middle') }}" dominant-baseline="middle">{{ $s['label'] }}</text>
            @endforeach
            <polygon points="{{ $polygon }}" fill="var(--color-primary-500)" fill-opacity="0.28" stroke="var(--color-primary-400)" stroke-width="2" stroke-linejoin="round"/>
            @foreach($skills as $i => $s)
                @php [$x, $y] = $point($i, max(0.03, $s['percent'] / 100)); @endphp
                <circle cx="{{ $x }}" cy="{{ $y }}" r="3.5" fill="var(--color-primary-300)"/>
            @endforeach
        </svg>
        <div class="space-y-3">
            @foreach($skills as $s)
                <div title="{{ $s['description'] }}">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-dark-200">{{ $s['label'] }}</span>
                        <span class="text-dark-400">{{ $s['percent'] }}% · {{ $s['completed'] }}/{{ $s['total'] }}</span>
                    </div>
                    <div class="tx-skill-bar"><span style="width: {{ $s['percent'] }}%"></span></div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
