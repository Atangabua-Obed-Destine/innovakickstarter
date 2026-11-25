@extends('layouts.app')

@section('title', 'Key words')

@section('content')
@php
    $data = collect($terms)->map(fn ($t) => ['k' => $t['key'], 't' => $t['term'], 'a' => $t['aliases'], 'd' => $t['definition'], 'e' => $t['example'], 'c' => $t['chapter']])->sortBy(fn ($t) => [$t['c'], mb_strtolower($t['t'])])->values();
@endphp
<div class="max-w-4xl mx-auto space-y-6" x-data="{ q: '', chapter: 0, words: @js($data),
        match(w) { const q = this.q.trim().toLowerCase(); return (!this.chapter || w.c === this.chapter) && (!q || w.t.toLowerCase().includes(q) || w.a.some(a => a.toLowerCase().includes(q)) || w.d.toLowerCase().includes(q)); } }">
    <div>
        <a href="{{ route('curriculum.index') }}" class="text-dark-400 hover:text-white text-sm">← Curriculum</a>
        <h1 class="text-2xl font-bold text-white mt-2">📚 Key words</h1>
        <p class="text-dark-400 mt-1">Every technical word in {{ $track?->name ?? 'your track' }}, in plain English, with an example from the course data. Explaining these words simply is a skill employers test in interviews: try saying each one out loud.</p>
    </div>

    @if(!$terms)
        <div class="card p-6 text-dark-300">Key words for your track are coming soon.</div>
    @else
        <div class="card p-4 flex flex-wrap gap-3 items-center">
            <input type="search" x-model="q" placeholder="Search a word, e.g. median or JOIN" class="flex-1 bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white placeholder-dark-500" style="min-width: 220px;">
            <select x-model.number="chapter" class="bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white">
                <option value="0">All chapters</option>
                @foreach($chapters as $i => $title)
                    <option value="{{ $i + 1 }}">Chapter {{ $i + 1 }}: {{ \Illuminate\Support\Str::limit($title, 40) }}</option>
                @endforeach
            </select>
        </div>
        <p class="text-dark-500 text-sm" x-text="words.filter(w => match(w)).length + ' words'"></p>
        <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <template x-for="w in words" :key="w.k">
                <div class="card p-4" x-show="match(w)">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-white font-semibold" x-text="w.t"></p>
                        <span class="text-dark-500 text-xs flex-shrink-0" x-text="'Chapter ' + w.c"></span>
                    </div>
                    <p class="text-dark-200 text-sm mt-1" x-text="w.d"></p>
                    <p class="text-dark-400 text-sm mt-2" x-show="w.e"><span class="text-primary-300">Example:</span> <span x-text="w.e"></span></p>
                </div>
            </template>
        </div>
    @endif
</div>
@endsection
