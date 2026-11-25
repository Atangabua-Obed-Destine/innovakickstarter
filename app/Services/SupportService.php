<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FlashAnswer;
use App\Models\MentorMessage;
use App\Models\Notification;
use App\Models\SupportBuddy;
use App\Models\SupportNudge;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Models\WeeklyStanding;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Early warning: spot fellows who are struggling (not only the lazy ones) and help
 * them before they give up. Signals are computed from what fellows actually do;
 * the team sees a named list with reasons, and fellows get kind, specific nudges.
 */
class SupportService
{
    public function __construct(protected FellowActivityService $activity) {}

    /**
     * @return array<int, array{key: string, label: string, detail: string, weight: int, nudge: ?string}>
     */
    public function signals(User $fellow, ?Track $track = null): array
    {
        $track ??= FellowTrack::where('fellow_id', $fellow->id)->where('is_primary', true)->first()?->track;
        if (!$track) {
            return [];
        }
        $activityIds = TrackCurriculumActivity::where('track_id', $track->id)->pluck('id');
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $activityIds)->with('curriculumActivity:id,title')->get();
        $out = [];

        // 1. Gone quiet.
        $last = collect([
            $this->activity->lastActiveDay($fellow),
            $progress->max('submitted_at'), $progress->max('started_at'),
            MentorMessage::where('fellow_id', $fellow->id)->max('created_at'),
        ])->filter()->map(fn ($d) => Carbon::parse($d))->max();
        $quietDays = $last ? (int) $last->startOfDay()->diffInDays(now()->startOfDay()) : null;
        if ($quietDays === null || $quietDays >= 5) {
            $out[] = ['key' => 'inactive', 'label' => 'Gone quiet', 'detail' => $quietDays === null ? 'No learning activity recorded yet' : "No learning activity for {$quietDays} days", 'weight' => $quietDays === null || $quietDays >= 10 ? 3 : 2,
                'nudge' => 'We miss you! Even 20 minutes today keeps your momentum: open your curriculum and pick up your next step.'];
        }

        // 2. Stuck on one activity.
        $stuck = $progress->filter(fn ($p) => $p->status === CurriculumStatus::IN_PROGRESS && $p->started_at && $p->started_at->lt(now()->subDays(4)));
        if ($stuck->isNotEmpty()) {
            $p = $stuck->sortBy('started_at')->first();
            $out[] = ['key' => 'stuck', 'label' => 'Stuck on an activity', 'detail' => '"' . ($p->curriculumActivity?->title ?? 'activity') . '" started ' . $p->started_at->diffForHumans(), 'weight' => 2,
                'nudge' => 'Stuck on "' . ($p->curriculumActivity?->title ?? 'your activity') . '"? Press 🆘 Stuck? on the step that blocks you, or use 🙋 Ask a human: the team will answer.'];
        }

        // 3. Work sent back twice.
        $rejected = $progress->filter(fn ($p) => $p->status === CurriculumStatus::REJECTED)->count();
        if ($rejected >= 2) {
            $out[] = ['key' => 'rejected', 'label' => 'Work sent back', 'detail' => "{$rejected} activities need fixing", 'weight' => 2,
                'nudge' => 'You have work to fix. Read the reviewer feedback, use "Check my work first" before resubmitting, and ask a human if the feedback is unclear.'];
        }

        // 4. Overdue.
        $overdue = $progress->filter(fn ($p) => in_array($p->status, [CurriculumStatus::IN_PROGRESS, CurriculumStatus::OVERDUE, CurriculumStatus::AVAILABLE], true) && $p->deadline_at && $p->deadline_at->isPast())->count();
        if ($overdue > 0) {
            $out[] = ['key' => 'overdue', 'label' => 'Past deadlines', 'detail' => "{$overdue} " . ($overdue === 1 ? 'activity' : 'activities') . ' past the deadline', 'weight' => $overdue >= 3 ? 3 : 1,
                'nudge' => 'Some deadlines have passed. Late work still counts for most of its points: submit what you have, then improve.'];
        }

        // 5. Getting review questions wrong.
        $recent = FlashAnswer::where('fellow_id', $fellow->id)->where('created_at', '>=', now()->subDays(14))->latest('id')->limit(12)->pluck('correct');
        if ($recent->count() >= 6 && $recent->filter()->count() / $recent->count() < 0.5) {
            $out[] = ['key' => 'low_checks', 'label' => 'Low quiz accuracy', 'detail' => round($recent->filter()->count() / $recent->count() * 100) . '% right in recent review and quick checks', 'weight' => 2,
                'nudge' => 'Some ideas haven\'t clicked yet, and that is normal. Re-read the lesson, then use "Explain it back": explaining is the fastest way to understand.'];
        }

