@extends('layouts.app')

@section('title', 'Live AI Interview')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-dark-900 via-dark-800 to-dark-900" x-data="liveInterviewLobby()">
    <div class="max-w-4xl mx-auto px-4 py-12">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-white mb-3">Live AI Interview</h1>
            <p class="text-dark-400 text-lg max-w-2xl mx-auto">
                Have a real conversation with your AI interviewer. Voice-enabled, 
                context-aware follow-ups, and instant feedback.
            </p>
        </div>

        <!-- Features Preview -->
        <div class="grid md:grid-cols-3 gap-4 mb-12">
            <div class="card p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">🎤</span>
                </div>
                <h3 class="text-white font-medium mb-1">Voice Conversation</h3>
                <p class="text-dark-400 text-sm">Speak naturally, AI responds with voice</p>
            </div>
            <div class="card p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">💭</span>
                </div>
                <h3 class="text-white font-medium mb-1">Dynamic Follow-ups</h3>
                <p class="text-dark-400 text-sm">AI asks relevant follow-up questions</p>
            </div>
            <div class="card p-5 text-center">
                <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">⚡</span>
                </div>
                <h3 class="text-white font-medium mb-1">Real-time Feedback</h3>
                <p class="text-dark-400 text-sm">Get coaching tips during the interview</p>
            </div>
        </div>

        <!-- Setup Form -->
        <div class="card p-8">
            <h2 class="text-xl font-semibold text-white mb-6">Configure Your Interview</h2>
            
            <form @submit.prevent="startInterview" class="space-y-6">
                <!-- Track Selection -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Track</label>
                    <div class="grid sm:grid-cols-3 gap-3">
                        @foreach($tracks as $track)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="track" value="{{ $track->id }}" 
                                   x-model="form.track_id" class="sr-only peer">
                            <div class="p-4 rounded-xl border-2 border-dark-600 peer-checked:border-primary-500 peer-checked:bg-primary-500/10 transition-all">
                                <div class="text-center">
                                    <x-track-icon :icon="$track->icon" class="w-6 h-6" />
                                    <p class="text-white font-medium mt-2">{{ $track->name }}</p>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Interview Type -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Interview Type</label>
                    <div class="grid sm:grid-cols-3 gap-3">
                        @foreach($interviewTypes as $type)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="{{ $type->value }}" 
                                   x-model="form.type" class="sr-only peer">
                            <div class="p-4 rounded-xl border-2 border-dark-600 peer-checked:border-primary-500 peer-checked:bg-primary-500/10 transition-all">
                                <div class="text-center">
                                    <span class="text-2xl">
                                        @if($type->value === 'behavioral') 🗣️
                                        @elseif($type->value === 'technical_coding') 💻
                                        @elseif($type->value === 'system_design') 🏗️
                                        @else 📋
                                        @endif
                                    </span>
                                    <p class="text-white font-medium mt-2">{{ $type->label() }}</p>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Difficulty -->
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Difficulty Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="difficulty" value="beginner" 
                                   x-model="form.difficulty" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 border-dark-600 peer-checked:border-green-500 peer-checked:bg-green-500/10 text-center transition-all">
                                <span class="text-green-400 font-medium">Beginner</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="difficulty" value="intermediate" 
                                   x-model="form.difficulty" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 border-dark-600 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 text-center transition-all">
                                <span class="text-yellow-400 font-medium">Intermediate</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="difficulty" value="advanced" 
                                   x-model="form.difficulty" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 border-dark-600 peer-checked:border-red-500 peer-checked:bg-red-500/10 text-center transition-all">
                                <span class="text-red-400 font-medium">Advanced</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Practice Mode Toggle -->
                <div class="flex items-center justify-between p-4 rounded-xl bg-dark-700/50 border border-dark-600">
                    <div>
                        <p class="text-white font-medium">Practice Mode</p>
                        <p class="text-dark-400 text-sm">No impact on your Career Capital score</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="form.is_practice" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <!-- Microphone Check -->
                <div class="p-4 rounded-xl border border-dark-600" :class="micStatus === 'ready' ? 'bg-green-500/10 border-green-500/50' : 'bg-dark-700/50'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                 :class="micStatus === 'ready' ? 'bg-green-500/20' : micStatus === 'error' ? 'bg-red-500/20' : 'bg-dark-600'">
                                <svg class="w-5 h-5" :class="micStatus === 'ready' ? 'text-green-400' : micStatus === 'error' ? 'text-red-400' : 'text-dark-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-medium">Microphone</p>
                                <p class="text-sm" :class="micStatus === 'ready' ? 'text-green-400' : micStatus === 'error' ? 'text-red-400' : 'text-dark-400'">
                                    <span x-show="micStatus === 'checking'">Checking...</span>
                                    <span x-show="micStatus === 'ready'">✓ Ready to use</span>
                                    <span x-show="micStatus === 'error'">✗ Access denied</span>
                                    <span x-show="micStatus === 'idle'">Click to test</span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="testMicrophone" 
                                class="btn btn-sm" :class="micStatus === 'ready' ? 'btn-success' : 'btn-outline'">
                            <span x-show="micStatus !== 'checking'">Test Mic</span>
                            <span x-show="micStatus === 'checking'">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Start Button -->
                <button type="submit" 
                        class="w-full btn btn-primary btn-lg py-4 text-lg font-semibold"
                        :disabled="!canStart || loading">
                    <span x-show="!loading" class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Start Live Interview
                    </span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Connecting to Interviewer...
                    </span>
                </button>
            </form>
        </div>

        <!-- Tips -->
        <div class="mt-8 p-6 rounded-xl bg-dark-800/50 border border-dark-700">
            <h3 class="text-white font-medium mb-3 flex items-center gap-2">
                <span class="text-xl">💡</span> Tips for Success
            </h3>
            <ul class="space-y-2 text-dark-400 text-sm">
                <li class="flex items-start gap-2">
                    <span class="text-primary-400">•</span>
                    Find a quiet environment with minimal background noise
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-primary-400">•</span>
                    Speak clearly and at a natural pace - the AI will wait for you
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-primary-400">•</span>
                    Use the STAR method for behavioral questions (Situation, Task, Action, Result)
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-primary-400">•</span>
                    Don't be afraid to ask for clarification - it's a conversation!
                </li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
function liveInterviewLobby() {
    return {
        form: {
            track_id: '{{ $preselectedTrack ?? $tracks->first()?->id }}',
            type: 'behavioral',
            difficulty: 'intermediate',
            is_practice: true,
        },
        micStatus: 'idle', // idle, checking, ready, error
        loading: false,

        get canStart() {
            return this.form.track_id && this.form.type && !this.loading;
        },

        async testMicrophone() {
            this.micStatus = 'checking';
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach(track => track.stop());
                this.micStatus = 'ready';
            } catch (err) {
                console.error('Microphone error:', err);
                this.micStatus = 'error';
            }
        },

        async startInterview() {
            if (!this.canStart) return;
            
            this.loading = true;

            try {
                const response = await fetch('{{ route("interviews.live.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Failed to start interview');
                    this.loading = false;
                }
            } catch (err) {
                console.error('Error starting interview:', err);
                alert('Failed to start interview. Please try again.');
                this.loading = false;
            }
        },

        init() {
            // Auto-test microphone on load
            setTimeout(() => this.testMicrophone(), 500);
        }
    }
}
</script>
@endpush
@endsection
