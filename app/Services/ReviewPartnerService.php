<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\AccountabilityPair;
use App\Models\FellowCurriculumProgress;
use App\Models\Notification;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Review partners for collaborative activities (e.g. the capstone peer review):
 * a fellow who reaches the activity is paired with another fellow waiting at
 * the same step. Both are notified with each other's GitHub link. Fellows left
 * without a partner for 3 days are flagged to the team.
 *
 * A review pair is an AccountabilityPair whose milestone_id is the activity's
 * milestone, so the partner also shows on the fellow's Curriculum page.
 */
class ReviewPartnerService
{
    public const WAIT_DAYS_BEFORE_ALERT = 3;

    /** The fellow's review partner for this activity, if any. */
    public function partnerFor(User $fellow, TrackCurriculumActivity $activity): ?User
    {
        $pair = $this->pairQuery($activity)
            ->where(fn ($q) => $q->where('fellow_a_id', $fellow->id)->orWhere('fellow_b_id', $fellow->id))
            ->first();

        return $pair ? User::find($pair->fellow_a_id === $fellow->id ? $pair->fellow_b_id : $pair->fellow_a_id) : null;
    }

    /**
     * Pair the fellow with someone waiting at the same activity. Returns the
     * partner, or null when nobody else is waiting yet.
     */
    public function matchFellow(User $fellow, TrackCurriculumActivity $activity): ?User
    {
        if (!$activity->is_collaborative || !$this->isWaiting($fellow->id, $activity)) {
            return $this->partnerFor($fellow, $activity);
        }

        return Cache::lock("review-partner:{$activity->id}", 10)->block(5, function () use ($fellow, $activity) {
            if ($partner = $this->partnerFor($fellow, $activity)) {
                return $partner;
            }

            $candidate = $this->waitingFellowIds($activity)
                ->reject(fn ($id) => $id === $fellow->id)
                ->first();
            if (!$candidate) {
                return null;
            }

            $partner = User::find($candidate);
            DB::transaction(function () use ($fellow, $partner, $activity) {
                AccountabilityPair::create([
                    'fellow_a_id' => $fellow->id,
                    'fellow_b_id' => $partner->id,
                    'track_id' => $activity->track_id,
                    'milestone_id' => $activity->milestone_id,
                    'paired_at' => now(),
                    'is_active' => true,
                ]);
            });

            $this->notifyPair($fellow, $partner, $activity);
            $this->notifyPair($partner, $fellow, $activity);

            return $partner;
        });
    }

    /**
     * Pair everyone waiting, two by two (oldest waiting first), and flag to the
     * team anyone waiting longer than WAIT_DAYS_BEFORE_ALERT. Run daily.
     *
     * @return array{paired: int, alerted: int}
     */
    public function matchWaiting(): array
    {
        $paired = 0;
        $alerted = 0;

        foreach (TrackCurriculumActivity::where('is_collaborative', true)->where('is_active', true)->get() as $activity) {
            $waiting = $this->waitingFellowIds($activity);
            $attempts = 0;
            while ($waiting->count() >= 2 && $attempts++ < 500) {
                $fellow = User::find($waiting->first());
                if (!$fellow || !$this->matchFellow($fellow, $activity)) {
                    break;
                }
                $paired++;
                $waiting = $this->waitingFellowIds($activity);
            }

            foreach ($waiting as $fellowId) {
                $progress = $this->progress($fellowId, $activity);
                $since = $progress?->started_at ?? $progress?->updated_at;
                $alertKey = "review-partner-alerted:{$activity->id}:{$fellowId}";
                if ($since && $since->lt(now()->subDays(self::WAIT_DAYS_BEFORE_ALERT)) && !Cache::has($alertKey)) {
                    $this->alertTeam(User::find($fellowId), $activity);
                    Cache::put($alertKey, true, now()->addDays(7));
                    $alerted++;
                }
            }
        }

        return ['paired' => $paired, 'alerted' => $alerted];
    }

    /** Fellows who reached the activity and have no partner for it yet, longest waiting first. */
    protected function waitingFellowIds(TrackCurriculumActivity $activity)
    {
        $paired = $this->pairQuery($activity)->get()
            ->flatMap(fn ($p) => [$p->fellow_a_id, $p->fellow_b_id])
            ->unique();

        return FellowCurriculumProgress::where('curriculum_activity_id', $activity->id)
            ->whereIn('status', [CurriculumStatus::AVAILABLE->value, CurriculumStatus::IN_PROGRESS->value])
            ->whereNotIn('fellow_id', $paired)
            ->orderByRaw('COALESCE(started_at, updated_at)')
            ->pluck('fellow_id')
            ->values();
    }

    protected function isWaiting(int $fellowId, TrackCurriculumActivity $activity): bool
    {
        return $this->waitingFellowIds($activity)->contains($fellowId);
    }

    protected function pairQuery(TrackCurriculumActivity $activity)
    {
        return AccountabilityPair::where('track_id', $activity->track_id)
            ->where('milestone_id', $activity->milestone_id)
            ->where('is_active', true);
    }

    protected function progress(int $fellowId, TrackCurriculumActivity $activity): ?FellowCurriculumProgress
    {
        return FellowCurriculumProgress::where('fellow_id', $fellowId)->where('curriculum_activity_id', $activity->id)->first();
    }

    protected function notifyPair(User $to, User $partner, TrackCurriculumActivity $activity): void
    {
        $github = $partner->github_url ? " Their GitHub: {$partner->github_url}." : ' Ask them for their GitHub link.';

        Notification::send(
            $to,
            Notification::TYPE_SYSTEM_ANNOUNCEMENT,
            'You have a review partner',
            "For \"{$activity->title}\", your partner is {$partner->name}.{$github} Review their work and they will review yours.",
            ['action_url' => route('curriculum.activity.show', $activity), 'action_label' => 'Open activity']
        );
    }

    protected function alertTeam(?User $fellow, TrackCurriculumActivity $activity): void
    {
        if (!$fellow) {
            return;
        }

        foreach (User::role('admin')->get() as $admin) {
            Notification::send(
                $admin,
                Notification::TYPE_SYSTEM_ANNOUNCEMENT,
                'A fellow needs a review partner',
                "{$fellow->name} has been waiting more than " . self::WAIT_DAYS_BEFORE_ALERT . " days for a partner on \"{$activity->title}\". Assign a mentor as reviewer, or add them to an existing pair as a group of three.",
                ['action_url' => route('admin.fellows.show', $fellow), 'action_label' => 'View fellow']
            );
        }
    }
}
