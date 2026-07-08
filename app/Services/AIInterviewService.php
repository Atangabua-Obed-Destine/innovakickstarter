<?php

namespace App\Services;

use App\Enums\InterviewType;
use App\Models\AdminSetting;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * AI Interview Service
 * 
 * Integrates with OpenAI GPT-4 to provide:
 * - Dynamic interview question generation
 * - Real-time response evaluation
 * - Personalized feedback
 * - Score calculation based on responses
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AIInterviewService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model = config('services.openai.model', 'gpt-4o');
    }

    /**
     * Check if AI service is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate interview questions based on type, difficulty, and user context.
     */
    public function generateQuestions(
        InterviewType $type,
        string $difficulty = 'intermediate',
        ?Track $track = null,
        ?User $fellow = null
    ): array {
        if (!$this->isConfigured()) {
            return $this->getFallbackQuestions($type, $difficulty);
        }

        $cacheKey = "interview_questions_{$type->value}_{$difficulty}_" . ($track?->id ?? 'general');
        
        // Cache questions for 1 hour to reduce API calls
        return Cache::remember($cacheKey, 3600, function () use ($type, $difficulty, $track, $fellow) {
            try {
                return $this->callOpenAI($this->buildQuestionPrompt($type, $difficulty, $track, $fellow));
            } catch (\Exception $e) {
                Log::error('AI Interview Service Error: ' . $e->getMessage());
                return $this->getFallbackQuestions($type, $difficulty);
            }
        });
    }

    /**
     * Evaluate a candidate's response to an interview question.
     */
    public function evaluateResponse(
        string $question,
        string $response,
        InterviewType $type,
        string $difficulty = 'intermediate'
    ): array {
        if (!$this->isConfigured()) {
            return $this->getPlaceholderEvaluation($response);
        }

        try {
            $prompt = $this->buildEvaluationPrompt($question, $response, $type, $difficulty);
            $result = $this->callOpenAI($prompt, true);
            
            return [
                'score' => $result['score'] ?? 70,
                'feedback' => $result['feedback'] ?? 'Good attempt. Consider providing more specific examples.',
                'strengths' => $result['strengths'] ?? ['Clear communication'],
                'improvements' => $result['improvements'] ?? ['Add more technical depth'],
                'follow_up_question' => $result['follow_up_question'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('AI Evaluation Error: ' . $e->getMessage());
            return $this->getPlaceholderEvaluation($response);
        }
    }

    /**
     * Generate a complete interview session with personalized questions.
     */
    public function generateInterviewSession(
        User $fellow,
        Track $track,
        InterviewType $type,
        string $difficulty = 'intermediate',
        int $questionCount = 5
    ): array {
        $context = $this->buildUserContext($fellow, $track);
        $questions = [];

        if (!$this->isConfigured()) {
            return $this->getFallbackQuestions($type, $difficulty);
        }

        try {
            $prompt = $this->buildSessionPrompt($type, $difficulty, $questionCount, $context);
            $questions = $this->callOpenAI($prompt);
            
            // Add metadata to each question
            return collect($questions)->map(function ($q, $index) use ($type, $difficulty) {
                return [
                    'id' => $index + 1,
                    'question' => $q['question'] ?? $q,
                    'type' => $q['type'] ?? $type->value,
                    'difficulty' => $difficulty,
                    'time_limit' => $this->getTimeLimit($type, $difficulty),
                    'hints' => $q['hints'] ?? [],
                    'evaluation_criteria' => $q['criteria'] ?? $this->getDefaultCriteria($type),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('AI Session Generation Error: ' . $e->getMessage());
            return $this->getFallbackQuestions($type, $difficulty);
        }
    }

    /**
     * Calculate overall interview score based on all responses.
     */
    public function calculateOverallScore(array $evaluations): array
    {
        if (empty($evaluations)) {
            return ['score' => 0, 'grade' => 'N/A', 'summary' => 'No responses evaluated.'];
        }

        $totalScore = collect($evaluations)->avg('score');
        
        return [
            'score' => round($totalScore, 1),
            'grade' => $this->scoreToGrade($totalScore),
            'summary' => $this->generateScoreSummary($totalScore, $evaluations),
            'category_scores' => $this->calculateCategoryScores($evaluations),
        ];
    }

    /**
     * Generate personalized improvement recommendations.
     */
    public function generateRecommendations(
        User $fellow,
        array $interviewHistory,
        Track $track
    ): array {
        if (!$this->isConfigured() || empty($interviewHistory)) {
            return $this->getDefaultRecommendations($track);
        }

        try {
            $prompt = $this->buildRecommendationPrompt($fellow, $interviewHistory, $track);
            return $this->callOpenAI($prompt);
        } catch (\Exception $e) {
            Log::error('AI Recommendations Error: ' . $e->getMessage());
            return $this->getDefaultRecommendations($track);
        }
    }

    /**
     * Call OpenAI API with the given prompt.
     */
    protected function callOpenAI(string $prompt, bool $jsonMode = false): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post("{$this->baseUrl}/chat/completions", [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'response_format' => $jsonMode ? ['type' => 'json_object'] : null,
        ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API Error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');
        
        // Try to parse as JSON
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Return as array with content
        return ['content' => $content];
    }

    /**
     * Build the system prompt for interview context.
     */
    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert technical interviewer for the IKS Career Capital Platform. Your role is to:
1. Generate challenging but fair interview questions appropriate for the difficulty level
2. Evaluate responses objectively based on technical accuracy, communication clarity, and problem-solving approach
3. Provide constructive feedback that helps candidates improve
4. Adapt questions based on the candidate's track and experience level

Always respond in JSON format when generating questions or evaluations.
Be encouraging but honest in feedback.
Focus on practical skills and real-world scenarios.
PROMPT;
    }

    /**
     * Build prompt for question generation.
     */
    protected function buildQuestionPrompt(
        InterviewType $type,
        string $difficulty,
        ?Track $track,
        ?User $fellow
    ): string {
        $typeLabel = $type->label();
        $trackName = $track?->name ?? 'General';
        
        return <<<PROMPT
Generate 5 {$typeLabel} interview questions for a {$difficulty} level candidate in the {$trackName} track.

Requirements:
- Questions should be progressively challenging
- Include a mix of conceptual and practical questions
- For coding questions, include expected complexity
- For behavioral questions, use the STAR method format

Return as JSON array:
[
  {
    "question": "The interview question text",
    "type": "conceptual|coding|behavioral|design",
    "hints": ["Optional hint 1", "Optional hint 2"],
    "criteria": ["Evaluation criteria 1", "Evaluation criteria 2"]
  }
]
PROMPT;
    }

    /**
     * Build prompt for response evaluation.
     */
    protected function buildEvaluationPrompt(
        string $question,
        string $response,
        InterviewType $type,
        string $difficulty
    ): string {
        return <<<PROMPT
Evaluate this {$type->label()} interview response at the {$difficulty} level.

Question: {$question}

Candidate's Response: {$response}

Provide evaluation as JSON:
{
  "score": 0-100,
  "feedback": "Detailed constructive feedback",
  "strengths": ["Strength 1", "Strength 2"],
  "improvements": ["Area for improvement 1", "Area for improvement 2"],
  "follow_up_question": "Optional follow-up question if clarification needed"
}
PROMPT;
    }

    /**
     * Build prompt for full interview session.
     */
    protected function buildSessionPrompt(
        InterviewType $type,
        string $difficulty,
        int $questionCount,
        array $context
    ): string {
        $contextJson = json_encode($context);
        
        return <<<PROMPT
Generate a complete {$type->label()} interview session with {$questionCount} questions at {$difficulty} level.

Candidate Context: {$contextJson}

Create questions that:
1. Start easier and progress to harder
2. Build upon each other where appropriate
3. Test both theoretical knowledge and practical application
4. Allow the candidate to demonstrate their unique experience

Return as JSON array of question objects.
PROMPT;
    }

    /**
     * Build prompt for recommendations.
     */
    protected function buildRecommendationPrompt(
        User $fellow,
        array $interviewHistory,
        Track $track
    ): string {
        $historyJson = json_encode($interviewHistory);
        
        return <<<PROMPT
Based on this interview history, provide personalized improvement recommendations:

Track: {$track->name}
Interview History: {$historyJson}

Provide 3-5 specific, actionable recommendations as JSON:
[
  {
    "area": "Focus area",
    "recommendation": "Specific actionable advice",
    "resources": ["Resource 1", "Resource 2"],
    "priority": "high|medium|low"
  }
]
PROMPT;
    }

    /**
     * Build user context for personalized questions.
     */
    protected function buildUserContext(User $fellow, Track $track): array
    {
        $activities = $fellow->activities()
            ->where('status', 'approved')
            ->select('type', 'tech_stack')
            ->latest()
            ->limit(10)
            ->get();

        return [
            'track' => $track->name,
            'experience_level' => $fellow->experience_level ?? 'intermediate',
            'technologies' => $activities->pluck('tech_stack')->flatten()->unique()->take(10)->values()->toArray(),
            'project_count' => $fellow->activities()->where('type', 'project')->count(),
            'previous_interviews' => $fellow->interviewSessions()->count(),
        ];
    }

    /**
     * Get time limit based on question type and difficulty.
     */
    protected function getTimeLimit(InterviewType $type, string $difficulty): int
    {
        $baseTimes = [
            InterviewType::BEHAVIORAL->value => 3,
            InterviewType::TECHNICAL_CODING->value => 15,
            InterviewType::SYSTEM_DESIGN->value => 30,
        ];

        $baseTime = $baseTimes[$type->value] ?? 5;
        $multipliers = ['beginner' => 1.5, 'intermediate' => 1.0, 'advanced' => 0.8];
        
        return (int) ceil($baseTime * ($multipliers[$difficulty] ?? 1.0));
    }

    /**
     * Get default evaluation criteria by type.
     */
    protected function getDefaultCriteria(InterviewType $type): array
    {
        return match($type) {
            InterviewType::BEHAVIORAL => [
                'Clear STAR format structure',
                'Specific examples provided',
                'Demonstrates self-awareness',
                'Shows growth mindset',
            ],
            InterviewType::TECHNICAL_CODING => [
                'Correct solution approach',
                'Optimal time/space complexity',
                'Clean, readable code',
                'Handles edge cases',
            ],
            InterviewType::SYSTEM_DESIGN => [
                'Understands requirements',
                'Scalable architecture',
                'Considers trade-offs',
                'Addresses reliability',
            ],
            default => [
                'Clear communication',
                'Logical reasoning',
                'Relevant examples',
            ],
        };
    }

    /**
     * Convert score to letter grade.
     */
    protected function scoreToGrade(float $score): string
    {
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    /**
     * Generate score summary text.
     */
    protected function generateScoreSummary(float $score, array $evaluations): string
    {
        $grade = $this->scoreToGrade($score);
        $count = count($evaluations);
        
        $messages = [
            'A' => "Excellent performance! You demonstrated strong competency across all {$count} questions.",
            'B' => "Good job! You showed solid understanding with room for some improvement.",
            'C' => "Satisfactory performance. Focus on the improvement areas identified.",
            'D' => "Needs improvement. Review the feedback and practice more before your next interview.",
            'F' => "Significant improvement needed. Consider additional preparation resources.",
        ];

        return $messages[$grade] ?? "Interview completed with {$count} questions answered.";
    }

    /**
     * Calculate category-specific scores.
     */
    protected function calculateCategoryScores(array $evaluations): array
    {
        $categories = [];
        
        foreach ($evaluations as $eval) {
            $type = $eval['type'] ?? 'general';
            if (!isset($categories[$type])) {
                $categories[$type] = ['total' => 0, 'count' => 0];
            }
            $categories[$type]['total'] += $eval['score'] ?? 0;
            $categories[$type]['count']++;
        }

        return collect($categories)->map(function ($data) {
            return round($data['total'] / max($data['count'], 1), 1);
        })->toArray();
    }

    /**
     * Get fallback questions when AI is not available.
     */
    protected function getFallbackQuestions(InterviewType $type, string $difficulty): array
    {
        $questions = [
            InterviewType::BEHAVIORAL->value => [
                'beginner' => [
                    ['question' => 'Tell me about yourself and your interest in this field.', 'type' => 'introduction'],
                    ['question' => 'Describe a time when you worked effectively as part of a team.', 'type' => 'situational'],
                    ['question' => 'How do you handle feedback on your work?', 'type' => 'situational'],
                    ['question' => 'Tell me about a project you are proud of.', 'type' => 'situational'],
                    ['question' => 'What are your career goals for the next 2-3 years?', 'type' => 'goals'],
                ],
                'intermediate' => [
                    ['question' => 'Tell me about a time you faced a significant challenge at work.', 'type' => 'situational'],
                    ['question' => 'Describe a situation where you had to work with a difficult team member.', 'type' => 'situational'],
                    ['question' => 'Give an example of when you had to meet a tight deadline.', 'type' => 'situational'],
                    ['question' => 'Tell me about a time you had to learn something new quickly.', 'type' => 'situational'],
                    ['question' => 'Describe a situation where you took initiative beyond your normal responsibilities.', 'type' => 'situational'],
                ],
                'advanced' => [
                    ['question' => 'Tell me about a time you led a project through significant obstacles.', 'type' => 'leadership'],
                    ['question' => 'Describe how you handled a major disagreement with your manager.', 'type' => 'conflict'],
                    ['question' => 'Give an example of a strategic decision you made that impacted your team.', 'type' => 'strategic'],
                    ['question' => 'Tell me about a time you mentored someone and the outcome.', 'type' => 'leadership'],
                    ['question' => 'Describe a situation where you had to influence stakeholders without authority.', 'type' => 'influence'],
                ],
            ],
            InterviewType::TECHNICAL_CODING->value => [
                'beginner' => [
                    ['question' => 'Explain the difference between an array and a linked list.', 'type' => 'conceptual'],
                    ['question' => 'Write a function to check if a string is a palindrome.', 'type' => 'coding'],
                    ['question' => 'What is the difference between GET and POST HTTP methods?', 'type' => 'conceptual'],
                    ['question' => 'Explain what recursion is and when you would use it.', 'type' => 'conceptual'],
                    ['question' => 'Write a function to find the maximum value in an array.', 'type' => 'coding'],
                ],
                'intermediate' => [
                    ['question' => 'Explain the concept of time complexity and give examples.', 'type' => 'conceptual'],
                    ['question' => 'Write a function to reverse a linked list.', 'type' => 'coding'],
                    ['question' => 'How would you optimize a slow database query?', 'type' => 'problem-solving'],
                    ['question' => 'Explain the difference between SQL and NoSQL databases.', 'type' => 'conceptual'],
                    ['question' => 'Implement a function to detect a cycle in a linked list.', 'type' => 'coding'],
                ],
                'advanced' => [
                    ['question' => 'Design and implement an LRU Cache with O(1) operations.', 'type' => 'coding'],
                    ['question' => 'Explain CAP theorem and its implications for distributed systems.', 'type' => 'conceptual'],
                    ['question' => 'Implement a thread-safe singleton pattern.', 'type' => 'coding'],
                    ['question' => 'Design an algorithm to find the shortest path in a weighted graph.', 'type' => 'coding'],
                    ['question' => 'Explain eventual consistency and how to handle it in your application.', 'type' => 'conceptual'],
                ],
            ],
            InterviewType::SYSTEM_DESIGN->value => [
                'beginner' => [
                    ['question' => 'Design a simple to-do list application.', 'type' => 'design'],
                    ['question' => 'How would you structure a basic blog platform?', 'type' => 'design'],
                    ['question' => 'Design a user authentication system.', 'type' => 'design'],
                    ['question' => 'Explain how you would cache frequently accessed data.', 'type' => 'design'],
                    ['question' => 'Design a simple notification system for a web app.', 'type' => 'design'],
                ],
                'intermediate' => [
                    ['question' => 'Design a URL shortener service like bit.ly.', 'type' => 'design'],
                    ['question' => 'How would you design a real-time chat application?', 'type' => 'design'],
                    ['question' => 'Design a rate limiter for an API.', 'type' => 'design'],
                    ['question' => 'How would you design a file storage service like Dropbox?', 'type' => 'design'],
                    ['question' => 'Design a social media feed system.', 'type' => 'design'],
                ],
                'advanced' => [
                    ['question' => 'Design a distributed search engine.', 'type' => 'design'],
                    ['question' => 'How would you design a video streaming platform like YouTube?', 'type' => 'design'],
                    ['question' => 'Design a global payment processing system.', 'type' => 'design'],
                    ['question' => 'How would you design a real-time collaborative document editor?', 'type' => 'design'],
                    ['question' => 'Design a ride-sharing system like Uber.', 'type' => 'design'],
                ],
            ],
        ];

        $typeQuestions = $questions[$type->value] ?? $questions[InterviewType::BEHAVIORAL->value];
        return $typeQuestions[$difficulty] ?? $typeQuestions['intermediate'];
    }

    /**
     * Get placeholder evaluation when AI is not available.
     */
    protected function getPlaceholderEvaluation(string $response): array
    {
        $wordCount = str_word_count($response);
        $baseScore = min(70, max(40, $wordCount * 2));
        
        return [
            'score' => $baseScore,
            'feedback' => 'Your response has been recorded. A mentor will review and provide detailed feedback.',
            'strengths' => ['Response provided'],
            'improvements' => ['Await mentor review for detailed feedback'],
            'follow_up_question' => null,
        ];
    }

    /**
     * Get default recommendations when AI is not available.
     */
    protected function getDefaultRecommendations(Track $track): array
    {
        return [
            [
                'area' => 'Technical Skills',
                'recommendation' => "Continue building projects in the {$track->name} track to strengthen practical skills.",
                'resources' => ['Track coursework', 'Practice projects'],
                'priority' => 'high',
            ],
            [
                'area' => 'Interview Practice',
                'recommendation' => 'Complete at least one mock interview per week to build confidence.',
                'resources' => ['AI Interview simulator', 'Peer practice sessions'],
                'priority' => 'high',
            ],
            [
                'area' => 'Communication',
                'recommendation' => 'Practice explaining technical concepts clearly using the STAR method.',
                'resources' => ['Communication workshops', 'Presentation practice'],
                'priority' => 'medium',
            ],
        ];
    }
}
