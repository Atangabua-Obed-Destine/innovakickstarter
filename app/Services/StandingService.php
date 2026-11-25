<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\AdminSetting;
use App\Models\Fee;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Models\WeeklyStanding;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The weekly bar and a fellow's standing.
 *
 * Each week (Monday-Sunday) a fellow should: be active on N days, practise on N days
 * and make N curriculum actions (start, submit, tick steps...). Settings are in the
 * "standing" group of admin settings.
 *
 * Consecutive misses: 1st = warning; from the configured miss (default 2nd) = a
 * penalty fee (default 500 FCFA, each further miss adds one) and a HOLD on curriculum
 * progress until it is paid or an admin waives it; from the miss after that = an
 * honest final warning ("your place is at risk") and the team is asked to review.
 *
 * Fair by design: nothing before the configured start week; a fellow's first week
 * is free; weeks with nothing left to work on waive the curriculum requirement;
 * admins can excuse a week (which also waives its fee).
 */
class StandingService
{
    public function __construct(protected FellowActivityService $activity) {}

    public function settings(): array
    {
        return [
            'enabled' => (bool) AdminSetting::get('standing_enabled', true),
            'start' => Carbon::parse((string) AdminSetting::get('standing_start_date', '2026-11-30'))->startOfWeek(),
            'active_days' => (int) AdminSetting::get('weekly_bar_active_days', 3),
            'practice_days' => (int) AdminSetting::get('weekly_bar_practice_days', 3),
            'progress_actions' => (int) AdminSetting::get('weekly_bar_progress_actions', 1),
            'fee' => (int) AdminSetting::get('standing_penalty_fee', 500),
            'fee_from' => max(1, (int) AdminSetting::get('standing_penalty_from_miss', 2)),
        ];
    }

    public function weekStart(?CarbonInterface $date = null): Carbon
    {
        return Carbon::parse(($date ?? now())->toDateString())->startOfWeek(Carbon::MONDAY);
    }

    /** The fellow's primary track (where the weekly bar applies). */
    public function track(User $fellow): ?Track
    {
        return FellowTrack::where('fellow_id', $fellow->id)->where('is_primary', true)->first()?->track;
    }

    /** Can there be curriculum progress right now (something open to work on)? */
    protected function hasOpenWork(User $fellow, Track $track): bool
    {
        return FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('curriculum_activity_id', TrackCurriculumActivity::where('track_id', $track->id)->select('id'))
            ->whereIn('status', [CurriculumStatus::AVAILABLE->value, CurriculumStatus::IN_PROGRESS->value, CurriculumStatus::REJECTED->value, CurriculumStatus::OVERDUE->value])
            ->exists();
    }

    /** Is the fellow in the program for that week (not before joining, not after finishing)? */
    public function eligible(User $fellow, ?Track $track, CarbonInterface $weekStart): bool
    {
        if (!$track || !$fellow->hasRole('fellow') || $fellow->is_active === false || $fellow->suspended_at) {
            return false;
        }
        $enrolment = FellowTrack::where('fellow_id', $fellow->id)->where('track_id', $track->id)->first();
        $joined = $enrolment?->started_at ?? $enrolment?->created_at;
        if (!$joined || Carbon::parse($joined)->gte($weekStart)) {
            return false; // the week they joined (or later) is free
        }
        $required = TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->where('is_required', true)->pluck('id');
        if ($required->isEmpty()) {
            return false;
        }
        $done = FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $required)->where('status', CurriculumStatus::COMPLETED->value)->count();
        if ($done >= $required->count()) {
            return false; // finished the track
        }
        // Must actually be in the program: oriented, or already working in the track.
        $oriented = TrackOrientation::where('fellow_id', $fellow->id)->where('track_id', $track->id)->whereNotNull('completed_at')->exists();

