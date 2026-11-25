<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use Illuminate\Support\Facades\Cache;

/**
 * Key words: plain-English definitions (with a Nkwen Traders example) for the technical
 * words a lesson uses. Read from database/data/glossary/{track-slug}.json (public content).
 * A lesson shows the words it uses, newest first, and the page underlines the first
 * time each word appears so a fellow can tap it for the meaning.
 */
class GlossaryService
{
    /** Everyday words that are also technical terms: shown only in the chapter that teaches them, never underlined. */
    public const EVERYDAY = ['mean', 'table', 'cell', 'filter', 'spread', 'sample', 'index', 'mode', 'trend', 'legend', 'range', 'list', 'row', 'column', 'chart', 'bug', 'function', 'loop', 'string', 'query', 'metric', 'dimension', 'argument', 'method', 'commit', 'insight', 'caveat', 'bins', 'grain', 'axis'];

    /** @return array<int, array{key: string, term: string, aliases: string[], definition: string, example: string, chapter: int, case_sensitive: bool}> */
    public function terms(?Track $track): array
    {
        $file = $track ? database_path("data/glossary/{$track->slug}.json") : null;
        if (!$file || !is_file($file)) {
            return [];
        }

        return Cache::remember("glossary:{$track->slug}:" . filemtime($file), now()->addDay(), function () use ($file) {
            $data = json_decode((string) file_get_contents($file), true);

            return collect($data['terms'] ?? [])
                ->filter(fn ($t) => is_array($t) && is_string($t['term'] ?? null) && is_string($t['definition'] ?? null))
                ->map(fn ($t) => [
                    'key' => \Illuminate\Support\Str::slug($t['term']) ?: md5($t['term']),
                    'term' => $t['term'],
                    'aliases' => array_values(array_filter((array) ($t['aliases'] ?? []), 'is_string')),
                    'definition' => $t['definition'],
                    'example' => (string) ($t['example'] ?? ''),
                    'chapter' => (int) ($t['chapter'] ?? 1),
                    'case_sensitive' => (bool) ($t['case_sensitive'] ?? false),
                ])->unique('key')->values()->all();
        });
    }

    /** The chapter number (1-based) of an activity in its track. */
    public function chapterOf(TrackCurriculumActivity $activity): int
    {
        if (!$activity->milestone) {
            return 1;
        }
        $pathways = app(PathwayService::class);

        return $pathways->hasPathways($activity->track)
            ? $pathways->chapterNumber($activity->milestone)
            : max(1, TrackMilestone::where('track_id', $activity->track_id)->where('is_active', true)->where('sequence_order', '<=', $activity->milestone->sequence_order)->count());
    }

    /**
     * Terms used in a lesson, in the order they first appear, each with 'new' (introduced in this chapter).
     * 'highlight' marks terms worth underlining (taught in this chapter or recently).
     */
    public function forActivity(TrackCurriculumActivity $activity): array
    {
        $terms = $this->terms($activity->track);
        if (!$terms) {
            return [];
        }
        $text = html_entity_decode(strip_tags((string) $activity->description . "\n" . (string) $activity->instructions), ENT_QUOTES | ENT_HTML5);
        $chapter = $this->chapterOf($activity);

        $found = [];
        foreach ($terms as $t) {
            $pos = null;
            foreach ([$t['term'], ...$t['aliases']] as $word) {
                $p = $this->position($text, $word, $t['case_sensitive']);
                if ($p !== null && ($pos === null || $p < $pos)) {
                    $pos = $p;
                }
            }
            if ($pos === null) {
                continue;
            }
            $new = $t['chapter'] === $chapter;
            $everyday = in_array(mb_strtolower($t['term']), self::EVERYDAY, true);
            if ($everyday && !$new) {
                continue;
            }
            $found[] = $t + ['pos' => $pos, 'new' => $new, 'highlight' => !$everyday && $t['chapter'] >= $chapter - 2];
        }
        usort($found, fn ($a, $b) => [$b['new'], $a['pos']] <=> [$a['new'], $b['pos']]);

        return array_map(function ($t) {
            unset($t['pos']);

            return $t;
        }, $found);
    }

    private function position(string $text, string $word, bool $caseSensitive): ?int
    {
        $pattern = '/(?<![\p{L}\p{N}_])' . preg_quote($word, '/') . '(?![\p{L}\p{N}_])/u' . ($caseSensitive ? '' : 'i');

        return preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : null;
    }
}
