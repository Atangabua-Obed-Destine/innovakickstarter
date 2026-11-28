<?php

namespace App\Services\Interview;

/**
 * Communication metrics from spoken answers: words per minute, filler words,
 * and how many explanations were spoken rather than typed.
 */
class SpeechMetrics
{
    public static function words(?string $text): int
    {
        return preg_match_all("/[\\p{L}\\p{N}']+/u", (string) $text);
    }

    /** Filler words: um, uh, erm, hmm, "you know", "I mean", "basically", and "like," / "so," used as fillers. */
    public static function fillers(?string $text): int
    {
        $t = ' ' . mb_strtolower((string) $text) . ' ';
        $n = preg_match_all('/\b(u+m+|u+h+|e+r+m+|h+m+|ah+)\b/u', $t);
        $n += preg_match_all('/\b(you know|i mean|basically|kind of|sort of)\b/u', $t);
        // "like" and "so" are fillers only around pauses (commas, sentence starts).
        $n += preg_match_all('/(^|[.,!?]\s*|\s)like\s*,/u', $t);
        $n += preg_match_all('/,\s*like\b/u', $t);
        $n += preg_match_all('/(^\s*|[.!?]\s+)so\s*,/u', $t);

        return $n;
    }

    public static function wpm(int $words, float $seconds): ?int
    {
        return $seconds >= 3 ? (int) round($words / ($seconds / 60)) : null;
    }

    /**
     * @param array $responses server answer records
     * @return array{words_per_minute: ?int, filler_words: int, filler_words_per_min: ?float, spoken_answers: int, typed_answers: int, spoken_share: ?float}
     */
    public static function summarise(array $responses): array
    {
        $spoken = collect($responses)->filter(fn ($r) => ($r['explanation_mode'] ?? null) === 'voice' && empty($r['skipped']));
        $typed = collect($responses)->filter(fn ($r) => ($r['explanation_mode'] ?? null) === 'typed' && empty($r['skipped']));
        $seconds = (float) $spoken->sum(fn ($r) => (float) ($r['voice_seconds'] ?? 0));
        $words = (int) $spoken->sum(fn ($r) => self::words($r['answer'] ?? ''));
        $fillers = (int) $spoken->sum(fn ($r) => self::fillers($r['answer'] ?? ''));
        $total = $spoken->count() + $typed->count();

        return [
            'words_per_minute' => self::wpm($words, $seconds),
            'filler_words' => $fillers,
            'filler_words_per_min' => $seconds >= 30 ? round($fillers / ($seconds / 60), 1) : null,
            'spoken_seconds' => (int) round($seconds),
            'spoken_answers' => $spoken->count(),
            'typed_answers' => $typed->count(),
            'spoken_share' => $total ? round($spoken->count() / $total, 2) : null,
        ];
    }
}