        // 6. Pressing "Stuck?" a lot.
        $stuckPresses = MentorMessage::where('fellow_id', $fellow->id)->where('role', 'user')->where('created_at', '>=', now()->subDays(7))
            ->where(fn ($q) => $q->where('content', 'like', "I'm stuck on step%")->orWhere('content', 'like', "I'm on step%"))->count();
        if ($stuckPresses >= 4) {
            $out[] = ['key' => 'stuck_presses', 'label' => 'Asking for help often', 'detail' => "{$stuckPresses} \"Stuck?\" questions this week", 'weight' => 1,
                'nudge' => 'You are asking good questions. If the AI hints aren\'t enough, press 🙋 Ask a human: a person from the team will help.'];
        }

        // 7. Base Camp gaps after Chapter 2.
        $gaps = app(FoundationService::class)->gaps($fellow);
        $chapter = app(FlashReviewService::class)->currentChapter($fellow, $track);
        if ($gaps && $chapter >= 3) {
            $out[] = ['key' => 'base_camp', 'label' => 'Base Camp unfinished', 'detail' => 'Still to strengthen: ' . implode(', ', $gaps), 'weight' => 1,
                'nudge' => 'Your Base Camp still has: ' . implode(', ', $gaps) . '. Short, regular practice there makes every chapter easier.'];
        }

        // 8. Missed last week's bar.
        $lastWeek = WeeklyStanding::where('fellow_id', $fellow->id)->orderByDesc('week_start')->first();
        if ($lastWeek && !$lastWeek->met) {
            $out[] = ['key' => 'weekly_bar', 'label' => 'Missed the weekly bar', 'detail' => $lastWeek->consecutive_misses . ' week(s) in a row', 'weight' => min(3, $lastWeek->consecutive_misses + 1), 'nudge' => null];
        }

        return $out;
    }

    /** Fellows needing support, most urgent first. */
    public function atRisk(?string $trackId = null): Collection
    {
        $ids = FellowTrack::where('is_primary', true)->when($trackId, fn ($q) => $q->where('track_id', $trackId))->pluck('fellow_id');

        return User::role('fellow')->whereIn('id', $ids)->where(fn ($q) => $q->whereNull('is_active')->orWhere('is_active', true))->get()
            ->map(function (User $u) {
                $signals = $this->signals($u);

                return ['fellow' => $u, 'signals' => $signals, 'score' => array_sum(array_column($signals, 'weight')),
                    'buddy' => SupportBuddy::where('fellow_id', $u->id)->where('active', true)->with('buddy:id,name')->first()];
            })
            ->filter(fn ($r) => $r['score'] > 0)
            ->sortByDesc('score')->values();
    }

    /** Daily: one kind message per fellow with the signals not mentioned in the last 7 days. */
    public function nudgeAll(): int
    {
        $sent = 0;
        $standing = app(StandingService::class);
        $ids = FellowTrack::where('is_primary', true)->pluck('fellow_id');
        foreach (User::role('fellow')->whereIn('id', $ids)->get() as $fellow) {
            // Only fellows actually in the program (joined, oriented or working, not finished).
            if (!$standing->eligible($fellow, $standing->track($fellow), $standing->weekStart())) {
                continue;
            }
            $fresh = collect($this->signals($fellow))->filter(fn ($s) => $s['nudge']
                && !SupportNudge::where('fellow_id', $fellow->id)->where('signal', $s['key'])->where('created_at', '>=', now()->subDays(7))->exists());
            if ($fresh->isEmpty()) {
                continue;
            }
            Notification::send($fellow, Notification::TYPE_WEEKLY_REMINDER, '💬 A quick note from the I-NNOVA team',
                $fresh->pluck('nudge')->implode(' '), ['action_url' => route('curriculum.index'), 'action_label' => 'Open my curriculum']);
            foreach ($fresh as $s) {
                SupportNudge::create(['fellow_id' => $fellow->id, 'signal' => $s['key']]);
            }
            $sent++;
        }

        return $sent;
    }

    public function assignBuddy(User $fellow, User $buddy, User $admin, ?string $note = null): SupportBuddy
    {
        SupportBuddy::where('fellow_id', $fellow->id)->update(['active' => false]);
        $pair = SupportBuddy::create(['fellow_id' => $fellow->id, 'buddy_id' => $buddy->id, 'assigned_by' => $admin->id, 'note' => $note, 'active' => true]);
        $first = fn (User $u) => explode(' ', trim($u->name))[0];
        Notification::send($fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, '🤝 Meet your support buddy',
            "The team paired you with {$buddy->name} ({$buddy->email}). {$first($buddy)} is a classmate who can help when you are stuck. Say hello!" . ($note ? " Note from the team: {$note}" : ''));
        Notification::send($buddy, Notification::TYPE_SYSTEM_ANNOUNCEMENT, '🤝 You are a support buddy',
            "The team asked you to support {$fellow->name} ({$fellow->email}). A short message or a 15-minute call when they are stuck makes a big difference. Helping others also grows your collaboration score." . ($note ? " Note from the team: {$note}" : ''));

        return $pair;
    }

    public function buddyFor(User $fellow): ?User
    {
        return SupportBuddy::where('fellow_id', $fellow->id)->where('active', true)->latest('id')->first()?->buddy;
    }
}
