<?php

namespace App\Services\AiReview;

use App\Enums\CurriculumStatus;
use App\Jobs\RunAiReview;
use App\Models\AdminSetting;
use App\Models\AiReview;
use App\Models\FellowCurriculumProgress;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Runs the AI review of curriculum submissions.
 *
 * Phase 1 modes: in "shadow" the AI reviews silently and its verdict is only
 * shown to the human after they decide (so agreement can be measured
 * honestly); in "assist" the human sees the AI draft before deciding. In
 * both, a human takes every decision.
 */
class AiReviewService
{
    /** Activity types that are scored automatically elsewhere. */
    public const SKIPPED_TYPES = ['mock_interview'];

    public function __construct(
        protected EvidenceCollector $collector,
        protected SimilarityChecker $similarity,
        protected AiGrader $grader,
        protected ReviewPolicy $policy,
    ) {
    }

    public function modeFor(TrackCurriculumActivity $activity): string
    {
        $mode = $activity->ai_review_mode ?: (string) AdminSetting::get('ai_review_mode', AiReview::MODE_SHADOW);

        return array_key_exists($mode, AiReview::MODES) ? $mode : AiReview::MODE_OFF;
    }

    public function shouldReview(FellowCurriculumProgress $progress): bool
    {
        $activity = $progress->curriculumActivity;

        return $activity
            && $this->modeFor($activity) !== AiReview::MODE_OFF
            && !in_array($activity->type?->value ?? (string) $activity->type, self::SKIPPED_TYPES, true);
    }

    /**
     * Create the review record for the current attempt and queue it.
     */
    public function queue(FellowCurriculumProgress $progress, bool $sync = false): ?AiReview
    {
        $progress->loadMissing('curriculumActivity');

        if (!$this->shouldReview($progress)) {
            return null;
        }

        $attempt = (int) ($progress->attempt_number ?: 1);
        $review = AiReview::where('progress_id', $progress->id)->where('attempt_number', $attempt)->latest()->first();

        if ($review && in_array($review->status, [AiReview::STATUS_QUEUED, AiReview::STATUS_RUNNING], true) && !$sync) {
            return $review;
        }

        // One review per attempt: a re-run replaces the earlier result.
        $attributes = [
            'curriculum_activity_id' => $progress->curriculum_activity_id,
            'fellow_id' => $progress->fellow_id,
            'status' => AiReview::STATUS_QUEUED,
            'mode' => $this->modeFor($progress->curriculumActivity),
            'error' => null,
        ];
        $review = $review
            ? tap($review)->update($attributes)
            : AiReview::create($attributes + ['progress_id' => $progress->id, 'attempt_number' => $attempt]);

        if ($sync) {
            return $this->run($review);
        }

        RunAiReview::dispatch($review->id)->afterCommit();

        return $review;
    }

    /**
     * Review one submission attempt end to end.
     */
    public function run(AiReview $review): AiReview
    {
        $started = microtime(true);
        $progress = $review->progress()->with(['curriculumActivity.track', 'curriculumActivity.milestone', 'fellow'])->first();

        if (!$progress || !$progress->fellow) {
            $review->update(['status' => AiReview::STATUS_SKIPPED, 'error' => 'Submission or fellow no longer exists.']);

            return $review;
        }

        $review->update(['status' => AiReview::STATUS_RUNNING, 'model' => $this->grader->model(), 'prompt_version' => AiGrader::PROMPT_VERSION]);

        $package = null;
        $checks = ['failures' => [], 'notes' => []];
        $integrity = [];
        $result = null;
        $usage = ['input_tokens' => null, 'output_tokens' => null];
        $error = null;

        try {
            $package = $this->collector->collect($progress);
            $checks = $this->hardChecks($package);
            $integrity = $this->integrity($progress, $package);

            if (!$this->grader->isConfigured()) {
                throw new \RuntimeException('No AI provider key is set (Admin → Integrations).');
            }

            $graded = $this->grader->grade($package, $integrity);
            $result = $graded['result'];
            $usage = ['input_tokens' => $graded['input_tokens'], 'output_tokens' => $graded['output_tokens']];
            // The backup provider may have answered.
            $review->model = $graded['model'] ?? $review->model;
        } catch (\Throwable $e) {
            $error = mb_substr($e->getMessage(), 0, 2000);
            Log::warning('AI review failed', ['ai_review' => $review->id, 'error' => $error]);
        }

        $rubric = $package['activity']['rubric'] ?? [];
        $criteria = [];
        foreach ($result['criteria'] ?? [] as $i => $criterion) {
            $criteria[] = $criterion + ['weight' => $rubric[$i]['weight'] ?? null, 'rubric_criterion' => $rubric[$i]['criterion'] ?? null];
        }
        $weighted = $result ? $this->policy->weightedScore($rubric, $result['criteria']) : null;

        // A failure never approves: the policy sends it to a human.
        $decision = $this->policy->decide($package ?? ['unread' => [], 'activity' => []], $checks, $integrity, $result, (float) $weighted);

        $review->update([
            'status' => $result ? AiReview::STATUS_COMPLETED : AiReview::STATUS_FAILED,
            'evidence' => $package ? $this->storableEvidence($package) : null,
            'checks' => $checks,
            'integrity' => $integrity,
            'criteria' => $criteria ?: null,
            'weighted_score' => $weighted,
            'confidence' => $result['confidence'] ?? null,
            'flags' => $result['flags'] ?? null,
            'requirements_met' => $result['requirements_met'] ?? null,
            'missing_requirements' => $result['missing_requirements'] ?? null,
            'authenticity_concern' => $result['authenticity_concern'] ?? null,
            'summary' => $result['summary'] ?? null,
            'feedback_for_fellow' => $result['feedback_for_fellow'] ?? null,
            'notes_for_reviewer' => $result['notes_for_reviewer'] ?? null,
            'recommended_decision' => $decision['decision'],
            'decision_reasons' => $decision['reasons'],
            'model' => $review->model,
            'input_tokens' => $usage['input_tokens'],
            'output_tokens' => $usage['output_tokens'],
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
            'error' => $error,
        ]);

        return $review->fresh();
    }

