<?php

namespace App\Services\AiReview;

use App\Models\FellowCurriculumProgress;
use App\Models\SubmissionFingerprint;

/**
 * Finds copying between fellows.
 *
 * Text is reduced to hashed 5-word shingles (overlapping word sequences), after
 * removing the activity's own prompt so quoting the brief is not a match.
 * Overlap is measured as containment: the share of the smaller text found in
 * the other, which catches a copied paragraph inside a longer answer.
 *
 * The same GitHub repository submitted by two fellows for one activity is
 * also reported. The same fellow reusing their own repository across
 * activities is normal (projects grow across milestones) and is not.
 */
class SimilarityChecker
{
    public const SHINGLE_SIZE = 5;
    public const MIN_SHINGLES = 15;

    /**
     * Normalised words of a text.
     *
     * @return string[]
     */
    public function words(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('#https?://\S+#u', ' ', $text) ?? $text;
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;

        return array_values(array_filter(preg_split('/\s+/u', $text) ?: [], fn ($w) => $w !== ''));
    }

    /**
     * @return int[] unique shingle hashes
     */
    public function shingles(string $text): array
    {
        $words = $this->words($text);
        $hashes = [];

        for ($i = 0; $i + self::SHINGLE_SIZE <= count($words); $i++) {
            $hashes[crc32(implode(' ', array_slice($words, $i, self::SHINGLE_SIZE)))] = true;
        }

        return array_map('intval', array_keys($hashes));
    }

    /**
     * Share of the smaller set found in the larger one (0-1).
     */
    public function containment(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $common = count(array_intersect_key(array_flip($a), array_flip($b)));

        return $common / min(count($a), count($b));
    }

    /**
     * Fingerprint this submission, store it, and compare it with other
     * fellows' submissions of the same activity.
     *
     * @param  string[]  $repos  owner/repo of submitted GitHub repositories
     */
    public function check(FellowCurriculumProgress $progress, string $fellowText, string $activityPrompt, array $repos, float $threshold): array
    {
        $promptShingles = array_flip($this->shingles($activityPrompt));
        $hashes = array_values(array_filter($this->shingles($fellowText), fn ($h) => !isset($promptShingles[$h])));

        SubmissionFingerprint::updateOrCreate(
            ['progress_id' => $progress->id, 'attempt_number' => (int) ($progress->attempt_number ?: 1), 'kind' => 'text'],
            [
                'curriculum_activity_id' => $progress->curriculum_activity_id,
                'fellow_id' => $progress->fellow_id,
                'hashes' => $hashes,
                'shingle_count' => count($hashes),
                'repo' => $repos[0] ?? null,
            ]
        );

        $matches = [];

        if (count($hashes) >= self::MIN_SHINGLES) {
            SubmissionFingerprint::query()
                ->where('curriculum_activity_id', $progress->curriculum_activity_id)
                ->where('kind', 'text')
                ->where('fellow_id', '!=', $progress->fellow_id)
                ->where('shingle_count', '>=', self::MIN_SHINGLES)
                ->with('fellow:id,name')
                ->orderBy('id')
                ->chunk(200, function ($rows) use ($hashes, $threshold, &$matches) {
                    foreach ($rows as $row) {
                        $score = $this->containment($hashes, $row->hashes ?? []);
                        if ($score >= $threshold) {
                            $matches[$row->progress_id] = $this->match($row, 'text', $score);
                        }
                    }
                });
        }

        $repoMatches = [];
        foreach ($repos as $repo) {
            SubmissionFingerprint::query()
                ->where('curriculum_activity_id', $progress->curriculum_activity_id)
                ->where('fellow_id', '!=', $progress->fellow_id)
                ->whereRaw('LOWER(repo) = ?', [strtolower($repo)])
                ->with('fellow:id,name')
                ->get()
                ->each(function ($row) use (&$repoMatches, $repo) {
                    $repoMatches[$row->progress_id] = $this->match($row, 'same_repository', 1.0) + ['repo' => $repo];
                });
        }

        $all = array_values($matches + $repoMatches);
        usort($all, fn ($a, $b) => $b['overlap'] <=> $a['overlap']);

        return [
            'shingles' => count($hashes),
            'compared' => count($hashes) >= self::MIN_SHINGLES,
            'threshold' => $threshold,
            'matches' => array_slice($all, 0, 5),
        ];
    }

    protected function match(SubmissionFingerprint $row, string $type, float $score): array
    {
        return [
            'type' => $type,
            'progress_id' => $row->progress_id,
            'fellow_id' => $row->fellow_id,
            'fellow_name' => $row->fellow?->name,
            'overlap' => round($score * 100, 1),
            'submitted_at' => $row->created_at?->toIso8601String(),
        ];
    }
}
