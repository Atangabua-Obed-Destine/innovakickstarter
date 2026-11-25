@extends('layouts.app')

@section('title', 'Job Market')

@section('content')
<div class="max-w-2xl mx-auto card p-8 text-center">
    <p class="text-5xl">💼</p>
    <h1 class="text-2xl font-bold text-white mt-3">Job Market for {{ $track->name }} is coming</h1>
    <p class="text-dark-300 mt-2">We are studying real entry-level job adverts for your track, so we can show you exactly what employers ask for and how close you are. Data Science is first; your track follows.</p>
    <p class="text-dark-400 text-sm mt-4">Meanwhile, keep building reviewed projects: they are the proof every employer looks for.</p>
    <a href="{{ route('curriculum.index') }}" class="btn-primary inline-block mt-5">Back to my curriculum</a>
</div>
@endsection
