<?php

namespace App\Services\Interview;

use App\Models\InterviewBlueprint;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use Illuminate\Support\Str;

/**
 * Track interview blueprints: what a real hiring loop for the track looks like at the
 * fellow's level (rounds, rubric, hire thresholds, chapter loops, question bank).
 *
 * Schema (written by the content team, imported with `interviews:import`):
 *   {track, version, role{title,summary}, interviewer{name,title,style},
 *    rubric[{key,label,weight,what_it_means,levels{1..4}}], hire_signal{strong_hire,hire,lean_hire,lean_no},
 *    rounds{key:{label,purpose,answer_mode:voice|code|sql,language,time_limit_seconds,how_to_probe[],
 *                strong_answer_signals[],red_flags[]}},
 *    chapters[{milestone_title,pathways|null,stage,difficulty,rounds[],main_questions,minutes,topics[],skills[],pass_mark}],
 *    bank[{id,round,milestone_titles[],pathways|null,difficulty,skills[],question,setup,starter,follow_ups[],
 *          strong_answer,common_mistakes[]}]}
 *
 * Tracks without a blueprint (Cybersecurity, Cloud, ...) get a generic one built here.
 */
class BlueprintService
{
    public const MODES = ['voice', 'code', 'sql'];
    public const LANGUAGES = ['sql', 'javascript', 'python', 'php', 'dart', 'typescript'];
    public const STAGES = ['screening', 'technical', 'final'];
    public const DIFFICULTIES = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3];
    public const SIGNALS = ['strong_hire', 'hire', 'lean_hire', 'lean_no', 'no_hire'];

    /** @var array<string, array> */
    private array $cache = [];

    // ------------------------------------------------------------------ import

    /**
     * Structural errors in a blueprint (empty = valid).
     *
     * @return string[]
     */
    public function validate(mixed $bp): array
    {
        if (!is_array($bp)) {
            return ['The file is not a JSON object.'];
        }
        $errors = [];
        foreach (['track', 'version', 'role', 'interviewer', 'rubric', 'hire_signal', 'rounds', 'chapters', 'bank'] as $key) {
            if (!array_key_exists($key, $bp)) {
                $errors[] = "Missing top-level key \"{$key}\".";
            }
        }
        if ($errors) {
            return $errors;
        }
        if (!is_string($bp['track']) || $bp['track'] === '') {
            $errors[] = 'track must be the track slug.';
        }
        if (!is_int($bp['version']) || $bp['version'] < 1) {
            $errors[] = 'version must be a positive integer.';
        }
        foreach (['title', 'summary'] as $k) {
            if (!is_string($bp['role'][$k] ?? null) || trim($bp['role'][$k]) === '') {
                $errors[] = "role.{$k} is missing.";
            }
        }
        foreach (['name', 'title', 'style'] as $k) {
            if (!is_string($bp['interviewer'][$k] ?? null) || trim($bp['interviewer'][$k]) === '') {
                $errors[] = "interviewer.{$k} is missing.";
            }
        }

        // Rubric
        $rubric = is_array($bp['rubric']) ? $bp['rubric'] : [];
        if (count($rubric) < 3 || count($rubric) > 6) {
            $errors[] = 'rubric needs 3 to 6 dimensions.';
        }
        $keys = [];
        $weights = 0.0;
        foreach ($rubric as $i => $r) {
            $label = is_array($r) ? ($r['key'] ?? "#{$i}") : "#{$i}";
            if (!is_array($r)) {
                $errors[] = "rubric {$label}: not an object.";
                continue;
            }
            foreach (['key', 'label', 'weight', 'what_it_means', 'levels'] as $k) {
                if (!array_key_exists($k, $r)) {
                    $errors[] = "rubric {$label}: missing {$k}.";
                }
            }
            if (!is_string($r['key'] ?? null) || !preg_match('/^[a-z0-9_]+$/', $r['key'])) {
                $errors[] = "rubric {$label}: key must be snake_case.";
            }
            if (in_array($r['key'] ?? null, $keys, true)) {
                $errors[] = "rubric {$label}: duplicate key.";
            }
            $keys[] = $r['key'] ?? null;
            if (!is_numeric($r['weight'] ?? null) || (float) $r['weight'] <= 0) {
                $errors[] = "rubric {$label}: weight must be a positive number.";
            }
            $weights += (float) ($r['weight'] ?? 0);
            $levels = is_array($r['levels'] ?? null) ? array_map('strval', array_keys($r['levels'])) : [];
            sort($levels);
            if ($levels !== ['1', '2', '3', '4']) {
                $errors[] = "rubric {$label}: levels must be \"1\" to \"4\".";
            }
        }
        if ($rubric && abs($weights - 1.0) > 0.001) {
            $errors[] = 'rubric weights must add up to 1.0 (they add up to ' . round($weights, 3) . ').';
        }

        // Hire thresholds
        $hs = is_array($bp['hire_signal']) ? $bp['hire_signal'] : [];
        $order = ['strong_hire', 'hire', 'lean_hire', 'lean_no'];
        $values = array_map(fn ($k) => $hs[$k] ?? null, $order);
        if (in_array(null, $values, true) || array_filter($values, fn ($v) => !is_numeric($v) || $v < 0 || $v > 100)) {
            $errors[] = 'hire_signal needs strong_hire, hire, lean_hire and lean_no between 0 and 100.';
        } else {
            $sorted = $values;
            rsort($sorted);
            if ($sorted !== $values || count(array_unique($values)) !== 4) {
                $errors[] = 'hire_signal thresholds must go down: strong_hire > hire > lean_hire > lean_no.';
            }
        }

        // Rounds
        $rounds = is_array($bp['rounds']) ? $bp['rounds'] : [];
        if (!$rounds) {
            $errors[] = 'rounds is empty.';
        }
        foreach ($rounds as $key => $r) {
            if (!is_array($r)) {
                $errors[] = "round {$key}: not an object.";
                continue;
            }
            foreach (['label', 'purpose', 'answer_mode', 'language', 'time_limit_seconds', 'how_to_probe', 'strong_answer_signals', 'red_flags'] as $k) {
                if (!array_key_exists($k, $r)) {
                    $errors[] = "round {$key}: missing {$k}.";
                }
            }
            $mode = $r['answer_mode'] ?? null;
            $lang = $r['language'] ?? null;
            if (!in_array($mode, self::MODES, true)) {
                $errors[] = "round {$key}: answer_mode must be voice, code or sql.";
            }
            if ($lang !== null && !in_array($lang, self::LANGUAGES, true)) {
                $errors[] = "round {$key}: unknown language {$lang}.";
            }
            if ($mode === 'sql' && $lang !== 'sql') {
                $errors[] = "round {$key}: sql rounds need language sql.";
            }
            if ($mode === 'code' && in_array($lang, [null, 'sql'], true)) {
                $errors[] = "round {$key}: code rounds need a programming language.";
            }
            if ($mode === 'voice' && $lang !== null) {
                $errors[] = "round {$key}: voice rounds must have language null.";
            }
            $t = $r['time_limit_seconds'] ?? null;
            if (!is_int($t) || $t < 30 || $t > 1800) {
                $errors[] = "round {$key}: time_limit_seconds must be 30-1800.";
            }
            foreach (['how_to_probe', 'strong_answer_signals', 'red_flags'] as $k) {
                if (!is_array($r[$k] ?? null) || count($r[$k]) < 1) {
                    $errors[] = "round {$key}: {$k} needs at least one item.";
                }
            }
        }

        // Chapters
        $titles = [];
        foreach ((array) $bp['chapters'] as $i => $c) {
            $where = 'chapter ' . (is_array($c) && isset($c['milestone_title']) ? "\"{$c['milestone_title']}\"" : "#{$i}");
            if (!is_array($c)) {
                $errors[] = "{$where}: not an object.";
                continue;
            }
            foreach (['milestone_title', 'pathways', 'stage', 'difficulty', 'rounds', 'main_questions', 'minutes', 'topics', 'skills', 'pass_mark'] as $k) {
                if (!array_key_exists($k, $c)) {
                    $errors[] = "{$where}: missing {$k}.";
                }
            }
            $title = $this->norm($c['milestone_title'] ?? '');
            if ($title === '') {
                $errors[] = "{$where}: milestone_title is empty.";
            } elseif (isset($titles[$title])) {
                $errors[] = "{$where}: duplicate chapter.";
            }
            $titles[$title] = true;
            if (($c['pathways'] ?? null) !== null && (!is_array($c['pathways']) || !$c['pathways'])) {
                $errors[] = "{$where}: pathways must be null or a non-empty list.";
            }
            if (!in_array($c['stage'] ?? null, self::STAGES, true)) {
                $errors[] = "{$where}: stage must be screening, technical or final.";
            }
            if (!isset(self::DIFFICULTIES[$c['difficulty'] ?? ''])) {
                $errors[] = "{$where}: difficulty must be beginner, intermediate or advanced.";
            }
            if (!is_array($c['rounds'] ?? null) || !$c['rounds']) {
                $errors[] = "{$where}: rounds is empty.";
            }
            foreach ((array) ($c['rounds'] ?? []) as $rk) {
                if (!isset($rounds[$rk])) {
                    $errors[] = "{$where}: unknown round {$rk}.";
                }
            }
            if (!is_int($c['main_questions'] ?? null) || $c['main_questions'] < 1 || $c['main_questions'] > 10) {
                $errors[] = "{$where}: main_questions must be 1-10.";
            }
            if (!is_int($c['minutes'] ?? null) || $c['minutes'] < 5 || $c['minutes'] > 90) {
                $errors[] = "{$where}: minutes must be 5-90.";
            }
            if (!is_array($c['topics'] ?? null) || !$c['topics']) {
                $errors[] = "{$where}: topics is empty.";
            }
            if (!is_array($c['skills'] ?? null)) {
                $errors[] = "{$where}: skills must be a list.";
            }
            if (!is_int($c['pass_mark'] ?? null) || $c['pass_mark'] < 0 || $c['pass_mark'] > 100) {
                $errors[] = "{$where}: pass_mark must be 0-100.";
            }
        }

        // Bank
        $ids = [];
        foreach ((array) $bp['bank'] as $i => $q) {
            $where = 'bank ' . (is_array($q) && isset($q['id']) ? $q['id'] : "#{$i}");
            if (!is_array($q)) {
                $errors[] = "{$where}: not an object.";
                continue;
            }
            foreach (['id', 'round', 'milestone_titles', 'pathways', 'difficulty', 'skills', 'question', 'setup', 'starter', 'follow_ups', 'strong_answer', 'common_mistakes'] as $k) {
                if (!array_key_exists($k, $q)) {
                    $errors[] = "{$where}: missing {$k}.";
                }
            }
            if (!is_string($q['id'] ?? null) || $q['id'] === '') {
                $errors[] = "{$where}: id is empty.";
            } elseif (isset($ids[$q['id']])) {
                $errors[] = "{$where}: duplicate id.";
            }
            $ids[$q['id'] ?? ''] = true;
            if (!isset($rounds[$q['round'] ?? ''])) {
                $errors[] = "{$where}: unknown round " . ($q['round'] ?? '?') . '.';
            }
            if (!is_array($q['milestone_titles'] ?? null) || !$q['milestone_titles']) {
                $errors[] = "{$where}: milestone_titles is empty.";
            } else {
                foreach ($q['milestone_titles'] as $t) {
                    if (!isset($titles[$this->norm((string) $t)])) {
                        $errors[] = "{$where}: milestone title \"{$t}\" has no chapter entry.";
                    }
                }
            }
            if (!isset(self::DIFFICULTIES[$q['difficulty'] ?? ''])) {
                $errors[] = "{$where}: bad difficulty.";
            }
            if (!is_string($q['question'] ?? null) || trim($q['question']) === '') {
                $errors[] = "{$where}: question is empty.";
            }
            if (!is_string($q['strong_answer'] ?? null) || trim($q['strong_answer']) === '') {
                $errors[] = "{$where}: strong_answer is empty.";
            }
            foreach (['setup', 'starter'] as $k) {
                if (($q[$k] ?? null) !== null && !is_string($q[$k])) {
                    $errors[] = "{$where}: {$k} must be text or null.";
                }
            }
            foreach (['follow_ups', 'common_mistakes', 'skills'] as $k) {
                if (!is_array($q[$k] ?? null)) {
                    $errors[] = "{$where}: {$k} must be a list.";
                }
            }
        }

        return $errors;
    }

    /** Milestone titles in the blueprint that the track's curriculum does not have (a warning, not an error). */
    public function unknownMilestones(Track $track, array $bp): array
    {
        $known = TrackMilestone::where('track_id', $track->id)->pluck('title')->map(fn ($t) => $this->norm($t))->flip();

        return collect($bp['chapters'] ?? [])->pluck('milestone_title')
            ->filter(fn ($t) => !isset($known[$this->norm((string) $t)]))->values()->all();
    }

    public function store(Track $track, array $bp): InterviewBlueprint
    {
        unset($this->cache[$track->id]);

        return InterviewBlueprint::updateOrCreate(
            ['track_id' => $track->id],
            ['version' => (int) $bp['version'], 'data' => $bp, 'imported_at' => now()]
        );
    }

    // ------------------------------------------------------------------ reading

    /** The track's blueprint, or the generic one when none was imported. */
    public function forTrack(?Track $track): array
    {
        if (!$track) {
            return $this->generic(null);
        }

        if (!isset($this->cache[$track->id])) {
            $data = InterviewBlueprint::where('track_id', $track->id)->first()?->data;
            $this->cache[$track->id] = is_array($data) ? $this->normalise($data) : $this->generic($track);
        }

        return $this->cache[$track->id];
    }

    public function isGeneric(array $bp): bool
    {
        return !empty($bp['generic']);
    }

    /**
     * The chapter loop for an activity (matched by milestone title), or a generic
     * one built from the activity's interview_config and the milestone.
     */
    public function chapter(array $bp, ?TrackMilestone $milestone, ?TrackCurriculumActivity $activity = null): array
    {
        $title = $this->norm($milestone?->title ?? '');
        foreach ($bp['chapters'] ?? [] as $c) {
            if ($title !== '' && $this->norm($c['milestone_title'] ?? '') === $title) {
                return $c + ['matched' => true];
            }
        }

        $config = (array) ($activity?->interview_config ?? []);
        $type = (string) ($config['type'] ?? '');
        $topics = array_values(array_filter((array) ($config['topics'] ?? []), 'is_string'));
        if (!$topics && $milestone) {
            $topics = [$milestone->title];
        }
        $difficulty = $this->difficulty($config['difficulty'] ?? null);
        $technicalRound = isset($bp['rounds']['technical']) ? 'technical' : $this->firstVoiceRound($bp, exclude: 'behavioral');
        $rounds = $type === 'behavioral' ? ['behavioral', $technicalRound] : [$technicalRound, 'behavioral'];

        return [
            'milestone_title' => $milestone?->title ?? 'General practice',
            'pathways' => null,
            'stage' => 'technical',
            'difficulty' => $difficulty,
            'rounds' => array_values(array_unique(array_filter($rounds))),
            'main_questions' => max(2, min(8, (int) ($config['questions'] ?? 4))),
            'minutes' => max(10, min(60, (int) ($config['minutes'] ?? 25))),
            'topics' => $topics ?: ['the work of a junior ' . ($activity?->track?->name ?? 'professional')],
            'skills' => array_values((array) ($activity?->skills ?? [])),
            'pass_mark' => (int) ($config['min_score'] ?? 70),
            'matched' => false,
        ];
    }

    /** A round definition (falls back to the generic rounds). */
    public function round(array $bp, string $key): array
    {
        $round = $bp['rounds'][$key] ?? $this->genericRounds()[$key] ?? $this->genericRounds()['technical'];

        return $round + ['key' => $key];
    }

    /** Hire signal for a 0-100 score from the blueprint thresholds. */
    public function hireSignal(array $bp, float $score): string
    {
        $hs = $bp['hire_signal'] ?? $this->genericHireSignal();
        foreach (['strong_hire', 'hire', 'lean_hire', 'lean_no'] as $signal) {
            if ($score >= (float) ($hs[$signal] ?? 101)) {
                return $signal;
            }
        }

        return 'no_hire';
    }

    public static function signalLabel(?string $signal): string
    {
        return match ($signal) {
            'strong_hire' => 'Strong hire',
            'hire' => 'Hire',
            'lean_hire' => 'Lean hire',
            'lean_no' => 'Lean no',
            'no_hire' => 'No hire',
            default => 'Not rated',
        };
    }

    public function difficulty(mixed $value): string
    {
        $value = strtolower((string) $value);

        return match (true) {
            isset(self::DIFFICULTIES[$value]) => $value,
            in_array($value, ['easy', 'junior'], true) => 'beginner',
            in_array($value, ['hard', 'expert', 'senior'], true) => 'advanced',
            default => 'intermediate',
        };
    }

    public function norm(string $title): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', $title)));
    }

    // ------------------------------------------------------------------ generic

    public function generic(?Track $track): array
    {
        $name = $track?->name ?? 'your track';

        return [
            'generic' => true,
            'track' => $track?->slug ?? 'general',
            'version' => 0,
            'role' => [
                'title' => "Junior {$name} role",
                'summary' => "An entry-level {$name} position at a growing company in Cameroon. The team hires people who understand the basics, explain their thinking clearly and own their work.",
            ],
            'interviewer' => [
                'name' => 'Ngozi Tabe',
                'title' => "Senior {$name} lead",
                'style' => 'Calm, friendly and direct. Asks one thing at a time and digs into your answers.',
            ],
            'rubric' => [
                ['key' => 'technical_knowledge', 'label' => 'Technical knowledge', 'weight' => 0.35,
                    'what_it_means' => 'Knows the concepts of the chapter and uses the right words correctly.',
                    'levels' => ['1' => 'Wrong or no answer.', '2' => 'Partly right, important gaps.', '3' => 'Correct and complete for a junior.', '4' => 'Correct, precise, with examples and trade-offs.']],
                ['key' => 'problem_solving', 'label' => 'Problem solving', 'weight' => 0.25,
                    'what_it_means' => 'Breaks a problem into steps, checks assumptions and edge cases.',
                    'levels' => ['1' => 'No approach.', '2' => 'Jumps to an answer without steps.', '3' => 'Clear steps that work.', '4' => 'Clear steps, checks edge cases and compares options.']],
                ['key' => 'communication', 'label' => 'Communication', 'weight' => 0.25,
                    'what_it_means' => 'Explains clearly and in order, so a teammate could follow.',
                    'levels' => ['1' => 'Hard to follow.', '2' => 'Understandable but disorganised.', '3' => 'Clear and structured.', '4' => 'Clear, concise and adapted to the listener.']],
                ['key' => 'ownership', 'label' => 'Ownership & learning', 'weight' => 0.15,
                    'what_it_means' => 'Talks about their own work, decisions and what they learned from mistakes.',
                    'levels' => ['1' => 'No personal example.', '2' => 'Vague example.', '3' => 'Concrete own example.', '4' => 'Concrete example with reflection and a lesson applied.']],
            ],
            'hire_signal' => $this->genericHireSignal(),
            'rounds' => $this->genericRounds(),
            'chapters' => [],
            'bank' => [],
        ];
    }

    protected function genericHireSignal(): array
    {
        return ['strong_hire' => 85, 'hire' => 70, 'lean_hire' => 55, 'lean_no' => 40];
    }

    protected function genericRounds(): array
    {
        return [
            'behavioral' => [
                'label' => 'Behavioural', 'purpose' => 'How you work with others, own problems and learn.',
                'answer_mode' => 'voice', 'language' => null, 'time_limit_seconds' => 180,
                'how_to_probe' => ['Ask what they personally did, not the team.', 'Ask what they would do differently.'],
                'strong_answer_signals' => ['A real situation with a clear problem', 'What they did themselves, step by step', 'A result and what they learned'],
                'red_flags' => ['Only "we", never "I"', 'No concrete example'],
            ],
            'technical' => [
                'label' => 'Technical concepts', 'purpose' => 'Whether you understand and can explain what the chapter taught.',
                'answer_mode' => 'voice', 'language' => null, 'time_limit_seconds' => 240,
                'how_to_probe' => ['Ask for a concrete example.', 'Ask why, or what happens in an edge case.'],
                'strong_answer_signals' => ['Correct definition in plain words', 'A concrete example from their work', 'Mentions a trade-off or a common mistake'],
                'red_flags' => ['Memorised words without understanding', 'Confidently wrong'],
            ],
        ];
    }

    protected function firstVoiceRound(array $bp, string $exclude): ?string
    {
        foreach ($bp['rounds'] ?? [] as $key => $r) {
            if ($key !== $exclude && ($r['answer_mode'] ?? '') === 'voice') {
                return $key;
            }
        }

        return 'technical';
    }

    /** Fill optional keys so the engine can rely on them. */
    protected function normalise(array $bp): array
    {
        $bp['rounds'] = (array) ($bp['rounds'] ?? []);
        $bp['chapters'] = array_values((array) ($bp['chapters'] ?? []));
        $bp['bank'] = array_values((array) ($bp['bank'] ?? []));

        return $bp;
    }
}
