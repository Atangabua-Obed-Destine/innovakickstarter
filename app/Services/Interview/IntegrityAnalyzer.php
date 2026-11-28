<?php

namespace App\Services\Interview;

/**
 * Integrity report for an interview, from what the browser logged (full screen exits,
 * tab switches, blocked pastes and drops, copied interviewer text, sudden text bursts)
 * and what the server measured itself (time per answer, typed explanations, transcripts
 * edited far beyond what was spoken).
 *
 * Flags NEVER fail an interview: the level (clean | review | concern) and the plain
 * reasons are for the human reviewer.
 */
class IntegrityAnalyzer
{
    public const EVENT_TYPES = [
        'fullscreen_exit', 'visibility_hidden', 'blur', 'paste_blocked', 'drop_blocked', 'copy_blocked',
        'large_burst', 'mic_off', 'fullscreen_unsupported', 'resumed', 'context_menu', 'page_left',
    ];

    public const MAX_EVENTS = 400;

    /**
     * Clean events sent by the browser (never trusted beyond what they are: a log).
     *
     * @return array<int, array{type: string, at: string, turn: int, duration_ms?: int, chars?: int}>
     */
    public function sanitise(mixed $events, int $turn): array
    {
        if (is_string($events)) {
            $events = json_decode($events, true);
        }
        $out = [];
        foreach (array_slice(is_array($events) ? $events : [], 0, 200) as $e) {
            if (!is_array($e) || !in_array($e['type'] ?? null, self::EVENT_TYPES, true)) {
                continue;
            }
            $clean = ['type' => $e['type'], 'turn' => max(0, min(100, (int) ($e['turn'] ?? $turn))), 'at' => now()->toIso8601String()];
            if (isset($e['duration_ms']) && is_numeric($e['duration_ms'])) {
                $clean['duration_ms'] = max(0, min(7_200_000, (int) $e['duration_ms']));
            }
            if (isset($e['chars']) && is_numeric($e['chars'])) {
                $clean['chars'] = max(0, min(100_000, (int) $e['chars']));
            }
            $out[] = $clean;
        }

        return $out;
    }

    /** Append events to the stored integrity log (capped). */
    public function append(?array $integrity, array $events): array
    {
        $integrity = $integrity ?? [];
        $integrity['events'] = array_slice(array_merge((array) ($integrity['events'] ?? []), $events), -self::MAX_EVENTS);

        return $integrity;
    }

