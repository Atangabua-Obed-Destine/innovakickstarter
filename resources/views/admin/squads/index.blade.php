@extends('layouts.app')

@section('title', 'Squads')

@section('content')
@php $input = 'bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500'; @endphp
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Squads</h1>
            <p class="text-dark-400 text-sm">Teams of 3 to 5 fellows for squad chapters. Auto-forming mixes pathways (backend, frontend, mobile, full-stack) so each squad can build the whole product.</p>
        </div>
        @if($chapters->count() > 1)
            <form method="GET">
                <select name="chapter" class="{{ $input }}" onchange="this.form.submit()">
                    @foreach($chapters as $c)<option value="{{ $c->id }}" @selected($chapter?->id === $c->id)>{{ $c->track?->name }}: {{ $c->title }}</option>@endforeach
                </select>
            </form>
        @endif
    </div>

    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))<div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>@endif
    @endforeach
    @if($errors->any())<div class="p-4 rounded-xl border bg-red-500/10 border-red-500/30 text-red-400 text-sm">{{ $errors->first() }}</div>@endif

    @if(!$chapter)
        <div class="card p-6 text-dark-300">No squad chapters yet. A chapter becomes a squad chapter when the curriculum gives it a squad size.</div>
    @else
        <div class="card p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-white font-semibold">⏳ Waiting for a squad: {{ $waiting->count() }}</p>
                    <p class="text-dark-400 text-sm">Fellows who reached "{{ $chapter->title }}" and have no squad yet.</p>
                </div>
                <form method="POST" action="{{ route('admin.squads.form', $chapter) }}" class="flex items-center gap-2">
                    @csrf
                    <label class="text-dark-300 text-sm" for="size">Squad size</label>
                    <select id="size" name="size" class="{{ $input }}">
                        @foreach([3, 4, 5] as $s)<option value="{{ $s }}" @selected(($chapter->squad_size ?: 4) === $s)>{{ $s }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn-primary text-sm" @disabled($waiting->count() < 2)>Form squads</button>
                </form>
            </div>
            @if($waiting->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($waiting as $w)
                        <span class="px-3 py-1 rounded-lg text-sm text-dark-200" style="background: rgba(255,255,255,.05);">{{ $w->name }} <span class="text-dark-500 text-xs">· {{ $w->pathway ? ucfirst($w->pathway) : 'no pathway' }}</span></span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
            @forelse($squads as $squad)
                <div class="card p-5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-white font-semibold">{{ $squad->name }}</p>
                            <p class="text-dark-400 text-xs">{{ $squad->members->count() }} members · {{ $squad->members->pluck('pathway')->filter()->unique()->map(fn ($p) => ucfirst($p))->implode(', ') ?: 'no pathways' }}</p>
                            @if($squad->repo_url)<a href="{{ $squad->repo_url }}" target="_blank" rel="noopener" class="text-primary-400 text-xs">Repository ↗</a>@endif
                        </div>
                        <form method="POST" action="{{ route('admin.squads.destroy', $squad) }}" onsubmit="return confirm('Dissolve {{ $squad->name }}? Members go back to the waiting list.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Dissolve</button>
                        </form>
                    </div>
                    <ul class="mt-3 space-y-1">
                        @foreach($squad->members as $m)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <span class="text-dark-200">{{ $m->fellow?->name }} <span class="text-dark-500 text-xs">· {{ $m->pathway ? ucfirst($m->pathway) : 'no pathway' }}</span></span>
                                <form method="POST" action="{{ route('admin.squads.remove', $m) }}">@csrf @method('DELETE')<button type="submit" class="text-dark-500 hover:text-red-300 text-xs">Remove</button></form>
                            </li>
                        @endforeach
                    </ul>
                    @if($waiting->isNotEmpty())
                        <form method="POST" action="{{ route('admin.squads.assign', $squad) }}" class="flex items-center gap-2 mt-3">
                            @csrf
                            <select name="fellow_id" class="{{ $input }} flex-1 min-w-0">
                                @foreach($waiting as $w)<option value="{{ $w->id }}">{{ $w->name }} ({{ $w->pathway ?: 'no pathway' }})</option>@endforeach
                            </select>
                            <button type="submit" class="btn-secondary text-xs">Add</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="card p-6 text-dark-300">No squads yet for this chapter.</div>
            @endforelse
        </div>
    @endif
</div>
@endsection
