@extends('layouts.app')

@section('title', 'Live Interview - ' . $interview->type->label())

@push('styles')
<style>
    .pulse-ring {
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.8); opacity: 0.8; }
        50% { transform: scale(1.2); opacity: 0.4; }
        100% { transform: scale(0.8); opacity: 0.8; }
    }
    .speaking-wave {
        display: flex;
        align-items: center;
        gap: 3px;
        height: 24px;
    }
    .speaking-wave span {
        width: 3px;
        background: currentColor;
        border-radius: 2px;
        animation: wave 1s ease-in-out infinite;
    }
    .speaking-wave span:nth-child(1) { animation-delay: 0s; height: 8px; }
    .speaking-wave span:nth-child(2) { animation-delay: 0.1s; height: 16px; }
    .speaking-wave span:nth-child(3) { animation-delay: 0.2s; height: 24px; }
    .speaking-wave span:nth-child(4) { animation-delay: 0.3s; height: 16px; }
    .speaking-wave span:nth-child(5) { animation-delay: 0.4s; height: 8px; }
    @keyframes wave {
        0%, 100% { transform: scaleY(0.5); }
        50% { transform: scaleY(1); }
    }
    .message-enter {
        animation: messageEnter 0.3s ease-out;
    }
    @keyframes messageEnter {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .typing-indicator span {
        animation: typing 1.4s infinite;
        background: currentColor;
        border-radius: 50%;
        display: inline-block;
        height: 8px;
        width: 8px;
        margin: 0 2px;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-8px); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-dark-900 via-dark-800 to-dark-900" 
     x-data="liveInterviewRoom()" 
     x-init="init()"
     @keydown.escape="showEndModal = true">
    
    <!-- Top Bar -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-dark-900/95 backdrop-blur-sm border-b border-dark-700">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Interview Type Badge -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary-600/20 border border-primary-500/30">
                    <span class="text-lg">
                        @if($interview->type->value === 'behavioral') 🗣️
                        @elseif($interview->type->value === 'technical_coding') 💻
                        @elseif($interview->type->value === 'system_design') 🏗️
                        @else 📋
                        @endif
                    </span>
                    <span class="text-primary-300 font-medium">{{ $interview->type->label() }}</span>
                </div>

                <!-- Practice Badge -->
                @if($interview->is_practice)
                <div class="px-3 py-1.5 rounded-lg bg-amber-600/20 border border-amber-500/30">
                    <span class="text-amber-300 text-sm font-medium">Practice Mode</span>
                </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <!-- Timer -->
                <div class="flex items-center gap-2 text-dark-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-mono text-lg" x-text="formatTime(elapsedTime)">00:00</span>
                </div>

                <!-- Progress -->
                <div class="hidden sm:flex items-center gap-2">
                    <div class="w-32 h-2 rounded-full bg-dark-700 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary-500 to-accent-500 transition-all duration-500"
                             :style="`width: ${progress.percentage}%`"></div>
                    </div>
                    <span class="text-dark-400 text-sm" x-text="`${progress.questions_answered}/${progress.total_questions}`"></span>
                </div>

                <!-- End Interview -->
                <button @click="showEndModal = true" 
                        class="btn btn-sm bg-red-600/20 text-red-400 hover:bg-red-600/30 border border-red-500/30">
                    End Interview
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pt-20 pb-32 px-4">
        <div class="max-w-4xl mx-auto">
            
            <!-- Interviewer Avatar & Status -->
            <div class="text-center mb-8">
                <div class="relative inline-block">
                    <!-- Avatar -->
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto"
                         :class="{ 'ring-4 ring-primary-500/50 pulse-ring': aiSpeaking }">
                        <span class="text-4xl">{{ $interview->type->value === 'behavioral' ? '👨‍💼' : ($interview->type->value === 'technical_coding' ? '👩‍💻' : '🧑‍🏫') }}</span>
                    </div>
                    
                    <!-- Speaking Indicator -->
                    <div x-show="aiSpeaking" 
                         class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 px-3 py-1 rounded-full bg-primary-600 text-white text-xs">
                        Speaking...
                    </div>
                </div>
                
                <h2 class="text-white font-semibold mt-4 text-lg" x-text="interviewerName">Alex</h2>
                <p class="text-dark-400 text-sm">AI Interviewer</p>
            </div>

            <!-- Conversation Area -->
            <div class="space-y-4 mb-8" id="conversation">
                <template x-for="(message, index) in messages" :key="index">
                    <div class="message-enter" :class="message.role === 'interviewer' ? 'pr-12' : 'pl-12'">
                        <div class="flex gap-3" :class="message.role === 'interviewer' ? '' : 'flex-row-reverse'">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg"
                                     :class="message.role === 'interviewer' ? 'bg-primary-600/30' : 'bg-dark-600'">
                                    <span x-text="message.role === 'interviewer' ? '🎤' : '👤'"></span>
                                </div>
                            </div>
                            
                            <!-- Message Bubble -->
                            <div class="flex-1 max-w-2xl">
                                <div class="rounded-2xl px-5 py-4"
                                     :class="message.role === 'interviewer' 
                                        ? 'bg-dark-700 rounded-tl-sm' 
                                        : 'bg-primary-600/20 border border-primary-500/30 rounded-tr-sm'">
                                    <p class="text-white whitespace-pre-wrap" x-html="formatMessage(message.content)"></p>
                                    
                                    <!-- Evaluation Badge (if present) -->
                                    <template x-if="message.evaluation && message.evaluation.score">
                                        <div class="mt-3 pt-3 border-t border-dark-600">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-dark-400">Score:</span>
                                                <span class="px-2 py-0.5 rounded text-sm font-medium"
                                                      :class="message.evaluation.score >= 80 ? 'bg-green-600/30 text-green-400' : 
                                                              message.evaluation.score >= 60 ? 'bg-yellow-600/30 text-yellow-400' : 
                                                              'bg-red-600/30 text-red-400'"
                                                      x-text="message.evaluation.score + '%'"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Coaching Tip -->
                                <template x-if="message.coaching_tip">
                                    <div class="mt-2 flex items-start gap-2 text-sm text-amber-400/80">
                                        <span>💡</span>
                                        <span x-text="message.coaching_tip"></span>
                                    </div>
                                </template>
                                
                                <!-- Timestamp -->
                                <p class="text-dark-500 text-xs mt-1" 
                                   :class="message.role === 'interviewer' ? '' : 'text-right'"
                                   x-text="formatTimestamp(message.timestamp)"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="isProcessing" class="pr-12 message-enter">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-600/30 flex items-center justify-center text-lg">
                            🎤
                        </div>
                        <div class="bg-dark-700 rounded-2xl rounded-tl-sm px-5 py-4">
                            <div class="typing-indicator text-primary-400">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area (Fixed Bottom) -->
    <div class="fixed bottom-0 left-0 right-0 bg-dark-900/95 backdrop-blur-sm border-t border-dark-700 p-4">
        <div class="max-w-4xl mx-auto">
            <!-- Voice/Text Toggle & Input -->
            <div class="flex items-end gap-4">
                <!-- Input Mode Toggle -->
                <div class="flex-shrink-0">
                    <div class="flex rounded-xl bg-dark-700 p-1">
                        <button @click="inputMode = 'voice'" 
                                class="px-3 py-2 rounded-lg text-sm transition-all"
                                :class="inputMode === 'voice' ? 'bg-primary-600 text-white' : 'text-dark-400 hover:text-white'">
                            🎤 Voice
                        </button>
                        <button @click="inputMode = 'text'" 
                                class="px-3 py-2 rounded-lg text-sm transition-all"
                                :class="inputMode === 'text' ? 'bg-primary-600 text-white' : 'text-dark-400 hover:text-white'">
                            ⌨️ Text
                        </button>
                    </div>
                </div>

                <!-- Voice Input Mode -->
                <div x-show="inputMode === 'voice'" class="flex-1 flex items-center justify-center gap-4">
                    <!-- Push to Talk Button -->
                    <button @mousedown="startListening" 
                            @mouseup="stopListening" 
                            @mouseleave="stopListening"
                            @touchstart.prevent="startListening"
                            @touchend.prevent="stopListening"
                            class="relative w-20 h-20 rounded-full transition-all duration-200"
                            :class="isListening 
                                ? 'bg-red-500 scale-110 shadow-lg shadow-red-500/50' 
                                : 'bg-primary-600 hover:bg-primary-500 hover:scale-105'"
                            :disabled="isProcessing || aiSpeaking">
                        
                        <!-- Mic Icon -->
                        <svg class="w-8 h-8 text-white mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        
                        <!-- Recording Pulse -->
                        <div x-show="isListening" 
                             class="absolute inset-0 rounded-full bg-red-500 animate-ping opacity-50"></div>
                    </button>

                    <!-- Live Transcript -->
                    <div class="flex-1 max-w-md">
                        <div class="bg-dark-700 rounded-xl px-4 py-3 min-h-[60px]">
                            <p x-show="!interimTranscript && !isListening" class="text-dark-500 text-sm">
                                Hold the button and speak...
                            </p>
                            <p x-show="isListening && !interimTranscript" class="text-primary-400 text-sm flex items-center gap-2">
                                <span class="speaking-wave text-primary-400">
                                    <span></span><span></span><span></span><span></span><span></span>
                                </span>
                                Listening...
                            </p>
                            <p x-show="interimTranscript" class="text-white" x-text="interimTranscript"></p>
                        </div>
                    </div>
                </div>

                <!-- Text Input Mode -->
                <div x-show="inputMode === 'text'" class="flex-1 flex gap-3">
                    <div class="flex-1 relative">
                        <textarea x-model="textInput" 
                                  @keydown.enter.prevent="if (!$event.shiftKey) sendTextMessage()"
                                  placeholder="Type your response... (Enter to send, Shift+Enter for new line)"
                                  rows="2"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-xl px-4 py-3 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none"
                                  :disabled="isProcessing || aiSpeaking"></textarea>
                    </div>
                    <button @click="sendTextMessage" 
                            class="btn btn-primary px-6 h-auto"
                            :disabled="!textInput.trim() || isProcessing || aiSpeaking">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="flex-shrink-0 flex gap-2">
                    <!-- Hint Button -->
                    <button @click="requestHint" 
                            class="btn btn-ghost text-amber-400 hover:bg-amber-600/20"
                            :disabled="isProcessing"
                            title="Get a hint">
                        💡
                    </button>
                    
                    <!-- Skip Button -->
                    <button @click="skipQuestion" 
                            class="btn btn-ghost text-dark-400 hover:text-white hover:bg-dark-600"
                            :disabled="isProcessing"
                            title="Skip question">
                        ⏭️
                    </button>

                    <!-- Voice Settings -->
                    <button @click="showVoiceSettings = !showVoiceSettings" 
                            class="btn btn-ghost text-dark-400 hover:text-white hover:bg-dark-600"
                            title="Voice settings">
                        🔊
                    </button>
                </div>
            </div>

            <!-- Voice Settings Panel -->
            <div x-show="showVoiceSettings" 
                 x-transition
                 class="mt-3 p-4 bg-dark-700 rounded-xl">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-white font-medium">Voice Settings</span>
                    <button @click="showVoiceSettings = false" class="text-dark-400 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-dark-400 text-sm mb-1 block">AI Voice</label>
                        <select x-model="voiceSettings.voice" 
                                @change="updateVoice"
                                class="w-full bg-dark-600 border border-dark-500 rounded-lg px-3 py-2 text-white">
                            <template x-for="voice in availableVoices" :key="voice.name">
                                <option :value="voice.name" x-text="voice.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-dark-400 text-sm mb-1 block">Speed: <span x-text="voiceSettings.rate + 'x'"></span></label>
                        <input type="range" x-model="voiceSettings.rate" min="0.5" max="1.5" step="0.1"
                               class="w-full">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="voiceSettings.enabled" class="rounded">
                            <span class="text-dark-300">Enable AI voice responses</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End Interview Modal -->
    <div x-show="showEndModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
         @click.self="showEndModal = false">
        
        <div class="bg-dark-800 rounded-2xl p-6 max-w-md w-full border border-dark-600">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-red-600/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">End Interview?</h3>
                <p class="text-dark-400 mb-6">
                    You've answered <span class="text-white font-medium" x-text="progress.questions_answered"></span> 
                    out of <span class="text-white font-medium" x-text="progress.total_questions"></span> questions.
                    Are you sure you want to end the interview now?
                </p>
                <div class="flex gap-3">
                    <button @click="showEndModal = false" 
                            class="flex-1 btn btn-outline">
                        Continue Interview
                    </button>
                    <button @click="endInterview" 
                            class="flex-1 btn bg-red-600 hover:bg-red-500 text-white"
                            :disabled="isEnding">
                        <span x-show="!isEnding">End & Get Results</span>
                        <span x-show="isEnding" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview Complete Modal -->
    <div x-show="showResultsModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        
        <div class="bg-dark-800 rounded-2xl p-8 max-w-lg w-full border border-dark-600">
            <div class="text-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">🎉</span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Interview Complete!</h3>
                <p class="text-dark-400">Great job completing your interview</p>
            </div>

            <!-- Score -->
            <div class="bg-dark-700 rounded-xl p-6 mb-6">
                <div class="text-center">
                    <p class="text-dark-400 text-sm uppercase tracking-wide mb-2">Overall Score</p>
                    <p class="text-5xl font-bold"
                       :class="results.overall_score >= 80 ? 'text-green-400' : 
                               results.overall_score >= 60 ? 'text-yellow-400' : 'text-red-400'"
                       x-text="results.overall_score + '%'"></p>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-6 pt-4 border-t border-dark-600">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white" x-text="results.questions_answered || 0"></p>
                        <p class="text-dark-400 text-xs">Answered</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white" x-text="results.duration_minutes || 0"></p>
                        <p class="text-dark-400 text-xs">Minutes</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-white" x-text="results.hints_used || 0"></p>
                        <p class="text-dark-400 text-xs">Hints Used</p>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="mb-6">
                <p class="text-dark-300" x-text="results.summary"></p>
            </div>

            <!-- Strengths & Improvements -->
            <div class="grid sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-green-600/10 border border-green-500/30 rounded-xl p-4">
                    <h4 class="text-green-400 font-medium mb-2 flex items-center gap-2">
                        <span>💪</span> Strengths
                    </h4>
                    <ul class="space-y-1">
                        <template x-for="strength in (results.strengths || []).slice(0, 3)" :key="strength">
                            <li class="text-dark-300 text-sm flex items-start gap-2">
                                <span class="text-green-400">•</span>
                                <span x-text="strength"></span>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="bg-amber-600/10 border border-amber-500/30 rounded-xl p-4">
                    <h4 class="text-amber-400 font-medium mb-2 flex items-center gap-2">
                        <span>🎯</span> To Improve
                    </h4>
                    <ul class="space-y-1">
                        <template x-for="improvement in (results.improvements || []).slice(0, 3)" :key="improvement">
                            <li class="text-dark-300 text-sm flex items-start gap-2">
                                <span class="text-amber-400">•</span>
                                <span x-text="improvement"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <a :href="results.redirect || '{{ route('interviews.index') }}'" 
               class="btn btn-primary w-full py-3">
                View Full Results
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function liveInterviewRoom() {
    return {
        // Interview state
        interviewId: '{{ $interview->id }}',
        interviewerName: '{{ $interview->type->value === "behavioral" ? "Alex" : ($interview->type->value === "technical_coding" ? "Jordan" : "Sam") }}',
        messages: @json($conversation['messages'] ?? []),
        progress: @json($conversation['progress'] ?? ['percentage' => 0, 'questions_answered' => 0, 'total_questions' => 6]),
        
        // Routes (with proper base path)
        routes: {
            message: '{{ route("interviews.live.message", $interview) }}',
            hint: '{{ route("interviews.live.hint", $interview) }}',
            skip: '{{ route("interviews.live.skip", $interview) }}',
            end: '{{ route("interviews.live.end", $interview) }}',
            progress: '{{ route("interviews.live.progress", $interview) }}',
            results: '{{ route("interviews.show", $interview) }}',
        },
        
        // UI state
        inputMode: 'voice',
        textInput: '',
        isProcessing: false,
        aiSpeaking: false,
        isListening: false,
        interimTranscript: '',
        showEndModal: false,
        showResultsModal: false,
        showVoiceSettings: false,
        isEnding: false,
        results: {},
        
        // Timer
        elapsedTime: 0,
        timerInterval: null,
        
        // Speech recognition
        recognition: null,
        
        // Speech synthesis
        synth: window.speechSynthesis,
        availableVoices: [],
        voiceSettings: {
            enabled: true,
            voice: null,
            rate: 1.0,
            pitch: 1.0,
        },

        init() {
            // Start timer
            this.startTimer();
            
            // Initialize speech recognition
            this.initSpeechRecognition();
            
            // Initialize speech synthesis
            this.initSpeechSynthesis();
            
            // Scroll to bottom of conversation
            this.$nextTick(() => this.scrollToBottom());
            
            // Auto-speak first message if voice enabled
            if (this.messages.length > 0 && this.voiceSettings.enabled) {
                setTimeout(() => {
                    this.speakMessage(this.messages[0].content);
                }, 500);
            }
        },

        startTimer() {
            this.timerInterval = setInterval(() => {
                this.elapsedTime++;
            }, 1000);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        initSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                console.warn('Speech recognition not supported');
                this.inputMode = 'text';
                return;
            }

            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = 'en-US';

            this.recognition.onresult = (event) => {
                let interim = '';
                let final = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        final += event.results[i][0].transcript;
                    } else {
                        interim += event.results[i][0].transcript;
                    }
                }

                this.interimTranscript = interim || final;
                
                if (final) {
                    this.sendMessage(final, 'voice');
                }
            };

            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.isListening = false;
            };

            this.recognition.onend = () => {
                this.isListening = false;
            };
        },

        initSpeechSynthesis() {
            // Load voices
            const loadVoices = () => {
                this.availableVoices = this.synth.getVoices().filter(v => v.lang.startsWith('en'));
                if (this.availableVoices.length > 0 && !this.voiceSettings.voice) {
                    // Prefer a natural sounding voice
                    const preferred = this.availableVoices.find(v => 
                        v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('Samantha')
                    );
                    this.voiceSettings.voice = preferred?.name || this.availableVoices[0].name;
                }
            };

            loadVoices();
            this.synth.onvoiceschanged = loadVoices;
        },

        startListening() {
            if (!this.recognition || this.isProcessing || this.aiSpeaking) return;
            
            this.interimTranscript = '';
            this.isListening = true;
            
            try {
                this.recognition.start();
            } catch (e) {
                // Already started
            }
        },

        stopListening() {
            if (!this.recognition) return;
            
            this.isListening = false;
            this.recognition.stop();
        },

        sendTextMessage() {
            if (!this.textInput.trim() || this.isProcessing || this.aiSpeaking) return;
            
            const message = this.textInput.trim();
            this.textInput = '';
            this.sendMessage(message, 'text');
        },

        async sendMessage(content, inputMode) {
            // Stop any AI speech
            this.synth.cancel();
            this.aiSpeaking = false;
            
            // Add user message to UI immediately
            this.messages.push({
                role: 'candidate',
                content: content,
                input_mode: inputMode,
                timestamp: new Date().toISOString(),
            });
            
            this.interimTranscript = '';
            this.isProcessing = true;
            this.scrollToBottom();

            try {
                const response = await fetch(this.routes.message, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: content,
                        input_mode: inputMode,
                    }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                // Add AI response
                this.messages.push({
                    role: 'interviewer',
                    content: data.message,
                    type: data.type,
                    evaluation: data.evaluation,
                    coaching_tip: data.coaching_tip,
                    timestamp: new Date().toISOString(),
                });

                // Update progress
                if (data.progress) {
                    this.progress = data.progress;
                }

                // Check if interview is complete
                if (data.is_complete) {
                    setTimeout(() => this.showEndModal = true, 1500);
                }

                this.scrollToBottom();

                // Speak the response
                if (this.voiceSettings.enabled) {
                    this.speakMessage(data.message);
                }

            } catch (err) {
                console.error('Error sending message:', err);
                this.messages.push({
                    role: 'interviewer',
                    content: "I'm sorry, I had trouble processing that. Could you please repeat?",
                    timestamp: new Date().toISOString(),
                });
            } finally {
                this.isProcessing = false;
            }
        },

        speakMessage(text) {
            if (!this.voiceSettings.enabled) return;
            
            // Clean text for speech
            const cleanText = text
                .replace(/\*\*/g, '')
                .replace(/\*/g, '')
                .replace(/#/g, '');

            const utterance = new SpeechSynthesisUtterance(cleanText);
            
            // Find selected voice
            const voice = this.availableVoices.find(v => v.name === this.voiceSettings.voice);
            if (voice) utterance.voice = voice;
            
            utterance.rate = parseFloat(this.voiceSettings.rate);
            utterance.pitch = parseFloat(this.voiceSettings.pitch);

            utterance.onstart = () => {
                this.aiSpeaking = true;
            };

            utterance.onend = () => {
                this.aiSpeaking = false;
            };

            utterance.onerror = () => {
                this.aiSpeaking = false;
            };

            this.synth.speak(utterance);
        },

        async requestHint() {
            if (this.isProcessing) return;
            
            this.isProcessing = true;

            try {
                const response = await fetch(this.routes.hint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                
                // Add hint as system message
                this.messages.push({
                    role: 'interviewer',
                    content: `💡 **Hint:** ${data.hint}`,
                    type: 'hint',
                    timestamp: new Date().toISOString(),
                });

                this.scrollToBottom();

                if (this.voiceSettings.enabled) {
                    this.speakMessage(data.hint);
                }

            } catch (err) {
                console.error('Error getting hint:', err);
            } finally {
                this.isProcessing = false;
            }
        },

        async skipQuestion() {
            if (this.isProcessing) return;
            
            if (!confirm('Skip this question? It will be marked as unanswered.')) return;
            
            this.isProcessing = true;

            try {
                const response = await fetch(this.routes.skip, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                
                // Add skip notice and new question
                this.messages.push({
                    role: 'interviewer',
                    content: `No problem, let's move on. ${data.message}`,
                    type: 'new_question',
                    timestamp: new Date().toISOString(),
                });

                if (data.progress) {
                    this.progress = data.progress;
                }

                if (data.is_complete) {
                    setTimeout(() => this.showEndModal = true, 1500);
                }

                this.scrollToBottom();

                if (this.voiceSettings.enabled) {
                    this.speakMessage(data.message);
                }

            } catch (err) {
                console.error('Error skipping question:', err);
            } finally {
                this.isProcessing = false;
            }
        },

        async endInterview() {
            this.isEnding = true;

            try {
                const response = await fetch(this.routes.end, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();
                
                this.results = data.results;
                this.showEndModal = false;
                this.showResultsModal = true;

                // Stop timer
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                }

            } catch (err) {
                console.error('Error ending interview:', err);
                alert('Failed to end interview. Please try again.');
            } finally {
                this.isEnding = false;
            }
        },

        formatMessage(content) {
            // Convert markdown bold to HTML
            return content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
        },

        formatTimestamp(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('conversation');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            });
        },

        updateVoice() {
            // Test the new voice
            if (this.voiceSettings.enabled) {
                this.synth.cancel();
                this.speakMessage("Hello, I'm your interviewer today.");
            }
        },
    }
}
</script>
@endpush
@endsection
