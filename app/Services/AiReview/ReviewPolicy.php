<?php

namespace App\Services\AiReview;

use App\Models\AdminSetting;
use App\Models\AiReview;

/**
 * Turns the hard checks, integrity findings and AI scores into a
 * recommendation: approve, revise (return for revision) or escalate (a human
 * must decide). The model never decides; these rules do, with thresholds the
 * admin controls.
 *
 * Principles:
 * - Integrity concerns (copying, manipulation, suspicious history) always go
 *   to a human. The AI never rejects anyone for suspected cheating.
 * - Unread evidence means the work was not fully seen, so no approval.
 * - High-value activities always get a human.
 */
class ReviewPolicy
{
    /**
     * @return array{decision: string, reasons: string[]}
     */
    public function decide(array $package, array $checks, array $integrity, ?array $result, float $weightedScore): array
    {
        $passScore = (float) AdminSetting::get('ai_review_pass_score', 70);
        $floor = (float) AdminSetting::get('ai_review_criterion_floor', 50);
        $reviseBelow = (float) AdminSetting::get('ai_review_revise_below', 50);
        $humanFrom = (int) AdminSetting::get('ai_review_human_from_points', 60);

        $escalate = [];
        $revise = [];

        // 1. Integrity: always a human.
        foreach ($integrity['similarity']['matches'] ?? [] as $match) {
            $escalate[] = $match['type'] === 'same_repository'
                ? "Same GitHub repository as {$match['fellow_name']}'s submission."
                : "{$match['overlap']}% of the text matches {$match['fellow_name']}'s submission.";
        }
        foreach ($integrity['provenance'] ?? [] as $finding) {
            if (($finding['severity'] ?? '') === 'flag') {
                $escalate[] = $finding['message'];
            }
        }

        if ($result === null) {
            $escalate[] = 'The AI review did not complete.';

            return ['decision' => AiReview::DECISION_ESCALATE, 'reasons' => array_values(array_unique($escalate))];
        }

        $flags = $result['flags'] ?? [];
        if (in_array('prompt_injection', $flags, true)) {
            $escalate[] = 'The submission tries to give instructions to the reviewer.';
        }
        if (in_array($result['authenticity_concern'] ?? 'none', ['medium', 'high'], true)) {
            $escalate[] = 'Authenticity concern (' . $result['authenticity_concern'] . '): ' . implode(' ', $result['authenticity_reasons'] ?? []);
        }
        if (in_array('inconsistent_with_evidence', $flags, true)) {
            $escalate[] = 'The write-up does not match the code or files.';
        }

        // 2. Hard checks the fellow can fix themselves.
        foreach ($checks['failures'] ?? [] as $failure) {
            $revise[] = $failure;
        }

        // 3. Evidence that could not be seen.
        $unread = !empty($package['unread']) || in_array('evidence_unverifiable', $flags, true);

        // 4. The scores.
        $criteria = $result['criteria'] ?? [];
        $lowest = empty($criteria) ? 0 : min(array_column($criteria, 'score'));
        $clearlyShort = $weightedScore < $reviseBelow
            || in_array('placeholder_or_empty', $flags, true)
            || in_array('off_topic', $flags, true);

        if ($clearlyShort && !$unread) {
            $revise[] = sprintf('Score %.0f is below %d, or the work does not address the activity.', $weightedScore, $reviseBelow);
        }

        if (!empty($escalate)) {
            return ['decision' => AiReview::DECISION_ESCALATE, 'reasons' => array_values(array_unique($escalate))];
        }

        if (!empty($revise)) {
            return ['decision' => AiReview::DECISION_REVISE, 'reasons' => array_values(array_unique($revise))];
        }

        $reasons = [];
        if ($unread) {
            $reasons[] = 'Part of the evidence could not be read: ' . implode('; ', array_map(fn ($u) => $u['item'], $package['unread'] ?? [])) . '.';
        }
        if ($weightedScore < $passScore) {
            $reasons[] = sprintf('Score %.0f is between %d and %d: borderline.', $weightedScore, $reviseBelow, $passScore);
        }
        if ($lowest < $floor) {
            $reasons[] = "One criterion scored {$lowest}, below the minimum of {$floor} for approval.";
        }
        if (($result['confidence'] ?? 'low') !== 'high') {
            $reasons[] = 'The AI is not highly confident (' . ($result['confidence'] ?? 'low') . ').';
        }
        if (!($result['requirements_met'] ?? false)) {
            $reasons[] = 'Not every requirement was met: ' . implode('; ', $result['missing_requirements'] ?? []);
        }
        if (!empty(array_diff($flags, ['generic_not_personal']))) {
            $reasons[] = 'Flags raised: ' . implode(', ', $flags) . '.';
        }
        if ($humanFrom > 0 && ($package['activity']['points'] ?? 0) >= $humanFrom) {
            $reasons[] = "High-value activity ({$package['activity']['points']} points): always reviewed by a human.";
        }

        if (!empty($reasons)) {
            return ['decision' => AiReview::DECISION_ESCALATE, 'reasons' => $reasons];
        }

        return [
            'decision' => AiReview::DECISION_APPROVE,
            'reasons' => [sprintf('Score %.0f, every criterion at least %d, high confidence, requirements met, no integrity concerns.', $weightedScore, $floor)],
        ];
    }

    /**
     * Rubric-weighted score (0-100) from the AI criterion scores.
     */
    public function weightedScore(array $rubric, array $criteria): float
    {
        $total = 0.0;
        $weights = 0.0;

        foreach ($rubric as $i => $item) {
            $score = (float) ($criteria[$i]['score'] ?? 0);
            $total += $score * $item['weight'];
            $weights += $item['weight'];
        }

        return $weights > 0 ? round($total / $weights, 2) : 0.0;
    }
}
