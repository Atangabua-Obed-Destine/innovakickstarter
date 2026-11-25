<?php

namespace App\Services;

use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiUnavailableException;
use App\Support\LessonSteps;
use App\Support\TrackExperience;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Work tickets: the fellow can ask the stakeholder who "sent" a project clarifying
 * questions, as analysts do at work. The AI answers in character about the business
 * need, audience and priorities, never with the solution.
 */
class StakeholderService
{
    public const DAILY_LIMIT = 15;

    public function __construct(protected AiClient $ai) {}

    public function stakeholder(TrackCurriculumActivity $activity): array
    {
        $exp = TrackExperience::for($activity->track);
        $m = $exp?->manager();

        return ['name' => $m['name'] ?? 'Your manager', 'title' => $m['title'] ?? 'Team lead', 'initials' => $m['initials'] ?? 'TL', 'company' => $exp?->company()];
    }

    /** @return array{ok: bool, error?: string, reply?: string} */
    public function ask(User $fellow, TrackCurriculumActivity $activity, string $question, array $history = []): array
    {
        $key = "stakeholder:{$fellow->id}:" . now()->toDateString();
        $used = (int) Cache::get($key, 0);
        if ($used >= self::DAILY_LIMIT) {
            return ['ok' => false, 'error' => 'That is enough questions for today: make a decision, note your assumptions, and move on. That is what analysts do too!'];
        }
        $s = $this->stakeholder($activity);
        $sections = LessonSteps::parse($activity->instructions) ?? [];
        $goal = collect($sections)->first(fn ($x) => preg_match('/goal|situation|why/i', (string) $x['title']));
        $brief = Str::limit(strip_tags((string) $activity->description) . "\n" . strip_tags((string) ($goal['intro'] ?? '')) . "\n" . strip_tags((string) ($activity->milestone?->story['brief'] ?? '')), 2500);

        $system = implode("\n", [
            "You are {$s['name']}, {$s['title']}" . ($s['company'] ? " at {$s['company']}" : '') . '. A junior analyst on your team is working on a task you asked for and asks you a clarifying question.',
            'Stay in character: a busy but kind manager who is NOT technical. Answer about the business need, who will use the result, priorities, constraints and what "good" looks like.',
            'Never give the technical solution, code, formulas or the answers to the exercise. If asked for those, say it is their call as the analyst and ask what they would recommend.',
            'If the question is vague, ask them to be more specific. If something is not decided in the task, make a reasonable business decision and say so.',
            'Reply in 2-5 short sentences, simple English. End with one short question back when it helps them think.',
            "The task you gave them:\n{$activity->title}\n{$brief}",
        ]);
        $messages = [['role' => 'system', 'content' => $system]];
        foreach (array_slice($history, -6) as $h) {
            if (in_array($h['role'] ?? '', ['user', 'assistant'], true) && is_string($h['text'] ?? null)) {
                $messages[] = ['role' => $h['role'], 'content' => Str::limit($h['text'], 800)];
            }
        }
        $messages[] = ['role' => 'user', 'content' => Str::limit($question, 800)];

        try {
            $result = $this->ai->chat('mentor', ['messages' => $messages, 'temperature' => 0.5], 400, ['timeout' => 40]);
        } catch (AiUnavailableException $e) {
            report($e);

            return ['ok' => false, 'error' => "{$s['name']} is in a meeting right now. Try again in a minute."];
        }
        $reply = trim(preg_replace('/<think>.*?<\/think>/s', '', (string) $result['response']->json('choices.0.message.content')));
        if ($reply === '') {
            return ['ok' => false, 'error' => 'No answer this time. Try again.'];
        }
        Cache::put($key, $used + 1, now()->endOfDay());
        app(FellowActivityService::class)->record($fellow, 'mentor');

        return ['ok' => true, 'reply' => Str::limit($reply, 1200)];
    }
}
