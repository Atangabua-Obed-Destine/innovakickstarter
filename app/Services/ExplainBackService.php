<?php

namespace App\Services;

use App\Models\Explanation;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiUnavailableException;
use App\Support\TrackExperience;
use Illuminate\Support\Str;

/**
 * "Explain it back" (the Feynman technique): the fellow explains a lesson's
 * key idea in plain words; the AI mentor rates it 1-5 and says what is
 * strong and what is missing. Low stakes: practice XP only.
 */
class ExplainBackService
{
    public const XP = 10;
    public const PASS_SCORE = 3;
    public const DAILY_LIMIT = 10;

    public function __construct(protected AiClient $ai) {}

    public function prompt(TrackCurriculumActivity $activity): string
    {
        $audience = TrackExperience::for($activity->track)?->company()
            ? 'a ' . TrackExperience::for($activity->track)->company() . ' branch manager who doesn\'t code'
            : 'a friend who has never studied this';

        return "In 3 to 5 sentences, explain the key idea of \"{$activity->title}\" to {$audience}. Say what it is, why it matters, and give one small example.";
    }

    /** @return array{ok: bool, error?: string, score?: int, verdict?: string, strengths?: array, missing?: array, tip?: string, xp?: int} */
    public function evaluate(User $fellow, TrackCurriculumActivity $activity, string $text): array
    {
        $today = Explanation::where('fellow_id', $fellow->id)->whereDate('answered_on', now()->toDateString())->count();
        if ($today >= self::DAILY_LIMIT) {
            return ['ok' => false, 'error' => 'You have explained ' . self::DAILY_LIMIT . ' ideas today. Come back tomorrow!'];
        }

        $system = implode("\n", [
            'You are a kind, honest mentor checking whether a beginner really understands an idea (the Feynman technique).',
            'Judge only the explanation: is it correct, clear to a non-technical person, and does it include why it matters and an example?',
            'Score 1-5: 1 = wrong or empty, 2 = major gaps or errors, 3 = correct but thin, 4 = correct and clear, 5 = correct, clear, with a good example.',
            'Do not reward length or jargon. Never write the explanation for them.',
            'Reply with JSON only: {"score": 1-5, "verdict": "one short sentence", "strengths": ["..."], "missing": ["..."], "tip": "one concrete next step"} in simple English, at most 2 items per list.',
        ]);
        $user = "Lesson: {$activity->title}\nWhat the lesson covers: " . Str::limit(strip_tags((string) $activity->description), 900)
            . "\nTask given to the fellow: {$this->prompt($activity)}\n\nThe fellow's explanation:\n\"\"\"\n{$text}\n\"\"\"";

        try {
            $result = $this->ai->chat('mentor', ['messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]],
                'temperature' => 0.2, 'response_format' => ['type' => 'json_object']], 600, ['timeout' => 45]);
        } catch (AiUnavailableException $e) {
            report($e);

            return ['ok' => false, 'error' => 'Your mentor is busy right now. Try again in a minute.'];
        }

        $raw = trim(preg_replace('/<think>.*?<\/think>/s', '', (string) $result['response']->json('choices.0.message.content')));
        $json = json_decode(preg_replace('/^```(json)?|```$/m', '', $raw), true);
        $score = is_array($json) ? (int) ($json['score'] ?? 0) : 0;
        if ($score < 1 || $score > 5) {
            return ['ok' => false, 'error' => 'Your mentor could not read that. Try again.'];
        }

        $feedback = [
            'verdict' => Str::limit((string) ($json['verdict'] ?? ''), 300),
            'strengths' => array_slice(array_values(array_filter((array) ($json['strengths'] ?? []), 'is_string')), 0, 2),
            'missing' => array_slice(array_values(array_filter((array) ($json['missing'] ?? []), 'is_string')), 0, 2),
            'tip' => Str::limit((string) ($json['tip'] ?? ''), 300),
        ];
        $passed = $score >= self::PASS_SCORE;
        $earnedBefore = Explanation::where('fellow_id', $fellow->id)->where('curriculum_activity_id', $activity->id)->where('xp_awarded', '>', 0)->exists();
        $xp = $passed && !$earnedBefore ? self::XP : 0;

        Explanation::create([
            'fellow_id' => $fellow->id, 'curriculum_activity_id' => $activity->id, 'text' => $text, 'score' => $score, 'feedback' => $feedback,
            'correct' => $passed, 'xp_awarded' => $xp, 'answered_on' => now()->toDateString(), 'model' => $result['model'],
        ]);

        return ['ok' => true, 'score' => $score, 'xp' => $xp] + $feedback;
    }
}