        return $oriented || !app(OrientationService::class)->isRequired($fellow, $track);
    }

    /** Live progress for the current week (for the meter). */
    public function thisWeek(User $fellow): array
    {
        $s = $this->settings();
        $start = $this->weekStart();
        $sum = $this->activity->summary($fellow, $start, $start->copy()->endOfWeek());
        $track = $this->track($fellow);
        $waived = $track && !$this->hasOpenWork($fellow, $track);
        $items = [
            ['key' => 'active', 'label' => 'Active days', 'have' => $sum['active_days'], 'need' => $s['active_days'], 'hint' => 'Any real learning action counts: a step, a review, practice, a question to the mentor.'],
            ['key' => 'practice', 'label' => 'Practice days', 'have' => $sum['practice_days'], 'need' => $s['practice_days'], 'hint' => 'Daily review, quick checks, Practice Lab, Detective, typing, Base Camp.'],
            ['key' => 'progress', 'label' => 'Curriculum actions', 'have' => $sum['progress_actions'], 'need' => $waived ? 0 : $s['progress_actions'], 'hint' => $waived ? 'Nothing open to work on right now, so this one is waived.' : 'Start or submit an activity, or tick steps in a lesson.'],
        ];
        foreach ($items as &$i) {
            $i['met'] = $i['have'] >= $i['need'];
        }

        return [
            'enabled' => $s['enabled'] && now()->gte($s['start']),
            'starts_on' => $s['start'],
            'items' => $items,
            'met' => collect($items)->every(fn ($i) => $i['met']),
            'days_left' => (int) now()->startOfDay()->diffInDays($start->copy()->endOfWeek()->startOfDay()) + 1,
            'by_day' => $sum['by_day'],
            'week_start' => $start,
        ];
    }

    /** Unpaid, unwaived penalty fees. */
    public function unpaidPenalties(User $fellow): Collection
    {
        return Fee::where('fellow_id', $fellow->id)->where('billable_type', WeeklyStanding::class)
            ->whereNotIn('status', [Fee::STATUS_PAID, Fee::STATUS_WAIVED])->orderBy('created_at')->get();
    }

    public function onHold(User $fellow): bool
    {
        return $this->unpaidPenalties($fellow)->isNotEmpty();
    }

    /** good | warning | hold | at_risk */
    public function state(User $fellow): string
    {
        $last = WeeklyStanding::where('fellow_id', $fellow->id)->orderByDesc('week_start')->first();
        $misses = $last && !$last->met ? (int) $last->consecutive_misses : 0;
        if ($misses > $this->settings()['fee_from']) {
            return 'at_risk';
        }
        if ($this->onHold($fellow)) {
            return 'hold';
        }

        return $misses > 0 ? 'warning' : 'good';
    }

    /**
     * Evaluate one finished week for every fellow (idempotent: a week already
     * evaluated for a fellow is left alone).
     *
     * @return array{evaluated: int, met: int, warnings: int, fees: int, finals: int}
     */
    public function evaluateWeek(CarbonInterface $weekStart): array
    {
        $s = $this->settings();
        $weekStart = $this->weekStart($weekStart);
        $stats = ['evaluated' => 0, 'met' => 0, 'warnings' => 0, 'fees' => 0, 'finals' => 0];
        if (!$s['enabled'] || $weekStart->lt($s['start']) || $weekStart->copy()->endOfWeek()->isFuture()) {
            return $stats;
        }

        $fellows = User::role('fellow')->whereIn('id', FellowTrack::where('is_primary', true)->select('fellow_id'))->get();
        foreach ($fellows as $fellow) {
            if (WeeklyStanding::where('fellow_id', $fellow->id)->whereDate('week_start', $weekStart->toDateString())->exists()) {
                continue;
            }
            $track = $this->track($fellow);
            if (!$this->eligible($fellow, $track, $weekStart)) {
                continue;
            }
            $result = $this->evaluateFellow($fellow, $track, $weekStart, $s);
            $stats['evaluated']++;
            $stats[$result->met ? 'met' : match ($result->action) { 'warning' => 'warnings', 'fee' => 'fees', 'final' => 'finals', default => 'warnings' }]++;
        }

        return $stats;
    }

    protected function evaluateFellow(User $fellow, Track $track, Carbon $weekStart, array $s): WeeklyStanding
    {
        return DB::transaction(function () use ($fellow, $track, $weekStart, $s) {
            $sum = $this->activity->summary($fellow, $weekStart, $weekStart->copy()->endOfWeek());
            $waived = !$this->hasOpenWork($fellow, $track);
            $met = $sum['active_days'] >= $s['active_days'] && $sum['practice_days'] >= $s['practice_days']
                && ($waived || $sum['progress_actions'] >= $s['progress_actions']);

            $previous = WeeklyStanding::where('fellow_id', $fellow->id)->whereDate('week_start', $weekStart->copy()->subWeek()->toDateString())->first();
            $misses = $met ? 0 : (($previous && !$previous->met) ? $previous->consecutive_misses : 0) + 1;
            $action = $met ? 'none' : ($misses > $s['fee_from'] ? 'final' : ($misses >= $s['fee_from'] ? 'fee' : 'warning'));

            $standing = WeeklyStanding::create([
                'fellow_id' => $fellow->id, 'track_id' => $track->id, 'week_start' => $weekStart->toDateString(),
                'active_days' => $sum['active_days'], 'practice_days' => $sum['practice_days'], 'progress_actions' => $sum['progress_actions'],
                'progress_waived' => $waived, 'met' => $met, 'consecutive_misses' => $misses, 'action' => $action,
            ]);

            if (in_array($action, ['fee', 'final'], true) && $s['fee'] > 0) {
                $standing->update(['fee_id' => $this->chargePenalty($fellow, $standing, $s['fee'])->id]);
            }
            $this->notify($fellow, $standing, $s);

            return $standing;
        });
    }

    protected function chargePenalty(User $fellow, WeeklyStanding $standing, int $amount): Fee
    {
        $week = $standing->week_start->format('j M');

        return Fee::create([
            'reference' => app(FeeService::class)->generateFeeReference(),
            'fellow_id' => $fellow->id,
            'billable_type' => WeeklyStanding::class,
            'billable_id' => (string) $standing->id,
            'title' => "Weekly bar penalty (week of {$week})",
            'description' => "Missed the weekly bar {$standing->consecutive_misses} weeks in a row. Starting and submitting activities, interviews and competitions are on hold until this is paid or waived by the team.",
            'amount_total' => $amount,
            'amount_paid' => 0,
            'currency' => 'XAF',
            'plan_type' => Fee::PLAN_ONE_TIME,
            'first_due_date' => now()->toDateString(),
            'final_due_date' => now()->toDateString(),
            'grace_period_hours' => 0,
            'status' => Fee::STATUS_ACTIVE,
            'assigned_by' => User::role('admin')->orderBy('id')->value('id'),
            'assigned_at' => now(),
        ]);
    }

    protected function notify(User $fellow, WeeklyStanding $w, array $s): void
    {
        $got = "Last week you had {$w->active_days} active " . ($w->active_days === 1 ? 'day' : 'days') . " (need {$s['active_days']}), {$w->practice_days} practice "
            . ($w->practice_days === 1 ? 'day' : 'days') . " (need {$s['practice_days']})"
            . ($w->progress_waived ? '' : " and {$w->progress_actions} curriculum " . ($w->progress_actions === 1 ? 'action' : 'actions') . " (need {$s['progress_actions']})") . '.';
        $fee = number_format($s['fee']) . ' FCFA';
        $link = ['action_url' => route('standing.show'), 'action_label' => 'See my weekly bar'];

        match ($w->action) {
            'none' => Notification::send($fellow, Notification::TYPE_ACHIEVEMENT, '✅ You met the weekly bar', 'Well done: you showed up and kept learning last week. Keep the rhythm going.', $link),
            'warning' => Notification::send($fellow, Notification::TYPE_WEEKLY_REMINDER, '⚠️ You missed the weekly bar', $got
                . ($s['fee'] > 0 ? " If you miss it again, a {$fee} penalty applies and starting or submitting activities goes on hold until it is paid." : '')
                . ' Struggling? Use "Stuck?" in your lessons, ask a human, or talk to your mentor: we want you to succeed.', $link),
            'fee' => Notification::send($fellow, Notification::TYPE_WEEKLY_REMINDER, "⛔ Penalty: {$fee}. Your progress is on hold", $got
                . " You have missed the weekly bar {$w->consecutive_misses} weeks in a row. Starting and submitting activities, interviews and competitions are on hold until the penalty is paid. You can still learn, review and ask for help. If you had a serious reason (illness, no internet, family), contact the team: they can waive it.", ['action_url' => route('fees.index'), 'action_label' => 'Pay the penalty']),
            'final' => Notification::send($fellow, Notification::TYPE_WEEKLY_REMINDER, '⚠️ Final warning: your place in the program is at risk', $got
                . " You have missed the weekly bar {$w->consecutive_misses} weeks in a row. Another {$fee} penalty was added and your progress stays on hold. The team will now review your place in the program. Please contact them.", ['action_url' => route('fees.index'), 'action_label' => 'Pay and talk to the team']),
            default => null,
        };

        if ($w->action === 'final') {
            foreach (User::role('admin')->get() as $admin) {
                Notification::send($admin, Notification::TYPE_SYSTEM_ANNOUNCEMENT, "Team review needed: {$fellow->name}",
                    "{$fellow->name} has missed the weekly bar {$w->consecutive_misses} weeks in a row and has a final warning.", ['action_url' => route('admin.support.index'), 'action_label' => 'Open Support']);
            }
        }
    }

    /** An admin excuses a week: it counts as met, and its penalty (if any) is waived. */
    public function excuse(WeeklyStanding $standing, User $admin, string $reason): void
    {
        DB::transaction(function () use ($standing, $admin, $reason) {
            $standing->update(['exempt' => true, 'exempt_reason' => $reason, 'exempted_by' => $admin->id, 'met' => true, 'consecutive_misses' => 0]);
            if ($standing->fee && !in_array($standing->fee->status, [Fee::STATUS_PAID, Fee::STATUS_WAIVED], true)) {
                app(FeeService::class)->waiveFee($standing->fee, $admin, 'Week excused: ' . $reason);
            }
        });
        Notification::send($standing->fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, 'Your week was excused',
            'The team excused the week of ' . $standing->week_start->format('j M') . ($standing->fee_id ? ' and waived its penalty' : '') . ". Reason: {$reason}", ['action_url' => route('standing.show'), 'action_label' => 'See my weekly bar']);
    }

    /** Tell every fellow about the weekly bar before it starts counting. */
    public function announce(): int
    {
        $s = $this->settings();
        $count = 0;
        foreach (User::role('fellow')->get() as $fellow) {
            Notification::send($fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, '📏 New: the weekly bar',
                "From the week of {$s['start']->format('j F')}, every fellow is expected each week to be active on at least {$s['active_days']} days, practise on {$s['practice_days']} days and make {$s['progress_actions']} curriculum "
                . ($s['progress_actions'] === 1 ? 'action' : 'actions') . " (start or submit an activity, tick lesson steps). Miss it once: a warning. Miss it "
                . ($s['fee_from'] === 2 ? 'twice in a row' : "{$s['fee_from']} weeks in a row") . ": a " . number_format($s['fee']) . ' FCFA penalty and your curriculum progress goes on hold until it is paid. Serious reasons can be excused by the team. Your meter is on your dashboard.',
                ['action_url' => route('standing.show'), 'action_label' => 'See my weekly bar']);
            $count++;
        }

        return $count;
    }
}
