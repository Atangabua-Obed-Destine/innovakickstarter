<?php

namespace App\Console\Commands;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deadlines used to count from the day a fellow joined the track, so almost
 * every submission was flagged late and lost its late-penalty points. This
 * works out when each activity really opened for the fellow (when its
 * prerequisites were completed), resets the deadline from there, clears late
 * flags that were only caused by the old rule, and gives those points back.
 * It never adds a penalty that was not already applied.
 */
class RepairCurriculumDeadlines extends Command
{
    protected $signature = 'curriculum:repair-deadlines {--dry-run : Show what would change without saving}';

    protected $description = 'Recompute curriculum deadlines from when each activity opened and refund late penalties caused by the old rule';

    /** @var array<string, ?Carbon> completion time cache: "fellow|activity" */
    protected array $completedAt = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $activities = TrackCurriculumActivity::withTrashed()->get()->keyBy('id');
        $milestones = TrackMilestone::withTrashed()->get()->keyBy('id');
        $startedAt = FellowTrack::query()->get(['fellow_id', 'track_id', 'started_at', 'created_at'])
            ->mapWithKeys(fn ($ft) => ["{$ft->fellow_id}|{$ft->track_id}" => $ft->started_at ?? $ft->created_at]);

        foreach (FellowCurriculumProgress::whereNotNull('completed_at')->get(['fellow_id', 'curriculum_activity_id', 'completed_at']) as $row) {
            $this->completedAt["{$row->fellow_id}|{$row->curriculum_activity_id}"] = $row->completed_at;
        }

        $stats = ['rows' => 0, 'deadline_changed' => 0, 'late_cleared' => 0, 'still_late' => 0, 'points_refunded' => 0, 'rows_refunded' => 0];
        $fellowsToRescore = [];
        $changes = [];

        FellowCurriculumProgress::query()
            ->where('status', '!=', CurriculumStatus::LOCKED->value)
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($activities, $milestones, $startedAt, &$stats, &$fellowsToRescore, &$changes) {
                foreach ($rows as $progress) {
                    $activity = $activities->get($progress->curriculum_activity_id);
                    if (!$activity || $activity->deadline_days === null) {
                        continue;
                    }
                    $stats['rows']++;

                    $openedAt = $this->openedAt($progress, $activity, $milestones, $activities, $startedAt);
                    $deadline = $activity->calculateDeadlineFor(new \App\Models\User(), $openedAt);
                    $grace = $deadline->copy()->addDays((int) $activity->grace_period_days);

                    $update = [];
                    if (!$progress->deadline_at || !$progress->deadline_at->equalTo($deadline)) {
                        $update['deadline_at'] = $deadline;
                        $update['grace_deadline_at'] = $grace;
                        $stats['deadline_changed']++;
                    }

                    if ($progress->late_penalty_applied && $progress->submitted_at) {
                        if ($progress->submitted_at->lessThanOrEqualTo($deadline)) {
                            $update['late_penalty_applied'] = false;
                            $stats['late_cleared']++;

                            if ($progress->status === CurriculumStatus::COMPLETED && (int) $activity->late_penalty_percent > 0) {
                                $refunded = $this->unpenalised((int) $progress->points_awarded, (int) $activity->late_penalty_percent, (int) $activity->points);
                                if ($refunded > (int) $progress->points_awarded) {
                                    $update['points_awarded'] = $refunded;
                                    $stats['points_refunded'] += $refunded - (int) $progress->points_awarded;
                                    $stats['rows_refunded']++;
                                    $fellowsToRescore[$progress->fellow_id][$activity->track_id] = true;
                                }
                            }
                        } else {
                            $stats['still_late']++;
                        }
                    }

                    // An overdue mark under the old rule: back to in progress if the new grace period has not passed.
                    if ($progress->status === CurriculumStatus::OVERDUE && now()->lessThan($grace)) {
                        $update['status'] = $progress->started_at ? CurriculumStatus::IN_PROGRESS : CurriculumStatus::AVAILABLE;
                    }

                    if ($update) {
                        $changes[] = [$progress->id, $update];
                    }
                }
            });

        $this->table(['What', 'Count'], collect($stats)->map(fn ($v, $k) => [str_replace('_', ' ', $k), $v])->values()->all());
        $this->line('Fellows whose score will be recalculated: ' . count($fellowsToRescore));

        if ($dryRun) {
            $this->warn('Dry run: nothing saved.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes) {
            foreach ($changes as [$id, $update]) {
                FellowCurriculumProgress::whereKey($id)->update($update);
            }
        });

        $calculator = app(\App\Services\CareerCapitalCalculator::class);
        foreach ($fellowsToRescore as $fellowId => $tracks) {
            $fellow = \App\Models\User::find($fellowId);
            foreach (array_keys($tracks) as $trackId) {
                $track = \App\Models\Track::find($trackId);
                if ($fellow && $track) {
                    $calculator->updateScore($fellow, $track, notify: false, markActivity: false);
                }
            }
        }

        $this->info('Saved ' . count($changes) . ' progress rows.');

        return self::SUCCESS;
    }

    /**
     * When the activity opened for this fellow: the latest completion among
     * what gated it (prerequisites, chain parent, previous sequential step,
     * prerequisite milestone), and never before they joined the track.
     */
    protected function openedAt(FellowCurriculumProgress $progress, TrackCurriculumActivity $activity, $milestones, $activities, $startedAt): Carbon
    {
        $fellow = $progress->fellow_id;
        $times = [Carbon::parse($startedAt->get("{$fellow}|{$activity->track_id}") ?? $progress->created_at)];

        $gates = array_filter(array_merge((array) ($activity->prerequisites ?? []), [$activity->chain_parent_id]));

        if ($activity->is_sequential && $activity->sequence_order > 0) {
            $previous = $activities->where('milestone_id', $activity->milestone_id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->filter(fn ($a) => $a->sequence_order < $activity->sequence_order)
                ->sortByDesc('sequence_order')
                ->first();
            if ($previous) {
                $gates[] = $previous->id;
            }
        }

        $milestone = $milestones->get($activity->milestone_id);
        if ($milestone?->unlock_after_milestone_id) {
            $required = $activities->where('milestone_id', $milestone->unlock_after_milestone_id)
                ->where('is_required', true)->where('is_active', true)->whereNull('deleted_at')->pluck('id');
            $gates = array_merge($gates, $required->all());
        }

        foreach (array_unique($gates) as $gateId) {
            if ($done = $this->completedAt["{$fellow}|{$gateId}"] ?? null) {
                $times[] = Carbon::parse($done);
            }
        }

        $opened = collect($times)->max();

        // It cannot have opened after the fellow started or submitted it.
        $firstAction = collect([$progress->started_at, $progress->submitted_at])->filter()->min();

        return $firstAction && $firstAction->lessThan($opened) ? Carbon::parse($firstAction) : $opened;
    }

    /** Points before the late penalty was taken off (approve() rounds after applying it). */
    protected function unpenalised(int $awarded, int $penaltyPercent, int $activityPoints): int
    {
        if ($penaltyPercent >= 100) {
            return $activityPoints;
        }

        return (int) round($awarded / (1 - $penaltyPercent / 100));
    }
}
