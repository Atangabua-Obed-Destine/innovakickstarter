<?php

namespace App\Services;

use App\Models\FellowCurriculumProgress;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiUnavailableException;
use App\Support\LessonSteps;
use App\Support\TrackExperience;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * "Check my work first": before submitting, the AI compares what the fellow is
 * about to send with the activity's public requirements (what to submit, the
 * self-check, evidence types and grading criteria) and points out gaps.
 * It does not grade, never sees the private reviewer guide, and does not open links.
 */
class PreSubmitCheckService
{
    public const DAILY_LIMIT = 15;

    public function __construct(protected AiClient $ai) {}

    /** @return array{ok: bool, error?: string, ready?: bool, summary?: string, items?: array, tip?: string, remaining?: int} */
    public function check(FellowCurriculumProgress $progress, array $draft): array
    {
        $fellow = $progress->fellow;
        $key = "precheck:{$fellow->id}:" . now()->toDateString();
        $used = (int) Cache::get($key, 0);
        if ($used >= self::DAILY_LIMIT) {
            return ['ok' => false, 'error' => 'You have used all ' . self::DAILY_LIMIT . ' checks for today. Your work can still be submitted.'];
        }

        $activity = $progress->curriculumActivity;
        $sections = LessonSteps::parse($activity->instructions) ?? [];
        $find = fn (string $pattern) => collect($sections)->first(fn ($s) => preg_match($pattern, (string) $s['title']));
        $toSubmit = $find('/what to submit|submission/i');
        $selfCheck = $find('/self.?check/i');
        $sectionText = fn (?array $s) => $s ? trim(strip_tags((string) $s['intro'] . "\n" . collect($s['items'])->pluck('plain')->implode("\n- ") . "\n" . strip_tags((string) $s['outro']))) : '';

        $requirements = array_filter([
            'Activity: ' . $activity->title,
            'What the lesson asks for: ' . Str::limit(strip_tags((string) $activity->description), 600),
            $toSubmit ? "What to submit:\n" . Str::limit($sectionText($toSubmit), 1500) : null,
            $selfCheck ? "Self-check:\n- " . Str::limit(collect($selfCheck['items'])->pluck('plain')->implode("\n- "), 1200) : null,
            $activity->evidence_requirements ? 'Evidence types expected: ' . collect($activity->evidence_requirements)->map(fn ($e) => \App\Enums\EvidenceType::tryFrom($e)?->label() ?? $e)->implode(', ') : null,
            $activity->requires_video ? "Show & Tell video: a PUBLIC YouTube video on the fellow's own channel (screen + voice, 2-4 minutes) explaining the problem, what they did, what they found and what it means. The link is required." : null,
            $activity->evaluation_rubric ? "Grading criteria:\n" . collect($activity->evaluation_rubric)->map(fn ($c) => '- ' . ($c['criterion'] ?? '') . ' (' . ($c['weight'] ?? '?') . '%): ' . ($c['description'] ?? ''))->implode("\n") : null,
        ]);

        $files = array_values(array_filter((array) ($draft['files'] ?? []), 'is_string'));
        $submission = implode("\n", array_filter([
            'Link: ' . (trim((string) ($draft['url'] ?? '')) ?: '(none)'),
            $activity->requires_video ? 'Show & Tell video link: ' . (trim((string) ($draft['video'] ?? '')) ?: '(none yet)') : null,
            "Written evidence:\n" . (trim((string) ($draft['text'] ?? '')) ?: '(empty)'),
            $files ? 'Files attached: ' . implode(', ', array_map(fn ($f) => Str::limit($f, 80), array_slice($files, 0, 5))) : 'Files attached: (none)',
            "Reflection:\n" . (trim((string) ($draft['reflection'] ?? '')) ?: '(empty)'),
        ]));

        $company = TrackExperience::for($activity->track)?->company();
        $system = implode("\n", [
            'You help a beginner check a submission BEFORE they send it for review' . ($company ? " (course setting: {$company})" : '') . '.',
            'Compare the submission with the requirements. List each requirement as covered ("ok"), missing ("missing") or unclear/needs a closer look ("check").',
            'You cannot open links: judge a link only by its shape (e.g. is it a GitHub link to a notebook file in the folder the lesson names?). Say so when it matters.',
            'Do not grade, do not give a score or predict marks, and never write the work for them. Be kind, specific and short. Simple English.',
            'Also judge how CLEARLY the written evidence and reflection explain the work to a busy, non-technical manager: does it start with the answer, give numbers with units, explain technical words the first time, use short sentences, and say what the result means? Rate it "clear", "okay" or "unclear". List up to 5 technical words or abbreviations they used WITHOUT explaining (exact words from their text; empty list if none). Give one concrete tip that shows how to say one of their own sentences more clearly (never invent findings).',
            'Reply with JSON only: {"ready": true|false, "summary": "one sentence", "items": [{"status": "ok|missing|check", "text": "..."}], "tip": "the single most useful next step", "clarity": {"rating": "clear|okay|unclear", "jargon": ["..."], "tip": "..."}}. At most 7 items.',
        ]);
        $user = "REQUIREMENTS\n" . implode("\n\n", $requirements) . "\n\nSUBMISSION\n\"\"\"\n" . Str::limit($submission, 6000) . "\n\"\"\"";

        try {
            $result = $this->ai->chat('mentor', ['messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]],
                'temperature' => 0.2, 'response_format' => ['type' => 'json_object']], 900, ['timeout' => 45]);
        } catch (AiUnavailableException $e) {
            report($e);

            return ['ok' => false, 'error' => 'The checker is busy right now. Try again in a minute, or submit when you are sure.'];
        }

        $raw = trim(preg_replace('/<think>.*?<\/think>/s', '', (string) $result['response']->json('choices.0.message.content')));
        $json = json_decode(preg_replace('/^```(json)?|```$/m', '', $raw), true);
        if (!is_array($json) || !is_array($json['items'] ?? null)) {
            return ['ok' => false, 'error' => 'The checker could not read that. Try again.'];
        }
        Cache::put($key, $used + 1, now()->endOfDay());

        $items = collect($json['items'])->filter(fn ($i) => is_array($i) && is_string($i['text'] ?? null))
            ->map(fn ($i) => ['status' => in_array($i['status'] ?? '', ['ok', 'missing', 'check'], true) ? $i['status'] : 'check', 'text' => Str::limit($i['text'], 300)])
            ->take(7)->values()->all();

        return [
            'ok' => true,
            'ready' => (bool) ($json['ready'] ?? false) && !collect($items)->contains('status', 'missing'),
            'summary' => Str::limit((string) ($json['summary'] ?? ''), 300),
            'items' => $items,
            'tip' => Str::limit((string) ($json['tip'] ?? ''), 300),
            'clarity' => $this->clarity($json['clarity'] ?? null, (string) ($draft['text'] ?? '') . ' ' . (string) ($draft['reflection'] ?? '')),
            'remaining' => self::DAILY_LIMIT - $used - 1,
        ];
    }

    /** Keep only a well-formed clarity verdict; listed jargon must be words the fellow actually wrote. */
    public function clarity($c, string $written): ?array
    {
        if (!is_array($c) || trim($written) === '') {
            return null;
        }
        $rating = in_array($c['rating'] ?? '', ['clear', 'okay', 'unclear'], true) ? $c['rating'] : 'okay';
        $jargon = collect(is_array($c['jargon'] ?? null) ? $c['jargon'] : [])
            ->filter(fn ($w) => is_string($w) && trim($w) !== '' && mb_strlen($w) <= 40 && mb_stripos($written, trim($w)) !== false)
            ->map(fn ($w) => trim($w))->unique()->take(5)->values()->all();

        return ['rating' => $rating, 'jargon' => $jargon, 'tip' => Str::limit((string) ($c['tip'] ?? ''), 300)];
    }
}
