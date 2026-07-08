@extends('layouts.app')

@section('title', 'AI Interview - ' . $interview->type->label())

@push('styles')
<style>
    /* Voice Recording Animation */
    @keyframes pulse-ring {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(1.3); opacity: 0; }
    }
    .recording-pulse::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.4);
        animation: pulse-ring 1.5s ease-out infinite;
    }
    
    /* Code Editor Styling */
    .code-editor {
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 14px;
        line-height: 1.5;
        tab-size: 4;
        background: #1e1e2e;
        color: #cdd6f4;
    }
    .code-editor:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5);
    }
    
    /* Whiteboard Canvas */
    .whiteboard-canvas {
        cursor: crosshair;
        touch-action: none;
    }
    .whiteboard-canvas.eraser {
        cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M16.24 3.56l4.95 4.94-12.02 12.02-4.95-4.95 12.02-12.01m0-2.83L3.51 13.47a1.994 1.994 0 000 2.83l4.95 4.95a1.994 1.994 0 002.83 0L24 8.54a1.994 1.994 0 000-2.83l-4.93-4.98a2.034 2.034 0 00-2.83 0z'/%3E%3C/svg%3E") 12 12, auto;
    }
    
    /* Recording indicator */
    .recording-indicator {
        animation: blink 1s infinite;
    }
    @keyframes blink {
        50% { opacity: 0.5; }
    }
    
    /* Preparation checklist */
    .checklist-item.checked .checkmark {
        background: linear-gradient(135deg, #10b981, #059669);
    }
</style>
@endpush

@section('content')
<div x-data="enhancedAIInterview()" x-init="init()" class="max-w-6xl mx-auto" x-cloak>
    
    <!-- Practice Mode Banner -->
    @if(request()->has('practice') || ($interview->is_practice ?? false))
    <div class="bg-amber-600/20 border border-amber-500/30 rounded-lg p-4 mb-6 flex items-center gap-3">
        <span class="text-2xl">🎯</span>
        <div>
            <p class="text-amber-400 font-medium">Practice Mode Active</p>
            <p class="text-amber-400/70 text-sm">This interview won't affect your Career Capital score.</p>
        </div>
    </div>
    @endif

    <!-- Interview Header -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="text-2xl">🤖</span>
                    AI {{ $interview->type->label() }} Interview
                    @if($interview->is_practice ?? false)
                    <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-1 rounded">PRACTICE</span>
                    @endif
                </h1>
                <p class="text-dark-400 mt-1">Track: {{ $interview->track->name }}</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Recording Indicator -->
                <div x-show="isRecording" class="flex items-center gap-2 text-red-400">
                    <div class="w-3 h-3 bg-red-500 rounded-full recording-indicator"></div>
                    <span class="text-sm font-medium">Recording</span>
                </div>
                
                <!-- Timer -->
                <div class="text-center">
                    <div class="text-2xl font-mono font-bold" 
                         :class="timeRemaining < 60 ? 'text-red-400 animate-pulse' : 'text-primary-400'"
                         x-text="formatTime(timeRemaining)">
                        --:--
                    </div>
                    <p class="text-dark-500 text-xs">Time Remaining</p>
                </div>
                <!-- Progress -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-teal-400">
                        <span x-text="currentQuestion"></span>/<span x-text="totalQuestions"></span>
                    </div>
                    <p class="text-dark-500 text-xs">Questions</p>
                </div>
            </div>
        </div>
        <!-- Progress Bar -->
        <div class="mt-4 h-2 bg-dark-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-primary-500 to-teal-500 transition-all duration-500"
                 :style="'width: ' + ((currentQuestion - 1) / totalQuestions * 100) + '%'"></div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- STATE: PREPARATION (NEW!) -->
    <!-- ============================================ -->
    <template x-if="state === 'preparation'">
        <div class="card p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto bg-primary-600/20 rounded-full flex items-center justify-center text-4xl mb-4">
                    📋
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Interview Preparation</h2>
                <p class="text-dark-400">Complete this checklist before starting your interview</p>
            </div>

            <!-- Environment Check -->
            <div class="max-w-2xl mx-auto space-y-4 mb-8">
                <h3 class="text-lg font-semibold text-white mb-4">Environment Check</h3>
                
                <!-- Checklist Items -->
                <div class="space-y-3">
                    <div class="checklist-item flex items-center gap-4 p-4 bg-dark-800 rounded-lg cursor-pointer"
                         :class="{ 'checked': checklist.quietEnvironment }"
                         @click="checklist.quietEnvironment = !checklist.quietEnvironment">
                        <div class="checkmark w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center transition-all"
                             :class="checklist.quietEnvironment ? 'bg-green-500 border-green-500' : ''">
                            <svg x-show="checklist.quietEnvironment" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">Quiet Environment</p>
                            <p class="text-dark-500 text-sm">Find a quiet space with minimal distractions</p>
                        </div>
                        <span class="text-2xl">🔇</span>
                    </div>
                    
                    <div class="checklist-item flex items-center gap-4 p-4 bg-dark-800 rounded-lg cursor-pointer"
                         :class="{ 'checked': checklist.stableInternet }"
                         @click="checklist.stableInternet = !checklist.stableInternet">
                        <div class="checkmark w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center transition-all"
                             :class="checklist.stableInternet ? 'bg-green-500 border-green-500' : ''">
                            <svg x-show="checklist.stableInternet" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">Stable Internet Connection</p>
                            <p class="text-dark-500 text-sm">Ensure your connection is reliable</p>
                        </div>
                        <span class="text-2xl">📶</span>
                    </div>
                    
                    <div class="checklist-item flex items-center gap-4 p-4 bg-dark-800 rounded-lg cursor-pointer"
                         :class="{ 'checked': checklist.microphoneReady }"
                         @click="testMicrophone()">
                        <div class="checkmark w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center transition-all"
                             :class="checklist.microphoneReady ? 'bg-green-500 border-green-500' : ''">
                            <svg x-show="checklist.microphoneReady" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">Microphone Ready</p>
                            <p class="text-dark-500 text-sm">Click to test your microphone</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1">
                                <template x-for="i in 5" :key="i">
                                    <div class="w-1 h-4 bg-dark-600 rounded-full transition-all"
                                         :class="micLevel >= i * 20 ? 'bg-green-500' : ''"
                                         :style="'height: ' + (8 + i * 4) + 'px'"></div>
                                </template>
                            </div>
                            <span class="text-2xl">🎤</span>
                        </div>
                    </div>
                    
                    <div class="checklist-item flex items-center gap-4 p-4 bg-dark-800 rounded-lg cursor-pointer"
                         :class="{ 'checked': checklist.cameraReady }"
                         @click="testCamera()">
                        <div class="checkmark w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center transition-all"
                             :class="checklist.cameraReady ? 'bg-green-500 border-green-500' : ''">
                            <svg x-show="checklist.cameraReady" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">Camera Ready (Optional)</p>
                            <p class="text-dark-500 text-sm">Click to test your camera for recording</p>
                        </div>
                        <span class="text-2xl">📹</span>
                    </div>
                    
                    <div class="checklist-item flex items-center gap-4 p-4 bg-dark-800 rounded-lg cursor-pointer"
                         :class="{ 'checked': checklist.readInstructions }"
                         @click="checklist.readInstructions = !checklist.readInstructions">
                        <div class="checkmark w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center transition-all"
                             :class="checklist.readInstructions ? 'bg-green-500 border-green-500' : ''">
                            <svg x-show="checklist.readInstructions" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-white font-medium">Read Interview Guidelines</p>
                            <p class="text-dark-500 text-sm">Understand the interview format and expectations</p>
                        </div>
                        <span class="text-2xl">📖</span>
                    </div>
                </div>
            </div>

            <!-- Camera Preview -->
            <div x-show="showCameraPreview" x-transition class="max-w-md mx-auto mb-6">
                <div class="relative rounded-lg overflow-hidden bg-dark-800">
                    <video x-ref="cameraPreview" autoplay muted playsinline class="w-full h-48 object-cover"></video>
                    <button @click="stopCameraPreview()" 
                            class="absolute top-2 right-2 p-2 bg-dark-900/80 rounded-full hover:bg-dark-700 transition-colors">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="max-w-2xl mx-auto mb-8">
                <div class="bg-gradient-to-br from-primary-600/10 to-teal-600/10 border border-primary-500/30 rounded-lg p-6">
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="text-xl">💡</span>
                        Tips for {{ $interview->type->label() }} Interviews
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        @if($interview->type->value === 'behavioral')
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Use the STAR method (Situation, Task, Action, Result)</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Provide specific examples from your experience</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Focus on your role and contributions</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Quantify results when possible</span>
                        </div>
                        @elseif($interview->type->value === 'technical_coding')
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Think out loud as you code</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Start with a brute force solution, then optimize</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Consider edge cases and test your code</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Discuss time and space complexity</span>
                        </div>
                        @elseif($interview->type->value === 'system_design')
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Clarify requirements before designing</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Use the whiteboard to draw diagrams</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Consider scalability and trade-offs</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Break down into components</span>
                        </div>
                        @else
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Think through your answer before responding</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Be specific and provide examples</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Ask clarifying questions if needed</span>
                        </div>
                        <div class="flex items-start gap-2 text-dark-300">
                            <span class="text-green-400">✓</span>
                            <span>Structure your answers clearly</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recording Option -->
            <div class="max-w-2xl mx-auto mb-8">
                <div class="flex items-center justify-between p-4 bg-dark-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🎬</span>
                        <div>
                            <p class="text-white font-medium">Record Interview</p>
                            <p class="text-dark-500 text-sm">Save a recording for later review</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="enableRecording" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>

            <!-- Start Button -->
            <div class="text-center">
                <button @click="startInterview()" 
                        class="btn btn-primary btn-lg"
                        :disabled="!isPreparationComplete"
                        :class="{ 'opacity-50 cursor-not-allowed': !isPreparationComplete }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Start Interview
                </button>
                <p class="text-dark-500 text-sm mt-2" x-show="!isPreparationComplete">
                    Complete all checklist items to continue
                </p>
            </div>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- STATE: QUESTION ACTIVE (ENHANCED) -->
    <!-- ============================================ -->
    <template x-if="state === 'question'">
        <div class="space-y-6">
            <!-- Question Card -->
            <div class="card p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-primary-600/20 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🤖</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-primary-400 font-medium">AI Interviewer</span>
                            <span class="text-dark-500 text-sm" x-text="'Question ' + currentQuestion"></span>
                            <span x-show="currentQuestionData?.type" 
                                  class="text-xs px-2 py-0.5 bg-dark-700 rounded text-dark-400"
                                  x-text="currentQuestionData?.type"></span>
                        </div>
                        <p class="text-white text-lg leading-relaxed" x-text="currentQuestionData?.question"></p>
                        
                        <!-- Hints (collapsible) -->
                        <template x-if="currentQuestionData?.hints?.length > 0">
                            <div class="mt-4">
                                <button @click="showHints = !showHints" 
                                        class="text-sm text-dark-400 hover:text-dark-300 flex items-center gap-1">
                                    <svg class="w-4 h-4 transition-transform" :class="showHints ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    Need a hint?
                                </button>
                                <div x-show="showHints" x-transition class="mt-2 p-3 bg-dark-800 rounded-lg">
                                    <ul class="text-dark-400 text-sm space-y-1">
                                        <template x-for="hint in currentQuestionData.hints" :key="hint">
                                            <li class="flex items-start gap-2">
                                                <span class="text-amber-400">💡</span>
                                                <span x-text="hint"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Response Area - Dynamic based on interview type -->
            <div class="card p-6">
                <!-- Response Mode Tabs -->
                <div class="flex items-center gap-4 mb-4 border-b border-dark-700 pb-4">
                    <button @click="responseMode = 'text'" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="responseMode === 'text' ? 'bg-primary-600/20 text-primary-400' : 'text-dark-400 hover:text-white'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Text
                    </button>
                    <button @click="responseMode = 'voice'; initVoiceRecording()" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="responseMode === 'voice' ? 'bg-primary-600/20 text-primary-400' : 'text-dark-400 hover:text-white'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        Voice
                    </button>
                    @if(in_array($interview->type->value, ['technical_coding']))
                    <button @click="responseMode = 'code'" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="responseMode === 'code' ? 'bg-primary-600/20 text-primary-400' : 'text-dark-400 hover:text-white'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Code
                    </button>
                    @endif
                    @if(in_array($interview->type->value, ['system_design']))
                    <button @click="responseMode = 'whiteboard'" 
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="responseMode === 'whiteboard' ? 'bg-primary-600/20 text-primary-400' : 'text-dark-400 hover:text-white'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        Whiteboard
                    </button>
                    @endif
                </div>

                <!-- TEXT MODE -->
                <div x-show="responseMode === 'text'" x-transition>
                    <label class="block text-dark-300 font-medium mb-2">Your Response:</label>
                    <textarea 
                        x-model="currentResponse"
                        class="form-input w-full h-48 resize-none"
                        placeholder="Type your answer here... Think through your response carefully."
                        :disabled="isSubmitting"
                    ></textarea>
                </div>

                <!-- VOICE MODE -->
                <div x-show="responseMode === 'voice'" x-transition>
                    <div class="text-center py-8">
                        <!-- Voice Recording Button -->
                        <div class="relative inline-block mb-6">
                            <button @click="toggleVoiceRecording()" 
                                    class="relative w-24 h-24 rounded-full transition-all"
                                    :class="isVoiceRecording ? 'bg-red-500 hover:bg-red-600 recording-pulse' : 'bg-primary-600 hover:bg-primary-700'">
                                <svg x-show="!isVoiceRecording" class="w-10 h-10 text-white mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                <svg x-show="isVoiceRecording" class="w-10 h-10 text-white mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <p class="text-dark-300 mb-4" x-text="isVoiceRecording ? 'Recording... Click to stop' : 'Click to start speaking'"></p>
                        
                        <!-- Waveform Visualization -->
                        <div x-show="isVoiceRecording" class="flex justify-center gap-1 h-12 items-end mb-4">
                            <template x-for="i in 20" :key="i">
                                <div class="w-1 bg-primary-500 rounded-full transition-all"
                                     :style="'height: ' + (Math.random() * 100) + '%'"
                                     x-effect="setInterval(() => $el.style.height = (Math.random() * 100) + '%', 100)"></div>
                            </template>
                        </div>
                        
                        <!-- Transcription Preview -->
                        <div class="text-left bg-dark-800 rounded-lg p-4 min-h-[100px]">
                            <label class="block text-dark-400 text-sm mb-2">Transcription (editable):</label>
                            <textarea 
                                x-model="currentResponse"
                                class="w-full bg-transparent text-white resize-none border-none focus:outline-none"
                                placeholder="Your speech will be transcribed here..."
                                rows="4"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- CODE MODE -->
                <div x-show="responseMode === 'code'" x-transition>
                    <div class="mb-3 flex items-center justify-between">
                        <label class="text-dark-300 font-medium">Code Editor:</label>
                        <select x-model="codeLanguage" class="form-input py-1 px-3 text-sm w-auto">
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="java">Java</option>
                            <option value="cpp">C++</option>
                            <option value="typescript">TypeScript</option>
                            <option value="php">PHP</option>
                        </select>
                    </div>
                    <div class="relative">
                        <!-- Line Numbers -->
                        <div class="absolute left-0 top-0 bottom-0 w-10 bg-dark-900 text-dark-500 text-right pr-2 pt-3 text-sm font-mono overflow-hidden rounded-l-lg">
                            <template x-for="i in Math.max(currentResponse.split('\\n').length, 10)" :key="i">
                                <div x-text="i"></div>
                            </template>
                        </div>
                        <textarea 
                            x-model="currentResponse"
                            class="code-editor form-input w-full h-64 resize-none pl-12 rounded-lg"
                            placeholder="// Write your code here..."
                            :disabled="isSubmitting"
                            spellcheck="false"
                            @keydown.tab.prevent="insertTab($event)"
                        ></textarea>
                    </div>
                    <!-- Code Actions -->
                    <div class="flex gap-2 mt-3">
                        <button @click="runCode()" class="btn btn-outline btn-sm" :disabled="!currentResponse.trim()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            </svg>
                            Run Code
                        </button>
                        <button @click="formatCode()" class="btn btn-outline btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                            </svg>
                            Format
                        </button>
                    </div>
                    <!-- Code Output -->
                    <div x-show="codeOutput" x-transition class="mt-4">
                        <label class="block text-dark-400 text-sm mb-2">Output:</label>
                        <pre class="bg-dark-900 rounded-lg p-4 text-sm text-green-400 overflow-x-auto" x-text="codeOutput"></pre>
                    </div>
                </div>

                <!-- WHITEBOARD MODE -->
                <div x-show="responseMode === 'whiteboard'" x-transition>
                    <div class="mb-3 flex items-center justify-between">
                        <label class="text-dark-300 font-medium">System Design Whiteboard:</label>
                        <div class="flex items-center gap-2">
                            <button @click="whiteboardTool = 'pen'" 
                                    class="p-2 rounded transition-colors"
                                    :class="whiteboardTool === 'pen' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <button @click="whiteboardTool = 'eraser'" 
                                    class="p-2 rounded transition-colors"
                                    :class="whiteboardTool === 'eraser' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <select x-model="whiteboardColor" class="form-input py-1 px-2 text-sm w-auto">
                                <option value="#ffffff">White</option>
                                <option value="#ef4444">Red</option>
                                <option value="#22c55e">Green</option>
                                <option value="#3b82f6">Blue</option>
                                <option value="#f59e0b">Yellow</option>
                            </select>
                            <select x-model="whiteboardSize" class="form-input py-1 px-2 text-sm w-auto">
                                <option value="2">Thin</option>
                                <option value="4">Medium</option>
                                <option value="8">Thick</option>
                            </select>
                            <button @click="clearWhiteboard()" class="btn btn-outline btn-sm">Clear</button>
                        </div>
                    </div>
                    <div class="bg-dark-900 rounded-lg overflow-hidden">
                        <canvas 
                            x-ref="whiteboard"
                            class="whiteboard-canvas w-full"
                            :class="{ 'eraser': whiteboardTool === 'eraser' }"
                            width="800"
                            height="400"
                            @mousedown="startDrawing($event)"
                            @mousemove="draw($event)"
                            @mouseup="stopDrawing()"
                            @mouseleave="stopDrawing()"
                            @touchstart.prevent="startDrawing($event)"
                            @touchmove.prevent="draw($event)"
                            @touchend="stopDrawing()"
                        ></canvas>
                    </div>
                    <!-- Text Notes for Whiteboard -->
                    <div class="mt-4">
                        <label class="block text-dark-400 text-sm mb-2">Design Notes (describe your diagram):</label>
                        <textarea 
                            x-model="currentResponse"
                            class="form-input w-full h-24 resize-none"
                            placeholder="Explain your system design here..."
                        ></textarea>
                    </div>
                </div>

                <!-- Word Count & Actions -->
                <div class="flex items-center justify-between mt-4">
                    <div class="text-dark-500 text-sm">
                        <span x-text="currentResponse.split(/\s+/).filter(w => w).length"></span> words
                        <span x-show="responseMode === 'code'" class="ml-2">
                            | <span x-text="currentResponse.split('\\n').length"></span> lines
                        </span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="skipQuestion()" 
                                class="btn btn-outline"
                                :disabled="isSubmitting">
                            Skip
                        </button>
                        <button @click="submitResponse()" 
                                class="btn btn-primary"
                                :disabled="!currentResponse.trim() || isSubmitting">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting">
                                <span>Submit Answer</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Self-Video Preview (small corner) -->
            <div x-show="isRecording && enableRecording" 
                 class="fixed bottom-6 right-6 w-48 rounded-lg overflow-hidden shadow-2xl border-2 border-dark-700">
                <video x-ref="selfVideo" autoplay muted playsinline class="w-full h-36 object-cover bg-dark-900"></video>
                <div class="absolute top-2 left-2 flex items-center gap-1 bg-red-500 px-2 py-0.5 rounded text-xs text-white">
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    REC
                </div>
            </div>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- STATE: EVALUATING -->
    <!-- ============================================ -->
    <template x-if="state === 'evaluating'">
        <div class="card p-8 text-center">
            <div class="w-20 h-20 mx-auto bg-primary-600/20 rounded-full flex items-center justify-center mb-6 animate-pulse">
                <span class="text-4xl">🤔</span>
            </div>
            <h2 class="text-xl font-bold text-white mb-2">Evaluating Your Response...</h2>
            <p class="text-dark-400">Our AI is analyzing your answer for clarity, accuracy, and completeness.</p>
            <div class="mt-6 flex justify-center">
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- STATE: FEEDBACK (ENHANCED) -->
    <!-- ============================================ -->
    <template x-if="state === 'feedback'">
        <div class="space-y-6">
            <!-- Feedback Card -->
            <div class="card p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-teal-600/20 rounded-full flex items-center justify-center">
                        <span class="text-2xl">✨</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-teal-400 font-medium">Feedback</span>
                            <div class="flex items-center gap-2">
                                <span class="text-dark-400">Score:</span>
                                <div class="relative">
                                    <svg class="w-16 h-16 transform -rotate-90">
                                        <circle cx="32" cy="32" r="28" stroke-width="4" fill="none" class="stroke-dark-700"/>
                                        <circle cx="32" cy="32" r="28" stroke-width="4" fill="none" 
                                                class="transition-all duration-1000"
                                                :class="lastEvaluation?.score >= 80 ? 'stroke-green-500' : (lastEvaluation?.score >= 60 ? 'stroke-amber-500' : 'stroke-red-500')"
                                                :stroke-dasharray="175.93"
                                                :stroke-dashoffset="175.93 - (175.93 * (lastEvaluation?.score || 0) / 100)"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold"
                                          :class="lastEvaluation?.score >= 80 ? 'text-green-400' : (lastEvaluation?.score >= 60 ? 'text-amber-400' : 'text-red-400')"
                                          x-text="lastEvaluation?.score + '%'"></span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-dark-300 mb-4" x-text="lastEvaluation?.feedback"></p>

                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Strengths -->
                            <div class="bg-green-600/10 border border-green-500/30 rounded-lg p-4">
                                <h4 class="font-medium text-green-400 mb-2 flex items-center gap-2">
                                    <span>✓</span> Strengths
                                </h4>
                                <ul class="text-dark-300 text-sm space-y-1">
                                    <template x-for="strength in lastEvaluation?.strengths" :key="strength">
                                        <li x-text="'• ' + strength"></li>
                                    </template>
                                </ul>
                            </div>
                            <!-- Improvements -->
                            <div class="bg-amber-600/10 border border-amber-500/30 rounded-lg p-4">
                                <h4 class="font-medium text-amber-400 mb-2 flex items-center gap-2">
                                    <span>↑</span> Areas for Improvement
                                </h4>
                                <ul class="text-dark-300 text-sm space-y-1">
                                    <template x-for="improvement in lastEvaluation?.improvements" :key="improvement">
                                        <li x-text="'• ' + improvement"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Follow-up Question -->
                        <template x-if="lastEvaluation?.follow_up_question">
                            <div class="mt-4 p-4 bg-blue-600/10 border border-blue-500/30 rounded-lg">
                                <h4 class="font-medium text-blue-400 mb-2 flex items-center gap-2">
                                    <span>💭</span> Follow-up Question
                                </h4>
                                <p class="text-dark-300 text-sm" x-text="lastEvaluation.follow_up_question"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Continue Button -->
            <div class="flex justify-center gap-4">
                <!-- Retry Button -->
                <button x-show="retriesRemaining > 0 && lastEvaluation?.score < 70"
                        @click="retryQuestion()" 
                        class="btn btn-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Retry (<span x-text="retriesRemaining"></span> left)
                </button>
                
                <button @click="nextQuestion()" class="btn btn-primary btn-lg">
                    <template x-if="currentQuestion < totalQuestions">
                        <span>Next Question →</span>
                    </template>
                    <template x-if="currentQuestion >= totalQuestions">
                        <span>View Results</span>
                    </template>
                </button>
            </div>
        </div>
    </template>

    <!-- ============================================ -->
    <!-- STATE: COMPLETE (ENHANCED) -->
    <!-- ============================================ -->
    <template x-if="state === 'complete'">
        <div class="card p-8">
            <div class="text-center mb-8">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-500/20 to-teal-500/20 rounded-full flex items-center justify-center text-5xl mb-6">
                    🎉
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Interview Complete!</h2>
                <p class="text-dark-400">Here's a summary of your performance.</p>
            </div>

            <!-- Overall Score with Animation -->
            <div class="bg-dark-800 rounded-xl p-8 mb-8 text-center">
                <p class="text-dark-400 mb-4">Overall Score</p>
                <div class="relative w-40 h-40 mx-auto mb-4">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="80" cy="80" r="70" stroke-width="8" fill="none" class="stroke-dark-700"/>
                        <circle cx="80" cy="80" r="70" stroke-width="8" fill="none" 
                                class="transition-all duration-2000"
                                :class="overallScore >= 80 ? 'stroke-green-500' : (overallScore >= 60 ? 'stroke-amber-500' : 'stroke-red-500')"
                                stroke-linecap="round"
                                :stroke-dasharray="439.82"
                                :stroke-dashoffset="439.82 - (439.82 * overallScore / 100)"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-5xl font-bold"
                              :class="overallScore >= 80 ? 'text-green-400' : (overallScore >= 60 ? 'text-amber-400' : 'text-red-400')"
                              x-text="overallScore + '%'"></span>
                        <span class="text-dark-400 text-sm" x-text="scoreGrade"></span>
                    </div>
                </div>
            </div>

            <!-- Score Breakdown by Question -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-white mb-4">Question Breakdown</h3>
                <div class="grid sm:grid-cols-3 md:grid-cols-5 gap-4">
                    <template x-for="(score, index) in questionScores" :key="index">
                        <div class="bg-dark-800 rounded-lg p-4 text-center">
                            <p class="text-dark-500 text-sm mb-1" x-text="'Q' + (index + 1)"></p>
                            <p class="text-2xl font-bold"
                               :class="score >= 80 ? 'text-green-400' : (score >= 60 ? 'text-amber-400' : (score === 0 ? 'text-dark-500' : 'text-red-400'))"
                               x-text="score === 0 ? 'Skip' : score + '%'"></p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-dark-800 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-white mb-4">Performance Summary</h3>
                <p class="text-dark-300" x-text="performanceSummary"></p>
            </div>

            <!-- Recording Saved -->
            <div x-show="recordingBlob" class="bg-green-600/10 border border-green-500/30 rounded-lg p-4 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🎬</span>
                        <div>
                            <p class="text-green-400 font-medium">Recording Saved</p>
                            <p class="text-dark-400 text-sm">Your interview recording is available for review</p>
                        </div>
                    </div>
                    <button @click="downloadRecording()" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('interviews.show', $interview) }}" class="btn btn-outline">
                    View Full Report
                </a>
                <a href="{{ route('interviews.index') }}" class="btn btn-primary">
                    Back to Interviews
                </a>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function enhancedAIInterview() {
    return {
        // States: preparation, question, evaluating, feedback, complete
        state: 'preparation',
        currentQuestion: 0,
        totalQuestions: 5,
        timeLimit: {{ $interview->type->value === 'behavioral' ? 180 : ($interview->type->value === 'system_design' ? 1800 : 900) }},
        timeRemaining: 300,
        timerInterval: null,
        
        // Preparation checklist
        checklist: {
            quietEnvironment: false,
            stableInternet: false,
            microphoneReady: false,
            cameraReady: false,
            readInstructions: false
        },
        showCameraPreview: false,
        micLevel: 0,
        micStream: null,
        
        // Recording
        enableRecording: false,
        isRecording: false,
        mediaRecorder: null,
        recordedChunks: [],
        recordingBlob: null,
        
        // Question data
        questions: [],
        currentQuestionData: null,
        currentResponse: '',
        showHints: false,
        isSubmitting: false,
        
        // Response modes: text, voice, code, whiteboard
        responseMode: 'text',
        
        // Voice recording
        isVoiceRecording: false,
        speechRecognition: null,
        
        // Code editor
        codeLanguage: 'javascript',
        codeOutput: '',
        
        // Whiteboard
        whiteboardTool: 'pen',
        whiteboardColor: '#ffffff',
        whiteboardSize: 4,
        isDrawing: false,
        whiteboardContext: null,
        lastX: 0,
        lastY: 0,
        
        // Results
        evaluations: [],
        lastEvaluation: null,
        questionScores: [],
        overallScore: 0,
        scoreGrade: '',
        performanceSummary: '',
        
        // Retry system
        retriesRemaining: 1,
        
        // Interview metadata
        interviewId: '{{ $interview->id }}',
        interviewType: '{{ $interview->type->value }}',
        
        get isPreparationComplete() {
            return Object.values(this.checklist).every(v => v);
        },
        
        async init() {
            await this.loadQuestions();
            this.initSpeechRecognition();
        },
        
        // ==========================================
        // PREPARATION METHODS
        // ==========================================
        
        async testMicrophone() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.micStream = stream;
                
                // Analyze audio levels
                const audioContext = new AudioContext();
                const analyser = audioContext.createAnalyser();
                const microphone = audioContext.createMediaStreamSource(stream);
                microphone.connect(analyser);
                analyser.fftSize = 256;
                
                const dataArray = new Uint8Array(analyser.frequencyBinCount);
                
                const checkLevel = () => {
                    if (!this.checklist.microphoneReady) {
                        analyser.getByteFrequencyData(dataArray);
                        const average = dataArray.reduce((a, b) => a + b) / dataArray.length;
                        this.micLevel = Math.min(100, average * 2);
                        
                        if (this.micLevel > 10) {
                            this.checklist.microphoneReady = true;
                            stream.getTracks().forEach(track => track.stop());
                        } else {
                            requestAnimationFrame(checkLevel);
                        }
                    }
                };
                checkLevel();
                
                // Auto-pass after 3 seconds
                setTimeout(() => {
                    if (!this.checklist.microphoneReady) {
                        this.checklist.microphoneReady = true;
                        stream.getTracks().forEach(track => track.stop());
                    }
                }, 3000);
                
            } catch (error) {
                console.error('Microphone access denied:', error);
                // Allow to continue even without mic
                this.checklist.microphoneReady = true;
            }
        },
        
        async testCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                this.$refs.cameraPreview.srcObject = stream;
                this.showCameraPreview = true;
                this.checklist.cameraReady = true;
            } catch (error) {
                console.error('Camera access denied:', error);
                this.checklist.cameraReady = true;
            }
        },
        
        stopCameraPreview() {
            if (this.$refs.cameraPreview?.srcObject) {
                this.$refs.cameraPreview.srcObject.getTracks().forEach(track => track.stop());
            }
            this.showCameraPreview = false;
        },
        
        // ==========================================
        // INTERVIEW FLOW
        // ==========================================
        
        async loadQuestions() {
            try {
                const response = await fetch(`{{ route('interviews.questions', $interview) }}`);
                const data = await response.json();
                this.questions = data.questions || [];
                this.totalQuestions = this.questions.length || 5;
            } catch (error) {
                console.error('Failed to load questions:', error);
                this.questions = this.getFallbackQuestions();
                this.totalQuestions = this.questions.length;
            }
        },
        
        getFallbackQuestions() {
            const type = this.interviewType;
            if (type === 'behavioral') {
                return [
                    { question: 'Tell me about yourself and your experience in this field.', hints: ['Focus on relevant experience', 'Keep it under 2 minutes'] },
                    { question: 'Describe a challenging project you worked on. What was your role and what did you learn?', hints: ['Use the STAR method', 'Be specific about your contributions'] },
                    { question: 'Tell me about a time you had to work with a difficult team member.', hints: ['Focus on resolution', 'Show empathy'] },
                    { question: 'What is your greatest professional achievement?', hints: ['Quantify results if possible'] },
                    { question: 'Where do you see yourself in 5 years?', hints: ['Align with career growth'] },
                ];
            } else if (type === 'technical_coding') {
                return [
                    { question: 'Explain the difference between let, const, and var in JavaScript.', type: 'conceptual', hints: ['Consider scope', 'Consider hoisting'] },
                    { question: 'Write a function to reverse a string without using built-in methods.', type: 'coding', hints: ['Use a loop', 'Consider edge cases'] },
                    { question: 'What is Big O notation and why is it important?', type: 'conceptual', hints: ['Time vs space complexity'] },
                    { question: 'Implement a function to check if a string is a palindrome.', type: 'coding', hints: ['Two pointer approach'] },
                    { question: 'Explain REST API principles and HTTP methods.', type: 'conceptual', hints: ['GET, POST, PUT, DELETE'] },
                ];
            } else if (type === 'system_design') {
                return [
                    { question: 'Design a URL shortening service like bit.ly.', type: 'design', hints: ['Consider scale', 'Database design', 'Caching'] },
                    { question: 'How would you design a real-time chat application?', type: 'design', hints: ['WebSockets', 'Message queue', 'Storage'] },
                    { question: 'Design a notification system for a social media platform.', type: 'design', hints: ['Push vs Pull', 'Rate limiting'] },
                ];
            }
            return [
                { question: 'Tell me about yourself.', hints: [] },
                { question: 'Why are you interested in this field?', hints: [] },
                { question: 'What are your strengths?', hints: [] },
                { question: 'What are your weaknesses?', hints: [] },
                { question: 'Do you have any questions for us?', hints: [] },
            ];
        },
        
        async startInterview() {
            // Start recording if enabled
            if (this.enableRecording) {
                await this.startRecording();
            }
            
            this.currentQuestion = 1;
            this.currentQuestionData = this.questions[0];
            this.responseMode = this.getDefaultResponseMode();
            this.state = 'question';
            this.startTimer();
            
            // Initialize whiteboard if needed
            if (this.interviewType === 'system_design') {
                this.$nextTick(() => this.initWhiteboard());
            }
        },
        
        getDefaultResponseMode() {
            if (this.interviewType === 'technical_coding') return 'code';
            if (this.interviewType === 'system_design') return 'whiteboard';
            return 'text';
        },
        
        startTimer() {
            this.timeRemaining = this.timeLimit;
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            this.timerInterval = setInterval(() => {
                this.timeRemaining--;
                if (this.timeRemaining <= 0) {
                    this.handleTimeout();
                }
            }, 1000);
        },
        
        handleTimeout() {
            clearInterval(this.timerInterval);
            if (this.currentResponse.trim()) {
                this.submitResponse();
            } else {
                this.skipQuestion();
            }
        },
        
        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
        
        // ==========================================
        // VOICE RECOGNITION
        // ==========================================
        
        initSpeechRecognition() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.speechRecognition = new SpeechRecognition();
                this.speechRecognition.continuous = true;
                this.speechRecognition.interimResults = true;
                this.speechRecognition.lang = 'en-US';
                
                this.speechRecognition.onresult = (event) => {
                    let transcript = '';
                    for (let i = 0; i < event.results.length; i++) {
                        transcript += event.results[i][0].transcript;
                    }
                    this.currentResponse = transcript;
                };
                
                this.speechRecognition.onerror = (event) => {
                    console.error('Speech recognition error:', event.error);
                    if (event.error === 'not-allowed') {
                        this.responseMode = 'text';
                    }
                };
            }
        },
        
        initVoiceRecording() {
            // Voice mode initialized
        },
        
        toggleVoiceRecording() {
            if (this.isVoiceRecording) {
                this.stopVoiceRecording();
            } else {
                this.startVoiceRecording();
            }
        },
        
        startVoiceRecording() {
            if (this.speechRecognition) {
                this.speechRecognition.start();
                this.isVoiceRecording = true;
            } else {
                alert('Speech recognition is not supported in your browser. Please use Chrome or Edge.');
                this.responseMode = 'text';
            }
        },
        
        stopVoiceRecording() {
            if (this.speechRecognition) {
                this.speechRecognition.stop();
                this.isVoiceRecording = false;
            }
        },
        
        // ==========================================
        // CODE EDITOR
        // ==========================================
        
        insertTab(event) {
            const textarea = event.target;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            this.currentResponse = this.currentResponse.substring(0, start) + '    ' + this.currentResponse.substring(end);
            this.$nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + 4;
            });
        },
        
        async runCode() {
            this.codeOutput = 'Running...';
            
            // Simple JS execution for demo
            if (this.codeLanguage === 'javascript') {
                try {
                    // Create a safe eval environment
                    const logs = [];
                    const mockConsole = { log: (...args) => logs.push(args.map(a => JSON.stringify(a)).join(' ')) };
                    const fn = new Function('console', this.currentResponse);
                    fn(mockConsole);
                    this.codeOutput = logs.join('\n') || 'Code executed successfully (no output)';
                } catch (error) {
                    this.codeOutput = 'Error: ' + error.message;
                }
            } else {
                this.codeOutput = `[${this.codeLanguage}] Code would be executed on server.\nFor demo, only JavaScript runs locally.`;
            }
        },
        
        formatCode() {
            // Simple formatting - just normalize indentation
            const lines = this.currentResponse.split('\n');
            let indentLevel = 0;
            const formatted = lines.map(line => {
                line = line.trim();
                if (line.match(/^[}\])]/) && indentLevel > 0) indentLevel--;
                const indented = '    '.repeat(indentLevel) + line;
                if (line.match(/[{\[(]$/)) indentLevel++;
                return indented;
            });
            this.currentResponse = formatted.join('\n');
        },
        
        // ==========================================
        // WHITEBOARD
        // ==========================================
        
        initWhiteboard() {
            const canvas = this.$refs.whiteboard;
            if (!canvas) return;
            
            this.whiteboardContext = canvas.getContext('2d');
            this.whiteboardContext.fillStyle = '#1a1a2e';
            this.whiteboardContext.fillRect(0, 0, canvas.width, canvas.height);
        },
        
        getCanvasCoords(event) {
            const canvas = this.$refs.whiteboard;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            
            if (event.touches) {
                return {
                    x: (event.touches[0].clientX - rect.left) * scaleX,
                    y: (event.touches[0].clientY - rect.top) * scaleY
                };
            }
            return {
                x: (event.clientX - rect.left) * scaleX,
                y: (event.clientY - rect.top) * scaleY
            };
        },
        
        startDrawing(event) {
            this.isDrawing = true;
            const coords = this.getCanvasCoords(event);
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        draw(event) {
            if (!this.isDrawing || !this.whiteboardContext) return;
            
            const coords = this.getCanvasCoords(event);
            const ctx = this.whiteboardContext;
            
            ctx.beginPath();
            ctx.moveTo(this.lastX, this.lastY);
            ctx.lineTo(coords.x, coords.y);
            ctx.strokeStyle = this.whiteboardTool === 'eraser' ? '#1a1a2e' : this.whiteboardColor;
            ctx.lineWidth = this.whiteboardTool === 'eraser' ? 20 : parseInt(this.whiteboardSize);
            ctx.lineCap = 'round';
            ctx.stroke();
            
            this.lastX = coords.x;
            this.lastY = coords.y;
        },
        
        stopDrawing() {
            this.isDrawing = false;
        },
        
        clearWhiteboard() {
            if (this.whiteboardContext && this.$refs.whiteboard) {
                this.whiteboardContext.fillStyle = '#1a1a2e';
                this.whiteboardContext.fillRect(0, 0, this.$refs.whiteboard.width, this.$refs.whiteboard.height);
            }
        },
        
        // ==========================================
        // RECORDING
        // ==========================================
        
        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                
                // Show self-video
                if (this.$refs.selfVideo) {
                    this.$refs.selfVideo.srcObject = stream;
                }
                
                this.mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
                this.recordedChunks = [];
                
                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        this.recordedChunks.push(event.data);
                    }
                };
                
                this.mediaRecorder.onstop = () => {
                    this.recordingBlob = new Blob(this.recordedChunks, { type: 'video/webm' });
                    stream.getTracks().forEach(track => track.stop());
                };
                
                this.mediaRecorder.start(1000);
                this.isRecording = true;
            } catch (error) {
                console.error('Recording failed:', error);
                this.enableRecording = false;
            }
        },
        
        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
                this.isRecording = false;
            }
        },
        
        downloadRecording() {
            if (this.recordingBlob) {
                const url = URL.createObjectURL(this.recordingBlob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `interview-${this.interviewId}-${Date.now()}.webm`;
                a.click();
                URL.revokeObjectURL(url);
            }
        },
        
        // ==========================================
        // SUBMISSION & EVALUATION
        // ==========================================
        
        async submitResponse() {
            if (this.isSubmitting || !this.currentResponse.trim()) return;
            
            // Stop voice recording if active
            if (this.isVoiceRecording) {
                this.stopVoiceRecording();
            }
            
            this.isSubmitting = true;
            clearInterval(this.timerInterval);
            this.state = 'evaluating';
            
            // Add whiteboard image if in whiteboard mode
            let responseData = {
                question_index: this.currentQuestion - 1,
                question: this.currentQuestionData.question,
                response: this.currentResponse,
                response_mode: this.responseMode,
            };
            
            if (this.responseMode === 'whiteboard' && this.$refs.whiteboard) {
                responseData.whiteboard_image = this.$refs.whiteboard.toDataURL('image/png');
            }
            
            if (this.responseMode === 'code') {
                responseData.code_language = this.codeLanguage;
            }
            
            try {
                const response = await fetch(`{{ route('interviews.evaluate', $interview) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(responseData),
                });
                
                const data = await response.json();
                this.lastEvaluation = data;
                this.evaluations.push(data);
                this.questionScores.push(data.score || 0);
                
            } catch (error) {
                console.error('Evaluation failed:', error);
                this.lastEvaluation = {
                    score: 70,
                    feedback: 'Your response has been recorded. Detailed feedback will be provided.',
                    strengths: ['Response provided'],
                    improvements: ['Await detailed review'],
                };
                this.evaluations.push(this.lastEvaluation);
                this.questionScores.push(70);
            }
            
            this.isSubmitting = false;
            this.state = 'feedback';
        },
        
        skipQuestion() {
            clearInterval(this.timerInterval);
            if (this.isVoiceRecording) this.stopVoiceRecording();
            
            this.evaluations.push({ score: 0, skipped: true });
            this.questionScores.push(0);
            this.lastEvaluation = {
                score: 0,
                feedback: 'Question skipped.',
                strengths: [],
                improvements: ['Try to attempt all questions in future interviews'],
            };
            this.state = 'feedback';
        },
        
        retryQuestion() {
            if (this.retriesRemaining > 0) {
                this.retriesRemaining--;
                // Remove last evaluation
                this.evaluations.pop();
                this.questionScores.pop();
                // Reset response
                this.currentResponse = '';
                this.state = 'question';
                this.startTimer();
            }
        },
        
        nextQuestion() {
            this.retriesRemaining = 1; // Reset retries for next question
            
            if (this.currentQuestion >= this.totalQuestions) {
                this.finishInterview();
                return;
            }
            
            this.currentQuestion++;
            this.currentQuestionData = this.questions[this.currentQuestion - 1];
            this.currentResponse = '';
            this.showHints = false;
            this.codeOutput = '';
            this.state = 'question';
            this.startTimer();
            
            // Reinit whiteboard if needed
            if (this.responseMode === 'whiteboard') {
                this.$nextTick(() => {
                    this.initWhiteboard();
                });
            }
        },
        
        async finishInterview() {
            // Stop recording
            if (this.isRecording) {
                this.stopRecording();
            }
            
            // Calculate overall score
            const validScores = this.questionScores.filter(s => s > 0);
            this.overallScore = validScores.length > 0 
                ? Math.round(validScores.reduce((a, b) => a + b, 0) / validScores.length)
                : 0;
            
            // Determine grade
            if (this.overallScore >= 90) {
                this.scoreGrade = 'Excellent Performance! 🌟';
            } else if (this.overallScore >= 80) {
                this.scoreGrade = 'Great Job! 🎉';
            } else if (this.overallScore >= 70) {
                this.scoreGrade = 'Good Effort! 👍';
            } else if (this.overallScore >= 60) {
                this.scoreGrade = 'Satisfactory 📚';
            } else {
                this.scoreGrade = 'Needs Improvement 💪';
            }
            
            // Generate summary
            const attempted = this.evaluations.filter(e => !e.skipped).length;
            const skipped = this.evaluations.filter(e => e.skipped).length;
            this.performanceSummary = `You completed ${attempted} out of ${this.totalQuestions} questions` +
                (skipped > 0 ? ` (${skipped} skipped)` : '') +
                ` with an average score of ${this.overallScore}%. ` +
                `Focus on the improvement areas identified to boost your performance in future interviews.`;
            
            // Save results to server
            try {
                await fetch(`{{ route('interviews.complete', $interview) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        score: this.overallScore,
                        feedback: this.performanceSummary,
                        evaluations: this.evaluations,
                        is_practice: {{ ($interview->is_practice ?? false) ? 'true' : 'false' }},
                    }),
                });
            } catch (error) {
                console.error('Failed to save results:', error);
            }
            
            this.state = 'complete';
        },
        
        destroy() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            if (this.speechRecognition) this.speechRecognition.stop();
            if (this.isRecording) this.stopRecording();
            if (this.micStream) this.micStream.getTracks().forEach(t => t.stop());
        }
    }
}
</script>
@endpush
@endsection