    /**
     * Rules that need no AI: required evidence present, readable, not empty.
     * Failures are things the fellow can fix and resubmit.
     */
    public function hardChecks(array $package): array
    {
        $s = $package['submission'];
        $failures = [];
        $notes = [];

        $words = fn (?string $t) => $t ? count(preg_split('/\s+/u', trim($t), -1, PREG_SPLIT_NO_EMPTY)) : 0;
        $writtenWords = $words($s['text']) + $words($s['reflection']);
        $hasUrl = !empty($s['url']) || !empty($package['github']) || !empty($package['links']);
        $hasFiles = !empty($s['file_names']);
        $readableContent = !empty($package['files'])
            || collect($package['links'])->contains(fn ($l) => ($l['ok'] ?? false) && trim((string) ($l['text'] ?? '')) !== '')
            || collect($package['github'])->contains(fn ($g) => ($g['ok'] ?? false));

        if (!$hasUrl && !$hasFiles && $writtenWords < 15) {
            $failures[] = 'No real evidence was submitted: add your work (link, files or a written answer).';
        }

        foreach ($package['github'] as $repo) {
            if (!empty($repo['not_found'])) {
                $failures[] = 'The GitHub link does not open (' . $repo['url'] . '). Make the repository public and check the link.';
            }
        }

        foreach ($package['links'] as $link) {
            if (in_array($link['status'] ?? null, [404, 410], true)) {
                $failures[] = 'The link returns "page not found" (' . $link['url'] . '). Check the link and resubmit.';
            }
        }

        foreach ($package['activity']['evidence_requirements'] as $requirement) {
            $met = match ($requirement) {
                'github_repo', 'github_commit' => !empty($package['github']),
                'url', 'social_post' => $hasUrl || $hasFiles,
                'file_upload', 'presentation', 'screenshot' => $hasFiles || $hasUrl,
                'video' => $hasUrl || $hasFiles,
                'text' => $writtenWords >= 30 || $readableContent,
                default => true,
            };

            if (!$met) {
                $failures[] = match ($requirement) {
                    'github_repo', 'github_commit' => 'This activity asks for a GitHub link, and none was submitted.',
                    'text' => 'This activity asks for a written answer; the written part is too short.',
                    default => "This activity asks for evidence of type '{$requirement}', and none was submitted.",
                };
            }
        }

        if ($writtenWords > 0 && $writtenWords < 15 && !$readableContent) {
            $notes[] = "Only {$writtenWords} words of written evidence.";
        }

        return ['failures' => array_values(array_unique($failures)), 'notes' => $notes];
    }

