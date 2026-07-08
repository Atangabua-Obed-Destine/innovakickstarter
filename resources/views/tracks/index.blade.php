@extends('layouts.app')

@section('title', 'Career Tracks')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Career Tracks</h1>
        <p class="text-dark-400 mt-1">Explore available career tracks and see your progress.</p>
    </div>

    <!-- Track Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($tracks as $track)
        <div class="card p-6 hover:border-primary-600/50 transition-colors">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" 
                     style="background: {{ $track->color ?? '#1e293b' }}20; color: {{ $track->color ?? '#6366f1' }}">
                    <x-track-icon :icon="$track->icon" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ $track->name }}</h3>
                    <p class="text-dark-500 text-sm">{{ $track->fellows_count ?? 0 }} fellows enrolled</p>
                </div>
            </div>
            
            @if($track->description)
            <p class="text-dark-400 text-sm mb-4 line-clamp-3">{{ $track->description }}</p>
            @endif

            @if($track->skills)
            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach(array_slice(is_array($track->skills) ? $track->skills : [], 0, 5) as $skill)
                <span class="px-2 py-0.5 text-xs bg-dark-700 text-dark-300 rounded-full">{{ $skill }}</span>
                @endforeach
            </div>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-dark-700">
                <span class="text-dark-500 text-sm">{{ $track->duration_weeks ?? 12 }} weeks</span>
                <a href="{{ route('tracks.enroll') }}" class="text-primary-400 text-sm font-medium hover:text-primary-300 transition-colors">
                    Learn More →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-dark-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-dark-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <p class="text-lg">No tracks available at this time.</p>
            <p class="text-sm mt-1">Check back soon. New tracks are being added regularly.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
