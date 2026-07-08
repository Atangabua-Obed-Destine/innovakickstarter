@extends('layouts.app')

@section('title', 'AI Interview - ' . $interview->type->label())

@section('content')
<div x-data="aiInterview()" class="max-w-5xl mx-auto" x-cloak>
    <!-- Interview Header -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="text-2xl">🤖</span>
                    AI {{ $interview->type->label() }} Interview
                </h1>
                <p class="text-dark-400 mt-1">Track: {{ $interview->track->name }}</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Timer -->
                <div class="text-center">
                    <div class="text-2xl font-mono font-bold" 
                         :class="timeRemaining < 60 ? 'text-red-400' : 'text-primary-400'"
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
                 :style="'width: ' + (currentQuestion / totalQuestions * 100) + '%'"></div>
        </div>
    </div>

    <!-- Interview States -->
    <!-- State: Ready to Start -->
    <template x-if="state === 'ready'">
        <div class="card p-8 text-center">
            <div class="w-20 h-20 mx-auto bg-primary-600/20 rounded-full flex items-center justify-center text-4xl mb-6">
                🎯
            </div>
            <h2 class="text-2xl font-bold text-white mb-4">Ready to Begin?</h2>
            <p class="text-dark-300 mb-6 max-w-lg mx-auto">
                This interview will consist of <strong x-text="totalQuestions"></strong> questions.
                You'll have approximately <strong x-text="Math.ceil(timeLimit / 60)"></strong> minutes per question.
                Take your time to think through your answers.
            </p>
            <div class="bg-dark-800 rounded-lg p-4 mb-6 text-left max-w-md mx-auto">
                <h3 class="font-medium text-white mb-2">Tips for Success:</h3>
                <ul class="text-dark-400 text-sm space-y-1">
                    <li>• Think out loud - explain your reasoning</li>
                    <li>• Ask clarifying questions if needed</li>
                    <li>• Use specific examples from your experience</li>
                    <li>• Structure your answers clearly</li>
                </ul>
            </div>
            <button @click="startInterview()" class="btn btn-primary btn-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Start Interview
            </button>
        </div>
    </template>

    <!-- State: Question Active -->
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

            <!-- Response Area -->
            <div class="card p-6">
                <label class="block text-dark-300 font-medium mb-2">Your Response:</label>
                <textarea 
                    x-model="currentResponse"
                    class="form-input w-full h-48 resize-none"
                    placeholder="Type your answer here... Think through your response carefully."
                    :disabled="isSubmitting"
                ></textarea>
                
                <div class="flex items-center justify-between mt-4">
                    <div class="text-dark-500 text-sm">
                        <span x-text="currentResponse.split(/\s+/).filter(w => w).length"></span> words
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
        </div>
    </template>

    <!-- State: Evaluating -->
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

    <!-- State: Feedback -->
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
                                <span class="text-2xl font-bold"
                                      :class="lastEvaluation?.score >= 80 ? 'text-green-400' : (lastEvaluation?.score >= 60 ? 'text-amber-400' : 'text-red-400')"
                                      x-text="lastEvaluation?.score + '%'"></span>
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
                    </div>
                </div>
            </div>

            <!-- Continue Button -->
            <div class="flex justify-center">
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

    <!-- State: Complete -->
    <template x-if="state === 'complete'">
        <div class="card p-8">
            <div class="text-center mb-8">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-500/20 to-teal-500/20 rounded-full flex items-center justify-center text-5xl mb-6">
                    🎉
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Interview Complete!</h2>
                <p class="text-dark-400">Here's a summary of your performance.</p>
            </div>

            <!-- Overall Score -->
            <div class="bg-dark-800 rounded-xl p-6 mb-8 text-center">
                <p class="text-dark-400 mb-2">Overall Score</p>
                <div class="text-6xl font-bold mb-2"
                     :class="overallScore >= 80 ? 'text-green-400' : (overallScore >= 60 ? 'text-amber-400' : 'text-red-400')"
                     x-text="overallScore + '%'"></div>
                <p class="text-xl text-dark-300" x-text="scoreGrade"></p>
            </div>

            <!-- Score Breakdown -->
            <div class="grid sm:grid-cols-3 gap-4 mb-8">
                <template x-for="(score, index) in questionScores" :key="index">
                    <div class="bg-dark-800 rounded-lg p-4 text-center">
                        <p class="text-dark-500 text-sm mb-1" x-text="'Question ' + (index + 1)"></p>
                        <p class="text-2xl font-bold"
                           :class="score >= 80 ? 'text-green-400' : (score >= 60 ? 'text-amber-400' : 'text-red-400')"
                           x-text="score + '%'"></p>
                    </div>
                </template>
            </div>

            <!-- Summary -->
            <div class="bg-dark-800 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-white mb-4">Performance Summary</h3>
                <p class="text-dark-300" x-text="performanceSummary"></p>
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
function aiInterview() {
    return {
        // State
        state: 'ready',
        currentQuestion: 0,
        totalQuestions: 5,
        timeLimit: 300, // 5 minutes per question
        timeRemaining: 300,
        timerInterval: null,
        
        // Question data
        questions: [],
        currentQuestionData: null,
        currentResponse: '',
        showHints: false,
        isSubmitting: false,
        
        // Results
        evaluations: [],
        lastEvaluation: null,
        questionScores: [],
        overallScore: 0,
        scoreGrade: '',
        performanceSummary: '',
        
        // Interview metadata
        interviewId: {{ $interview->id }},
        
        init() {
            // Load questions on init
            this.loadQuestions();
        },
        
        async loadQuestions() {
            try {
                const response = await fetch(`{{ route('interviews.questions', $interview) }}`);
                const data = await response.json();
                this.questions = data.questions || [];
                this.totalQuestions = this.questions.length;
            } catch (error) {
                console.error('Failed to load questions:', error);
                // Use fallback questions
                this.questions = [
                    { question: 'Tell me about yourself and your experience in this field.', hints: [] },
                    { question: 'Describe a challenging project you worked on.', hints: ['Use the STAR method'] },
                    { question: 'How do you approach problem-solving?', hints: [] },
                    { question: 'What are your technical strengths?', hints: [] },
                    { question: 'Where do you see yourself in 5 years?', hints: [] },
                ];
                this.totalQuestions = this.questions.length;
            }
        },
        
        startInterview() {
            this.currentQuestion = 1;
            this.currentQuestionData = this.questions[0];
            this.state = 'question';
            this.startTimer();
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
        
        async submitResponse() {
            if (this.isSubmitting || !this.currentResponse.trim()) return;
            
            this.isSubmitting = true;
            clearInterval(this.timerInterval);
            this.state = 'evaluating';
            
            try {
                const response = await fetch(`{{ route('interviews.evaluate', $interview) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        question_index: this.currentQuestion - 1,
                        question: this.currentQuestionData.question,
                        response: this.currentResponse,
                    }),
                });
                
                const data = await response.json();
                this.lastEvaluation = data;
                this.evaluations.push(data);
                this.questionScores.push(data.score || 0);
                
            } catch (error) {
                console.error('Evaluation failed:', error);
                // Fallback evaluation
                this.lastEvaluation = {
                    score: 70,
                    feedback: 'Your response has been recorded. Detailed feedback will be provided by a mentor.',
                    strengths: ['Response provided'],
                    improvements: ['Await mentor review'],
                };
                this.evaluations.push(this.lastEvaluation);
                this.questionScores.push(70);
            }
            
            this.isSubmitting = false;
            this.state = 'feedback';
        },
        
        skipQuestion() {
            clearInterval(this.timerInterval);
            this.evaluations.push({ score: 0, skipped: true });
            this.questionScores.push(0);
            this.lastEvaluation = {
                score: 0,
                feedback: 'Question skipped.',
                strengths: [],
                improvements: ['Try to attempt all questions'],
            };
            this.state = 'feedback';
        },
        
        nextQuestion() {
            if (this.currentQuestion >= this.totalQuestions) {
                this.finishInterview();
                return;
            }
            
            this.currentQuestion++;
            this.currentQuestionData = this.questions[this.currentQuestion - 1];
            this.currentResponse = '';
            this.showHints = false;
            this.state = 'question';
            this.startTimer();
        },
        
        async finishInterview() {
            // Calculate overall score
            const validScores = this.questionScores.filter(s => s > 0);
            this.overallScore = validScores.length > 0 
                ? Math.round(validScores.reduce((a, b) => a + b, 0) / validScores.length)
                : 0;
            
            // Determine grade
            if (this.overallScore >= 90) {
                this.scoreGrade = 'Excellent Performance!';
            } else if (this.overallScore >= 80) {
                this.scoreGrade = 'Great Job!';
            } else if (this.overallScore >= 70) {
                this.scoreGrade = 'Good Effort!';
            } else if (this.overallScore >= 60) {
                this.scoreGrade = 'Satisfactory';
            } else {
                this.scoreGrade = 'Needs Improvement';
            }
            
            // Generate summary
            const attempted = this.evaluations.filter(e => !e.skipped).length;
            this.performanceSummary = `You completed ${attempted} out of ${this.totalQuestions} questions with an average score of ${this.overallScore}%. ` +
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
                    }),
                });
            } catch (error) {
                console.error('Failed to save results:', error);
            }
            
            this.state = 'complete';
        },
        
        destroy() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
        }
    }
}
</script>
@endpush
@endsection
