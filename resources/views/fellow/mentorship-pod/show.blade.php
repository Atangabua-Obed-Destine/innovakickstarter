@extends('layouts.app')

@section('title', $pod->display_name . ' - Mentorship Pod')

@section('content')
<div class="space-y-6">
    
    <!-- Pod Header -->
    <div class="card overflow-hidden bg-dark-800 border-none shadow-2xl relative mb-8">
        @if($pod->color)
            <div class="absolute inset-x-0 top-0 h-2" style="background-color: {{ $pod->color }}"></div>
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background: radial-gradient(circle at top right, {{ $pod->color }}, transparent 70%)"></div>
        @endif

        <div class="p-6 sm:p-10 relative z-10 flex flex-col md:flex-row gap-8 items-start md:items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-5xl shadow-lg border border-dark-600/50 shrink-0" 
                     style="background-color: {{ $pod->color ?? '#374151' }}20; color: {{ $pod->color ?? '#9CA3AF' }}">
                    {{ $pod->emoji ?? '🫂' }}
                </div>
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight flex items-center gap-3">
                        {{ $pod->display_name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 mt-3 text-dark-300">
                        <p class="flex items-center gap-1.5 font-medium px-3 py-1 rounded-full bg-dark-900/50 border border-dark-700/50">
                            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: {{ $pod->track->color ?? '#8B5CF6' }}"></span>
                            {{ $pod->track->name }}
                        </p>
                        <p class="flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            {{ $pod->active_member_count }} Members
                        </p>
                    </div>
                    @if($pod->description)
                        <p class="mt-4 text-dark-300 text-sm max-w-2xl leading-relaxed">{{ $pod->description }}</p>
                    @endif
                </div>
            </div>

            @if($isLead)
                <button type="button" @click="$dispatch('open-modal', 'edit-branding-modal')" class="shrink-0 btn btn-outline border-dark-600 hover:border-dark-500 bg-dark-900/50 backdrop-blur-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Pod Profile
                </button>
            @endif
        </div>
    </div>

    @if(!$pod->name && $isLead)
        <div class="bg-primary-500/10 border border-primary-500/30 rounded-xl p-5 mb-8 flex items-start gap-4">
            <div class="p-2 bg-primary-500/20 rounded-lg text-primary-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg">Set up your Pod!</h3>
                <p class="text-primary-200 mt-1">As the Pod Lead, you get to choose your team's name, emoji, and color. Click "Edit Pod Profile" above to make it yours.</p>
            </div>
        </div>
    @endif

    <!-- Leaderboard / Members -->
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Pod Leaderboard
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($members as $member)
            <div class="card border {{ $member->id === auth()->id() ? 'border-primary-500/50 bg-primary-900/5 shadow-[0_0_15px_rgba(139,92,246,0.1)]' : 'border-dark-700 bg-dark-800' }} relative overflow-hidden transition-transform hover:scale-[1.01] duration-300">
                
                <!-- Rank Ribbon -->
                <div class="absolute top-0 right-0 w-16 h-16 overflow-hidden">
                    <div class="absolute transform rotate-45 bg-dark-700 text-white font-bold text-xs py-1 right-[-35px] top-[32px] w-[170px] text-center shadow-lg {{ $member->rank === 1 ? 'bg-gradient-to-r from-amber-500 to-amber-600' : ($member->rank === 2 ? 'bg-gradient-to-r from-dark-400 to-dark-500' : ($member->rank === 3 ? 'bg-gradient-to-r from-orange-700 to-orange-800' : '')) }}">
                        Rank #{{ $member->rank }}
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-start gap-5">
                        <div class="avatar avatar-xl relative shrink-0">
                            @if($member->avatar)
                                <img src="{{ $member->avatar }}" alt="{{ $member->name }}">
                            @else
                                {{ substr($member->name, 0, 2) }}
                            @endif
                            
                            @if($member->is_lead)
                                <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-accent-500 flex items-center justify-center text-white shadow-lg border-2 border-dark-800" title="Pod Lead">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6-4.8-6 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0 pr-8">
                            <h3 class="font-bold text-white text-xl truncate flex items-center gap-2">
                                {{ $member->name }}
                                @if($member->id === auth()->id())
                                    <span class="badge badge-primary text-[10px] py-0 px-1.5">YOU</span>
                                @endif
                            </h3>
                            
                            <div class="flex flex-wrap items-center gap-3 mt-2">
                                @if($member->tier)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" style="background-color: {{ $member->tier['color'] }}15; color: {{ $member->tier['color'] }}; border-color: {{ $member->tier['color'] }}30;">
                                    {{ $member->tier['icon'] }} {{ $member->tier['label'] }}
                                </span>
                                @endif
                                <span class="text-xs text-dark-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $member->days_in_program }} days
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-dark-700/50">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-xs text-dark-400 uppercase tracking-wider font-semibold">Career Capital</p>
                                <p class="text-2xl font-black text-white leading-none mt-1">{{ $member->formatted_score }}</p>
                            </div>
                        </div>

                        <!-- Score Breakdown Bar -->
                        @if($member->score_breakdown)
                            <div class="w-full h-2.5 rounded-full bg-dark-900 flex overflow-hidden mt-3 shadow-inner">
                                @foreach($member->score_breakdown as $key => $cat)
                                    @if($cat['score'] > 0)
                                        <div style="width: {{ $cat['score'] }}%; background-color: {{ $cat['color'] }}" 
                                             title="{{ $cat['label'] }}: {{ number_format($cat['score'], 3) }}%"
                                             class="h-full hover:brightness-110 transition-all cursor-help"></div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <!-- Legend -->
                            <div class="flex flex-wrap gap-x-3 gap-y-1.5 mt-3">
                                @foreach($member->score_breakdown as $key => $cat)
                                    @if($cat['score'] > 0)
                                        <div class="flex items-center gap-1.5 text-[10px] text-dark-400">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                            {{ $cat['label'] }}: {{ number_format($cat['score'], 3) }}%
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Edit Branding Modal (Lead Only) -->
@if($isLead)
<div x-data="{ open: false }" 
     @open-modal.window="if ($event.detail === 'edit-branding-modal') open = true"
     class="relative z-50" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     x-show="open"
     style="display: none;">
    
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="open = false"
                 class="relative transform overflow-hidden rounded-xl bg-dark-800 border border-dark-700 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <form action="{{ route('mentorship-pod.branding') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="px-6 py-5 border-b border-dark-700 flex justify-between items-center bg-dark-900/50">
                        <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">Edit Pod Profile</h3>
                        <button type="button" @click="open = false" class="text-dark-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="col-span-3">
                                <label for="name" class="form-label">Pod Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $pod->name) }}" class="form-input w-full" placeholder="e.g. The Innovators" required maxlength="50">
                            </div>
                            <div class="col-span-1">
                                <label for="emoji" class="form-label">Emoji</label>
                                <input type="text" name="emoji" id="emoji" value="{{ old('emoji', $pod->emoji) }}" class="form-input w-full text-center text-xl" placeholder="🚀" maxlength="20">
                                <p class="text-[10px] text-dark-400 mt-1 text-center font-medium leading-tight">Win + . or <br>Cmd + Ctrl + Spc</p>
                            </div>
                        </div>

                        <div>
                            <label for="color" class="form-label">Theme Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="color" id="color" value="{{ old('color', $pod->color ?? '#8B5CF6') }}" class="h-10 w-14 rounded cursor-pointer border border-dark-600 bg-dark-800 p-0.5">
                                <span class="text-sm text-dark-400">Pick a color that represents your pod</span>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="form-label">Pod Mantra / Description</label>
                            <textarea name="description" id="description" rows="3" class="form-input w-full" placeholder="What is your pod about? What are your goals?" maxlength="500">{{ old('description', $pod->description) }}</textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-dark-900/50 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button type="button" @click="open = false" class="btn btn-outline border-dark-600 hover:border-dark-500 text-dark-200">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