    /**
     * @param array $integrity stored integrity (with events)
     * @param array $responses the server's answer records
     * @return array{integrity: array, level: string}
     */
    public function analyse(array $integrity, array $responses): array
    {
        $events = collect((array) ($integrity['events'] ?? []));
        $count = fn (string $type) => $events->where('type', $type)->count();
        $seconds = fn (string $type) => (int) round($events->where('type', $type)->sum(fn ($e) => (int) ($e['duration_ms'] ?? 0)) / 1000);

        $explanations = collect($responses)->filter(fn ($r) => in_array($r['explanation_mode'] ?? null, ['voice', 'typed'], true) && empty($r['skipped']));
        $typed = $explanations->where('explanation_mode', 'typed')->count();
        $serverBursts = collect($responses)->filter(fn ($r) => !empty($r['burst']))->count();
        $heavyEdits = collect($responses)->filter(fn ($r) => !empty($r['voice_heavily_edited']))->count();
        $late = collect($responses)->filter(fn ($r) => !empty($r['late']))->count();

        $counts = [
            'paste_attempts' => $count('paste_blocked') + $count('drop_blocked'),
            'copy_attempts' => $count('copy_blocked'),
            'fullscreen_exits' => $count('fullscreen_exit'),
            'tab_switches' => $count('visibility_hidden'),
            'focus_losses' => $count('blur'),
            'large_bursts' => $count('large_burst') + $serverBursts,
            'typed_explanations' => $typed,
            'spoken_explanations' => $explanations->count() - $typed,
            'heavily_edited_transcripts' => $heavyEdits,
            'late_answers' => $late,
            'page_left' => $count('page_left'),
        ];
        $durations = [
            'fullscreen_exit_seconds' => $seconds('fullscreen_exit'),
            'hidden_seconds' => $seconds('visibility_hidden'),
            'blur_seconds' => $seconds('blur'),
        ];
        $away = max($durations['fullscreen_exit_seconds'] + $durations['hidden_seconds'], $durations['blur_seconds']);

        $review = [];
        $concern = [];
        $add = function (int $n, int $reviewAt, int $concernAt, string $text) use (&$review, &$concern) {
            if ($n >= $concernAt) {
                $concern[] = $text;
            } elseif ($n >= $reviewAt) {
                $review[] = $text;
            }
        };

        $add($counts['paste_attempts'], 1, 3, $this->plural($counts['paste_attempts'], 'paste attempt was', 'paste attempts were') . ' blocked.');
        $add($counts['fullscreen_exits'], 1, 3, 'Left full screen ' . $this->times($counts['fullscreen_exits']) . ($durations['fullscreen_exit_seconds'] ? ' (' . $this->duration($durations['fullscreen_exit_seconds']) . ' in total).' : '.'));
        $add($counts['tab_switches'], 1, 3, 'Switched to another tab or app ' . $this->times($counts['tab_switches']) . ($durations['hidden_seconds'] ? ' (' . $this->duration($durations['hidden_seconds']) . ' away).' : '.'));
        if ($counts['tab_switches'] === 0 && $counts['focus_losses'] > 0) {
            $add($counts['focus_losses'], 2, 6, 'The interview window lost focus ' . $this->times($counts['focus_losses']) . '.');
        }
        if ($away >= 60) {
            $concern[] = 'Away from the interview for ' . $this->duration($away) . ' in total.';
        }
        $add($counts['large_bursts'], 1, 2, $this->plural($counts['large_bursts'], 'large block of text appeared', 'large blocks of text appeared') . ' at once in an answer.');
        $add($counts['heavily_edited_transcripts'], 1, 3, $this->plural($counts['heavily_edited_transcripts'], 'spoken answer was', 'spoken answers were') . ' heavily rewritten by typing after transcription.');
        if ($typed > 0) {
            $share = $explanations->count() ? $typed / $explanations->count() : 0;
            $text = $this->plural($typed, 'explanation was', 'explanations were') . ' typed instead of spoken ("My microphone isn\'t working").';
            if ($share >= 0.5 && ($counts['paste_attempts'] > 0 || $counts['tab_switches'] > 0)) {
                $concern[] = $text;
            } else {
                $review[] = $text;
            }
        }
        $add($counts['page_left'], 1, 3, 'The interview page was closed or reloaded ' . $this->times($counts['page_left']) . '.');
        $add($counts['copy_attempts'], 2, 99, 'Tried to copy the interviewer\'s text ' . $this->times($counts['copy_attempts']) . '.');
        $add($late, 2, 99, $this->plural($late, 'answer was', 'answers were') . ' sent well after the time limit.');

        $level = match (true) {
            $concern !== [] => 'concern',
            count($review) >= 4 => 'concern',
            $review !== [] => 'review',
            default => 'clean',
        };
        $reasons = array_values(array_merge($concern, $review));
        if ($level === 'concern' && $concern === []) {
            array_unshift($reasons, 'Several different signals together.');
        }

        $integrity['counts'] = $counts;
        $integrity['durations'] = $durations + ['away_seconds' => $away];
        $integrity['reasons'] = $reasons;
        $integrity['typed_explanations'] = $counts['typed_explanations'];
        $integrity['paste_attempts'] = $counts['paste_attempts'];
        $integrity['large_bursts'] = $counts['large_bursts'];
        $integrity['fullscreen_exits'] = $counts['fullscreen_exits'];
        $integrity['focus_losses'] = $counts['tab_switches'] + $counts['focus_losses'];
        $integrity['late_answers'] = $late;
        $integrity['analysed_at'] = now()->toIso8601String();

        return ['integrity' => $integrity, 'level' => $level];
    }

    /** One short, transparent line for the fellow ("2 tab switches were logged"). */
    public static function summaryForFellow(?array $integrity): string
    {
        $c = (array) ($integrity['counts'] ?? []);
        $parts = [];
        foreach ([
            'tab_switches' => ['tab switch', 'tab switches'], 'fullscreen_exits' => ['full screen exit', 'full screen exits'],
            'paste_attempts' => ['blocked paste', 'blocked pastes'], 'large_bursts' => ['sudden block of text', 'sudden blocks of text'],
            'typed_explanations' => ['typed explanation', 'typed explanations'], 'copy_attempts' => ['copy attempt', 'copy attempts'],
            'late_answers' => ['late answer', 'late answers'], 'page_left' => ['page reload or close', 'page reloads or closes'],
        ] as $key => [$one, $many]) {
            $n = (int) ($c[$key] ?? 0);
            if ($n > 0) {
                $parts[] = $n . ' ' . ($n === 1 ? $one : $many);
            }
        }

        return $parts ? 'Logged: ' . implode(', ', $parts) . '.' : 'Nothing unusual was logged.';
    }

    protected function plural(int $n, string $one, string $many): string
    {
        return $n . ' ' . ($n === 1 ? $one : $many);
    }

    protected function times(int $n): string
    {
        return $n === 1 ? 'once' : "{$n} times";
    }

    protected function duration(int $seconds): string
    {
        return $seconds >= 60 ? intdiv($seconds, 60) . ' min ' . ($seconds % 60) . ' s' : "{$seconds} s";
    }
}