    /**
     * Copying between fellows, where the GitHub work came from, and timing.
     */
    public function integrity(FellowCurriculumProgress $progress, array $package): array
    {
        $s = $package['submission'];
        $a = $package['activity'];

        $ownText = implode("\n", array_filter([
            $s['text'],
            $s['reflection'],
            ...array_column($package['files'], 'content'),
        ]));
        $prompt = implode("\n", [$a['description'], $a['instructions'], ...array_column($a['rubric'], 'description')]);
        $repos = array_values(array_filter(array_map(fn ($g) => ($g['ok'] ?? false) ? ($g['repo'] ?? null) : null, $package['github'])));

        $threshold = ((float) AdminSetting::get('ai_review_similarity_threshold', 50)) / 100;
        $similarity = $this->similarity->check($progress, $ownText, $prompt, $repos, $threshold);

        $provenance = [];
        foreach ($package['github'] as $repo) {
            if (!($repo['ok'] ?? false)) {
                continue;
            }
            $h = $repo['history'] ?? [];
            $name = $repo['repo'] ?? $repo['url'];

            if ($repo['kind'] === 'gist') {
                if (($h['created_before_activity'] ?? false) && ($h['changed_during_activity'] ?? null) === false) {
                    $provenance[] = ['severity' => 'flag', 'message' => "The gist was created before this activity started and not changed since."];
                }
                if (($h['owner_is_fellow'] ?? null) === false) {
                    $provenance[] = ['severity' => 'flag', 'message' => "The gist belongs to {$repo['owner']}, not the fellow's GitHub account."];
                }
                continue;
            }

            $during = $h['commits_during_activity'] ?? null;
            if ($repo['is_fork'] ?? false) {
                $provenance[] = $during === 0
                    ? ['severity' => 'flag', 'message' => "{$name} is a fork of {$repo['fork_parent']} with no commits during this activity."]
                    : ['severity' => 'info', 'message' => "{$name} is a fork of {$repo['fork_parent']}."];
            }
            if ($during === 0 && !($repo['is_fork'] ?? false)) {
                $provenance[] = ['severity' => 'flag', 'message' => "{$name} has no commits since the fellow started this activity."];
            }
            $byOthers = $h['commits_during_activity_by_others'] ?? 0;
            if ($during && $byOthers > $during / 2) {
                $provenance[] = ['severity' => 'flag', 'message' => "Most commits during the activity in {$name} were made by other people ({$byOthers} of {$during})."];
            }
            if (($h['owner_is_fellow'] ?? null) === false) {
                $provenance[] = ['severity' => 'flag', 'message' => "{$name} belongs to {$repo['owner']}, not the fellow's GitHub account ({$h['fellow_username']})."];
            }
            if (($h['fellow_username'] ?? null) === null) {
                $provenance[] = ['severity' => 'info', 'message' => 'The fellow has no GitHub profile on file, so commit authorship was compared with the repository owner.'];
            }
            if ($during === 1 && ($repo['file_count'] ?? 0) > 15) {
                $provenance[] = ['severity' => 'info', 'message' => "All the work in {$name} during the activity arrived in a single commit ({$repo['file_count']} files)."];
            }
        }

        $minutes = $s['minutes_from_start_to_submit'];
        if ($minutes !== null && $minutes < 10 && ($a['points'] ?? 0) >= 15) {
            $provenance[] = ['severity' => 'info', 'message' => "Submitted {$minutes} minutes after starting the activity."];
        }

        return [
            'similarity' => $similarity,
            'provenance' => $provenance,
            'ai_assistance' => $s['ai_assistance'],
        ];
    }

    /**
     * Record the human decision next to the AI review, for agreement stats.
     */
    public function recordHumanDecision(string $progressId, int $attempt, User $reviewer, string $decision, ?int $score): void
    {
        AiReview::where('progress_id', $progressId)
            ->where('attempt_number', $attempt)
            ->latest()
            ->first()
            ?->update([
                'human_decision' => $decision,
                'human_reviewer_id' => $reviewer->id,
                'human_score' => $score,
                'human_decided_at' => now(),
            ]);
    }

    /**
     * The review of the attempt currently awaiting a decision (or the latest).
     */
    public function currentFor(FellowCurriculumProgress $progress): ?AiReview
    {
        return AiReview::where('progress_id', $progress->id)
            ->orderByDesc('attempt_number')
            ->latest()
            ->first();
    }

    /**
     * Keep what was read, without storing whole repositories twice.
     */
    protected function storableEvidence(array $package): array
    {
        $trim = fn (?string $text, int $max = 1500) => $text === null ? null : mb_substr($text, 0, $max);

        return [
            'activity' => array_intersect_key($package['activity'], array_flip(['title', 'type', 'points', 'evidence_requirements', 'rubric'])),
            'submission' => $package['submission'],
            'github' => array_map(function ($repo) use ($trim) {
                $repo['files'] = array_map(fn ($f) => ['path' => $f['path'], 'chars' => $f['chars'], 'truncated' => $f['truncated'], 'excerpt' => $trim($f['content'], 400)], $repo['files'] ?? []);
                $repo['file_list'] = array_slice($repo['file_list'] ?? [], 0, 60);

                return $repo;
            }, $package['github']),
            'links' => array_map(fn ($l) => array_merge($l, ['text' => $trim($l['text'] ?? null)]), $package['links']),
            'files' => array_map(fn ($f) => ['name' => $f['name'], 'chars' => $f['chars'], 'excerpt' => $trim($f['content'], 600)], $package['files']),
            'unread' => $package['unread'],
        ];
    }
}
