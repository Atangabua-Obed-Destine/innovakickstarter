<?php

namespace App\Services;

use App\Enums\InterviewType;
use App\Models\InterviewSession;
use App\Models\Track;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Live AI Interview Service
 * 
 * Provides real-time conversational AI interview experience with:
 * - Dynamic conversation flow with memory
 * - Context-aware follow-up questions
 * - Real-time evaluation and coaching
 * - Natural interviewer personality
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class LiveAIInterviewService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $model;

    // Conversation state keys
    protected const CACHE_PREFIX = 'live_interview_';
    protected const CACHE_TTL = 7200; // 2 hours

    // Interview configuration
    protected const MAX_QUESTIONS = 6;
    protected const MAX_FOLLOW_UPS_PER_QUESTION = 2;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?? '';
        $this->model = config('services.openai.model') ?? 'gpt-4o';
        
        // Debug logging
        Log::debug('LiveAIInterviewService initialized', [
            'model' => $this->model,
            'has_key' => !empty($this->apiKey),
        ]);
    }

    /**
     * Check if service is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Start a new conversation and return the opening message.
     */
    public function startConversation(
        InterviewSession $interview,
        Track $track,
        InterviewType $type,
        string $difficulty
    ): array {
        // Initialize conversation state
        $state = [
            'interview_id' => $interview->id,
            'track' => $track->name,
            'type' => $type->value,
            'difficulty' => $difficulty,
            'current_question' => 0,
            'questions_asked' => 0,
            'follow_ups_for_current' => 0,
            'messages' => [],
            'evaluations' => [],
            'hints_used' => 0,
            'started_at' => now()->toISOString(),
        ];

        // Generate opening message based on interview type
        $opening = $this->generateOpening($type, $track->name, $difficulty);

        // Add to conversation
        $state['messages'][] = [
            'role' => 'interviewer',
            'content' => $opening['message'],
            'type' => 'opening',
            'timestamp' => now()->toISOString(),
        ];

        // Save state
        $this->saveState($interview->id, $state);

        // Update interview with initial question
        $interview->update([
            'transcript' => $state['messages'],
        ]);

        return [
            'message' => $opening['message'],
            'interviewer_name' => $opening['interviewer_name'],
            'type' => 'opening',
            'next_action' => 'wait_for_response',
        ];
    }

    /**
     * Process a user message and generate AI response.
     */
    public function processMessage(
        InterviewSession $interview,
        string $userMessage,
        string $inputMode = 'text'
    ): array {
        Log::debug('processMessage called', [
            'interview_id' => $interview->id,
            'message_length' => strlen($userMessage),
        ]);

        $state = $this->getState($interview->id);
        
        if (!$state) {
            Log::debug('No state found, restarting conversation');
            // Restart conversation if state lost
            return $this->startConversation(
                $interview,
                $interview->track,
                $interview->type,
                $interview->difficulty ?? 'intermediate'
            );
        }

        Log::debug('State found', ['questions_asked' => $state['questions_asked']]);

        // Add user message to conversation
        $state['messages'][] = [
            'role' => 'candidate',
            'content' => $userMessage,
            'input_mode' => $inputMode,
            'timestamp' => now()->toISOString(),
        ];

        // Determine what type of response to generate
        $responseType = $this->determineResponseType($state, $userMessage);
        Log::debug('Response type determined', ['type' => $responseType]);

        // Generate AI response
        $aiResponse = $this->generateResponse($state, $userMessage, $responseType, $interview);
        Log::debug('AI response generated', ['has_message' => isset($aiResponse['message'])]);

        // Add AI response to conversation
        $state['messages'][] = [
            'role' => 'interviewer',
            'content' => $aiResponse['message'],
            'type' => $responseType,
            'evaluation' => $aiResponse['evaluation'] ?? null,
            'timestamp' => now()->toISOString(),
        ];

        // Update state based on response type
        $this->updateStateAfterResponse($state, $responseType, $aiResponse);

        // Save updated state
        $this->saveState($interview->id, $state);

        // Update interview transcript
        $interview->update([
            'transcript' => $state['messages'],
            'responses' => $state['evaluations'],
        ]);

        Log::debug('processMessage complete');

        return [
            'message' => $aiResponse['message'],
            'type' => $responseType,
            'evaluation' => $aiResponse['evaluation'] ?? null,
            'progress' => $this->calculateProgress($state),
            'is_complete' => $state['questions_asked'] >= self::MAX_QUESTIONS,
            'coaching_tip' => $aiResponse['coaching_tip'] ?? null,
        ];
    }

    /**
     * Generate a hint for the current question.
     */
    public function generateHint(InterviewSession $interview): string
    {
        $state = $this->getState($interview->id);
        
        if (!$state) {
            return "Take a moment to think about a specific example from your experience.";
        }

        $state['hints_used']++;
        $this->saveState($interview->id, $state);

        // Get the last interviewer question
        $lastQuestion = collect($state['messages'])
            ->where('role', 'interviewer')
            ->last();

        if (!$this->isConfigured()) {
            return $this->getFallbackHint($interview->type);
        }

        try {
            $prompt = "The candidate is struggling with this interview question: \"{$lastQuestion['content']}\"\n\n" .
                "Provide a brief, helpful hint (1-2 sentences) that guides them without giving away the answer. " .
                "Be encouraging and supportive.";

            $response = $this->callOpenAI($prompt, false);
            return $response['content'] ?? $this->getFallbackHint($interview->type);
        } catch (\Exception $e) {
            return $this->getFallbackHint($interview->type);
        }
    }

    /**
     * Skip to the next question.
     */
    public function skipToNextQuestion(InterviewSession $interview): array
    {
        $state = $this->getState($interview->id);
        
        if (!$state) {
            return ['error' => 'Interview state not found'];
        }

        // Record that question was skipped
        $state['evaluations'][] = [
            'question_index' => $state['current_question'],
            'skipped' => true,
            'score' => 0,
        ];

        $state['current_question']++;
        $state['questions_asked']++;
        $state['follow_ups_for_current'] = 0;

        // Generate next question
        $nextQuestion = $this->generateNextQuestion($state, $interview);

        $state['messages'][] = [
            'role' => 'interviewer',
            'content' => $nextQuestion,
            'type' => 'new_question',
            'timestamp' => now()->toISOString(),
        ];

        $this->saveState($interview->id, $state);

        return [
            'message' => $nextQuestion,
            'type' => 'new_question',
            'progress' => $this->calculateProgress($state),
            'is_complete' => $state['questions_asked'] >= self::MAX_QUESTIONS,
        ];
    }

    /**
     * End the interview and generate final results.
     */
    public function endInterview(InterviewSession $interview): array
    {
        $state = $this->getState($interview->id);
        
        if (!$state || empty($state['evaluations'])) {
            return [
                'overall_score' => 0,
                'summary' => 'Interview ended early. No responses were evaluated.',
                'strengths' => [],
                'improvements' => ['Complete more questions in future interviews'],
                'detailed_feedback' => [],
                'questions_answered' => 0,
                'questions_skipped' => 0,
                'duration_minutes' => 0,
                'hints_used' => 0,
            ];
        }

        // Calculate overall score
        $validEvaluations = collect($state['evaluations'])->where('skipped', false);
        $overallScore = $validEvaluations->avg('score') ?? 0;

        // Generate comprehensive summary
        $summary = $this->generateFinalSummary($state, $overallScore, $interview);

        // Clean up cache
        $this->clearState($interview->id);

        return [
            'overall_score' => round($overallScore, 1),
            'summary' => $summary['summary'],
            'strengths' => $summary['strengths'],
            'improvements' => $summary['improvements'],
            'detailed_feedback' => $state['evaluations'],
            'questions_answered' => $validEvaluations->count(),
            'questions_skipped' => collect($state['evaluations'])->where('skipped', true)->count(),
            'duration_minutes' => now()->diffInMinutes($state['started_at']),
            'hints_used' => $state['hints_used'],
        ];
    }

    /**
     * Get current conversation state.
     */
    public function getConversation(InterviewSession $interview): array
    {
        $state = $this->getState($interview->id);
        
        return [
            'messages' => $state['messages'] ?? [],
            'progress' => $this->calculateProgress($state ?? []),
            'current_question' => $state['current_question'] ?? 0,
        ];
    }

    /**
     * Get interview progress.
     */
    public function getProgress(InterviewSession $interview): array
    {
        $state = $this->getState($interview->id);
        
        if (!$state) {
            return ['progress' => 0, 'questions_answered' => 0, 'total_questions' => self::MAX_QUESTIONS];
        }

        return $this->calculateProgress($state);
    }

    // ========== Private Helper Methods ==========

    /**
     * Generate opening message based on interview type.
     */
    protected function generateOpening(InterviewType $type, string $trackName, string $difficulty): array
    {
        $interviewerName = $this->getInterviewerName($type);
        
        $openings = [
            InterviewType::BEHAVIORAL->value => [
                "Hello! I'm {$interviewerName}, and I'll be conducting your behavioral interview today. " .
                "I'm excited to learn more about your experiences and how you've handled various situations. " .
                "This will be a conversation, so feel free to ask me to clarify anything. " .
                "Let's start with something to help you relax... **Tell me a bit about yourself and what drew you to the {$trackName} field?**",
            ],
            InterviewType::TECHNICAL_CODING->value => [
                "Hi there! I'm {$interviewerName}, your technical interviewer for today. " .
                "We'll be discussing some coding concepts and problem-solving approaches. " .
                "Don't worry about being perfect – I'm more interested in your thought process. " .
                "Let's begin... **Can you walk me through a recent technical project you worked on?**",
            ],
            InterviewType::SYSTEM_DESIGN->value => [
                "Welcome! I'm {$interviewerName}, and we'll be doing a system design discussion today. " .
                "I'll present you with a scenario, and we'll work through the design together. " .
                "Think of this as a collaborative session – feel free to ask questions and think out loud. " .
                "First, **tell me about a system or architecture you've worked with that you found interesting.**",
            ],
        ];

        $messages = $openings[$type->value] ?? $openings[InterviewType::BEHAVIORAL->value];
        
        return [
            'message' => $messages[0],
            'interviewer_name' => $interviewerName,
        ];
    }

    /**
     * Get a friendly interviewer name based on type.
     */
    protected function getInterviewerName(InterviewType $type): string
    {
        return match($type) {
            InterviewType::BEHAVIORAL => 'Alex',
            InterviewType::TECHNICAL_CODING => 'Jordan',
            InterviewType::SYSTEM_DESIGN => 'Sam',
            default => 'Alex',
        };
    }

    /**
     * Determine what type of response to generate.
     */
    protected function determineResponseType(array $state, string $userMessage): string
    {
        $wordCount = str_word_count($userMessage);
        $followUps = $state['follow_ups_for_current'];
        $questionsAsked = $state['questions_asked'];

        // If response is very short, probe deeper
        if ($wordCount < 15 && $followUps < self::MAX_FOLLOW_UPS_PER_QUESTION) {
            return 'probe';
        }

        // If we've had enough follow-ups or good response, evaluate and move on
        if ($followUps >= self::MAX_FOLLOW_UPS_PER_QUESTION || $wordCount >= 50) {
            // Check if we've asked enough questions
            if ($questionsAsked >= self::MAX_QUESTIONS - 1) {
                return 'final_evaluation';
            }
            return 'evaluate_and_next';
        }

        // Default: ask a follow-up
        return 'follow_up';
    }

    /**
     * Generate AI response based on context.
     */
    protected function generateResponse(
        array $state,
        string $userMessage,
        string $responseType,
        InterviewSession $interview
    ): array {
        if (!$this->isConfigured()) {
            return $this->getFallbackResponse($responseType, $state, $interview);
        }

        try {
            $prompt = $this->buildConversationalPrompt($state, $userMessage, $responseType, $interview);
            $result = $this->callOpenAI($prompt, true);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Live Interview AI Error: ' . $e->getMessage());
            return $this->getFallbackResponse($responseType, $state, $interview);
        }
    }

    /**
     * Build the conversational prompt for OpenAI.
     */
    protected function buildConversationalPrompt(
        array $state,
        string $userMessage,
        string $responseType,
        InterviewSession $interview
    ): string {
        $context = $this->buildConversationContext($state);
        $type = InterviewType::from($state['type']);
        
        $systemContext = "You are {$this->getInterviewerName($type)}, a friendly but professional interviewer " .
            "conducting a {$type->label()} interview. The candidate is at {$state['difficulty']} level.\n\n" .
            "Interview Context:\n{$context}\n\n" .
            "The candidate just said: \"{$userMessage}\"\n\n";

        $instruction = match($responseType) {
            'probe' => "The response was brief. Ask a probing follow-up question to get more details. " .
                "Be encouraging and specific about what you'd like to know more about.",
            
            'follow_up' => "Ask a natural follow-up question that digs deeper into their response. " .
                "Show genuine interest in their experience.",
            
            'evaluate_and_next' => "1. Briefly acknowledge their response positively (1 sentence)\n" .
                "2. Provide a short piece of constructive feedback or insight\n" .
                "3. Transition smoothly to a NEW question on a different topic\n\n" .
                "Also provide a JSON evaluation with: score (0-100), strengths (array), improvements (array)",
            
            'final_evaluation' => "This is the last response. Provide:\n" .
                "1. Warm acknowledgment of their answer\n" .
                "2. A brief summary of how the interview went\n" .
                "3. One encouraging closing statement\n\n" .
                "Also provide a JSON evaluation with: score (0-100), strengths (array), improvements (array)",
            
            default => "Respond naturally as an interviewer would.",
        };

        return $systemContext . "Your task: " . $instruction . "\n\n" .
            "Respond with JSON: {\"message\": \"your response\", \"evaluation\": {\"score\": 80, \"strengths\": [], \"improvements\": []}, \"coaching_tip\": \"optional tip\"}";
    }

    /**
     * Build conversation context string.
     */
    protected function buildConversationContext(array $state): string
    {
        $recentMessages = array_slice($state['messages'], -6);
        
        return collect($recentMessages)->map(function ($msg) {
            $role = $msg['role'] === 'interviewer' ? 'Interviewer' : 'Candidate';
            return "{$role}: {$msg['content']}";
        })->implode("\n\n");
    }

    /**
     * Generate the next question.
     */
    protected function generateNextQuestion(array $state, InterviewSession $interview): string
    {
        $type = InterviewType::from($state['type']);
        $questionNumber = $state['questions_asked'] + 1;

        if (!$this->isConfigured()) {
            return $this->getFallbackQuestion($type, $state['difficulty'], $questionNumber);
        }

        try {
            $prompt = "Generate interview question #{$questionNumber} for a {$state['difficulty']} level {$type->label()} interview.\n\n" .
                "Previous questions asked:\n" . $this->getPreviousQuestions($state) . "\n\n" .
                "Generate a NEW question on a DIFFERENT topic. Make it conversational and engaging.\n" .
                "Respond with just the question, no JSON.";

            $response = $this->callOpenAI($prompt, false);
            return $response['content'] ?? $this->getFallbackQuestion($type, $state['difficulty'], $questionNumber);
        } catch (\Exception $e) {
            return $this->getFallbackQuestion($type, $state['difficulty'], $questionNumber);
        }
    }

    /**
     * Get previous questions asked.
     */
    protected function getPreviousQuestions(array $state): string
    {
        return collect($state['messages'])
            ->where('role', 'interviewer')
            ->whereIn('type', ['opening', 'new_question', 'evaluate_and_next'])
            ->pluck('content')
            ->map(fn($q, $i) => ($i + 1) . ". " . substr($q, 0, 100) . "...")
            ->implode("\n");
    }

    /**
     * Update state after generating a response.
     */
    protected function updateStateAfterResponse(array &$state, string $responseType, array $response): void
    {
        if (in_array($responseType, ['evaluate_and_next', 'final_evaluation'])) {
            // Store evaluation
            if (!empty($response['evaluation'])) {
                $state['evaluations'][] = [
                    'question_index' => $state['current_question'],
                    'score' => $response['evaluation']['score'] ?? 70,
                    'strengths' => $response['evaluation']['strengths'] ?? [],
                    'improvements' => $response['evaluation']['improvements'] ?? [],
                    'skipped' => false,
                ];
            }

            $state['current_question']++;
            $state['questions_asked']++;
            $state['follow_ups_for_current'] = 0;
        } elseif (in_array($responseType, ['follow_up', 'probe'])) {
            $state['follow_ups_for_current']++;
        }
    }

    /**
     * Calculate interview progress.
     */
    protected function calculateProgress(array $state): array
    {
        $questionsAnswered = $state['questions_asked'] ?? 0;
        $totalQuestions = self::MAX_QUESTIONS;
        $percentage = min(100, ($questionsAnswered / $totalQuestions) * 100);

        return [
            'percentage' => round($percentage),
            'questions_answered' => $questionsAnswered,
            'total_questions' => $totalQuestions,
            'current_question' => ($state['current_question'] ?? 0) + 1,
        ];
    }

    /**
     * Generate final summary.
     */
    protected function generateFinalSummary(array $state, float $score, InterviewSession $interview): array
    {
        $allStrengths = collect($state['evaluations'])->pluck('strengths')->flatten()->unique()->take(5);
        $allImprovements = collect($state['evaluations'])->pluck('improvements')->flatten()->unique()->take(5);

        if (!$this->isConfigured()) {
            return [
                'summary' => "You completed the interview with a score of " . round($score) . "%. " .
                    "Great job on finishing! Review the detailed feedback for each question to improve.",
                'strengths' => $allStrengths->toArray() ?: ['Completed the interview'],
                'improvements' => $allImprovements->toArray() ?: ['Practice more frequently'],
            ];
        }

        try {
            $prompt = "Generate a 2-3 sentence encouraging summary for a candidate who scored {$score}% in a {$state['type']} interview.\n" .
                "Mention 1-2 strengths and 1 area for growth. Be warm and motivating.";
            
            $response = $this->callOpenAI($prompt, false);
            
            return [
                'summary' => $response['content'] ?? "Great effort on your interview! You scored " . round($score) . "%.",
                'strengths' => $allStrengths->toArray(),
                'improvements' => $allImprovements->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'summary' => "You completed the interview with a score of " . round($score) . "%.",
                'strengths' => $allStrengths->toArray(),
                'improvements' => $allImprovements->toArray(),
            ];
        }
    }

    /**
     * Call OpenAI API.
     */
    protected function callOpenAI(string $prompt, bool $jsonMode = false): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional, friendly interview coach. Respond naturally and conversationally. ' .
                    ($jsonMode ? 'Always respond with valid JSON.' : ''),
            ],
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.8,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        
        if ($jsonMode) {
            return json_decode($content, true) ?? ['message' => $content];
        }

        return ['content' => $content];
    }

    // ========== State Management ==========

    protected function getState(string $interviewId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $interviewId);
    }

    protected function saveState(string $interviewId, array $state): void
    {
        Cache::put(self::CACHE_PREFIX . $interviewId, $state, self::CACHE_TTL);
    }

    protected function clearState(string $interviewId): void
    {
        Cache::forget(self::CACHE_PREFIX . $interviewId);
    }

    // ========== Fallback Methods ==========

    protected function getFallbackResponse(string $responseType, array $state, InterviewSession $interview): array
    {
        $type = InterviewType::from($state['type']);
        
        $responses = [
            'probe' => [
                'message' => "That's interesting! Could you tell me more about the specific steps you took and the outcome?",
                'coaching_tip' => "Try to use the STAR method: Situation, Task, Action, Result.",
            ],
            'follow_up' => [
                'message' => "I appreciate that perspective. What would you do differently if you faced a similar situation today?",
                'coaching_tip' => "Showing self-reflection and growth mindset is valuable.",
            ],
            'evaluate_and_next' => [
                'message' => "Thank you for sharing that experience. I can see you've given this thought. " .
                    "Now, let me ask you something different: " . $this->getFallbackQuestion($type, $state['difficulty'], $state['questions_asked'] + 1),
                'evaluation' => ['score' => 70, 'strengths' => ['Provided relevant example'], 'improvements' => ['Add more specific metrics']],
            ],
            'final_evaluation' => [
                'message' => "That was a great answer to end on! Thank you for your thoughtful responses throughout this interview. " .
                    "You've shown good communication skills and self-awareness. The interview is now complete.",
                'evaluation' => ['score' => 75, 'strengths' => ['Good communication'], 'improvements' => ['Continue practicing']],
            ],
        ];

        return $responses[$responseType] ?? $responses['follow_up'];
    }

    protected function getFallbackQuestion(InterviewType $type, string $difficulty, int $questionNumber): string
    {
        $questions = [
            InterviewType::BEHAVIORAL->value => [
                "Tell me about a time when you had to work with a difficult team member. How did you handle it?",
                "Describe a situation where you had to meet a tight deadline. What was your approach?",
                "Can you share an example of when you received critical feedback? How did you respond?",
                "Tell me about a time when you had to learn something new quickly. What strategies did you use?",
                "Describe a project where things didn't go as planned. What did you learn from it?",
                "What's your proudest professional achievement and why?",
            ],
            InterviewType::TECHNICAL_CODING->value => [
                "Explain the difference between a stack and a queue. When would you use each?",
                "How would you approach optimizing a slow database query?",
                "What's your experience with version control? Describe your typical workflow.",
                "Explain what REST APIs are and the principles behind them.",
                "How do you approach debugging a complex issue in your code?",
                "Describe a technical challenge you solved recently. What was your approach?",
            ],
            InterviewType::SYSTEM_DESIGN->value => [
                "How would you design a URL shortening service? Walk me through the components.",
                "Describe how you would design a simple chat application. What technologies would you use?",
                "How would you approach designing a rate limiter for an API?",
                "Walk me through designing a basic notification system.",
                "How would you design a file storage service? What considerations are important?",
                "Describe how caching works and when you would use it in a system.",
            ],
        ];

        $typeQuestions = $questions[$type->value] ?? $questions[InterviewType::BEHAVIORAL->value];
        $index = min($questionNumber - 1, count($typeQuestions) - 1);
        
        return $typeQuestions[$index];
    }

    protected function getFallbackHint(InterviewType $type): string
    {
        $hints = [
            InterviewType::BEHAVIORAL->value => "Think of a specific situation. What was the context, what did YOU do, and what was the result?",
            InterviewType::TECHNICAL_CODING->value => "Break down the problem into smaller parts. What are the key components you need to consider?",
            InterviewType::SYSTEM_DESIGN->value => "Start with the requirements. What are the core features and how would they interact?",
        ];

        return $hints[$type->value] ?? $hints[InterviewType::BEHAVIORAL->value];
    }
}
