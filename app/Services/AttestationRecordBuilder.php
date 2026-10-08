<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\CurriculumStatus;
use App\Enums\InterviewStatus;
use App\Models\Activity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\FellowTrack;
use App\Models\InternshipProfile;
use App\Models\InterviewSession;
use App\Models\MentorshipPod;
use App\Models\TrackMilestone;
use App\Models\User;
use Carbon\Carbon;

/**
 * Attestation Record Builder
 *
 * Compiles everything a fellow verifiably did during their approved
 * internship window into a plain array. That array becomes the frozen
 * `snapshot` of an InternshipAttestation, so it must contain only scalars
 * and arrays (no models).
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationRecordBuilder
{
    /**
     * Build the full record for an internship.
     */
    public function build(InternshipProfile $profile): array
    {
        $fellow = $profile->fellow;
        $start = $profile->approved_start_date->copy()->startOfDay();
        $end = $profile->approved_end_date->copy()->endOfDay();

        $fellowTracks = FellowTrack::where('fellow_id', $fellow->id)
            ->approved()
            ->with('track')
            ->orderByDesc('is_primary')
            ->orderByDesc('score')
            ->get()
            ->filter(fn (FellowTrack $ft) => $ft->track !== null)
            ->values();

        $projects = $this->projects($fellow, $start, $end);

        return [
            'compiled_at' => now()->toIso8601String(),
            'holder' => [
                'name' => $fellow->name,
                'fellow_type' => $fellow->fellow_type?->value,
                'fellow_type_label' => $fellow->fellow_type?->label(),
            ],
            'internship' => $this->internship($profile),
            'tracks' => $fellowTracks->map(fn (FellowTrack $ft) => $this->track($fellow, $ft, $start, $end))->all(),
            'projects' => $projects,
            'interviews' => $this->interviews($fellow, $start, $end),
            'attendance' => $this->attendance($fellow, $start, $end),
            'technologies' => $this->technologies($projects),
            'badges' => $this->badges($fellow, $start, $end),
            'highlights' => $this->highlights($fellow),
        ];
    }

    /**
     * Institution, supervisor and the admin-approved period.
     */
    protected function internship(InternshipProfile $profile): array
    {
        $days = $profile->total_days ?? 0;

        return [
            'type' => $profile->type,
            'institution' => $profile->institution_name,
            'department' => $profile->department,
            'academic_level' => InternshipProfile::ACADEMIC_LEVELS[$profile->academic_level] ?? $profile->academic_level,
            'student_id' => $profile->student_id,
            'supervisor_name' => $profile->supervisor_name,
            'start_date' => $profile->approved_start_date->toDateString(),
            'end_date' => $profile->approved_end_date->toDateString(),
            'days' => $days,
            'weeks' => (int) max(1, round($days / 7)),
        ];
    }

    /**
     * One enrolled track: Career Capital standing and curriculum work.
     */
    protected function track(User $fellow, FellowTrack $fellowTrack, Carbon $start, Carbon $end): array
    {
        $track = $fellowTrack->track;

        $completed = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', fn ($q) => $q->where('track_id', $track->id))
            ->get()
            ->filter(fn (FellowCurriculumProgress $p) => ($p->submitted_at ?? $p->completed_at)?->between($start, $end))
            ->keyBy('curriculum_activity_id');

        $milestones = TrackMilestone::where('track_id', $track->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->with(['curriculumActivities' => fn ($q) => $q->where('is_active', true)->orderBy('sequence_order')])
            ->get();

        $requiredTotal = 0;
        $requiredDone = 0;
        $milestoneRows = [];

        foreach ($milestones as $milestone) {
            $activities = [];
            foreach ($milestone->curriculumActivities as $activity) {
                $progress = $completed->get($activity->id);
                if ($activity->is_required) {
                    $requiredTotal++;
                    $requiredDone += $progress ? 1 : 0;
                }
                if ($progress) {
                    $activities[] = [
                        'title' => $activity->title,
                        'type' => $activity->type?->label(),
                        'score' => (int) $progress->score_awarded,
                        'points' => (int) $progress->points_awarded,
                        'completed_at' => $progress->completed_at?->toDateString(),
                    ];
                }
            }

            if ($activities) {
                $milestoneRows[] = [
                    'title' => $milestone->title,
                    'completed' => count($activities),
                    'total' => $milestone->curriculumActivities->count(),
                    'activities' => $activities,
                ];
            }
        }

        return [
            'name' => $track->name,
            'is_primary' => (bool) $fellowTrack->is_primary,
            'career_capital' => [
                'score' => round((float) $fellowTrack->score, 1),
                'tier' => $fellowTrack->tier,
                'tier_label' => $fellowTrack->tier_label,
                'categories' => collect($fellowTrack->score_breakdown)
                    ->mapWithKeys(fn ($c) => [$c['label'] => round((float) $c['score'], 1)])
                    ->all(),
            ],
            'curriculum' => [
                'activities_completed' => $completed->count(),
                'required_total' => $requiredTotal,
                'required_completed' => $requiredDone,
                'average_score' => $completed->isNotEmpty() ? round($completed->avg('score_awarded'), 1) : null,
                'points' => (int) $completed->sum('points_awarded'),
                'milestones' => $milestoneRows,
            ],
        ];
    }

    /**
     * Approved self-reported activities (projects, posts, mentoring, ...).
     */
    protected function projects(User $fellow, Carbon $start, Carbon $end): array
    {
        return Activity::where('fellow_id', $fellow->id)
            ->where('status', ActivityStatus::APPROVED->value)
            ->orderBy('submitted_at')
            ->get()
            ->filter(fn (Activity $a) => ($a->submitted_at ?? $a->created_at)?->between($start, $end))
            ->map(fn (Activity $a) => [
                'title' => $a->title,
                'type' => $a->type?->label(),
                'tech_stack' => array_values(array_filter((array) $a->tech_stack, 'is_string')),
                'url' => $a->demo_url ?: ($a->url ?: $a->github_url),
                'points' => (int) $a->points_earned,
                'date' => ($a->submitted_at ?? $a->created_at)?->toDateString(),
            ])
            ->values()
            ->all();
    }

    /**
     * Completed, non-practice mock interviews.
     */
    protected function interviews(User $fellow, Carbon $start, Carbon $end): array
    {
        $sessions = InterviewSession::where('fellow_id', $fellow->id)
            ->where('status', InterviewStatus::COMPLETED->value)
            ->where(fn ($q) => $q->where('is_practice', false)->orWhereNull('is_practice'))
            ->whereBetween('completed_at', [$start, $end])
            ->orderBy('completed_at')
            ->get();

        $scores = $sessions->map(fn (InterviewSession $s) => $s->score ?? $s->overall_score)->filter(fn ($v) => $v !== null);

        return [
            'count' => $sessions->count(),
            'average_score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
            'by_type' => $sessions->groupBy(fn (InterviewSession $s) => $s->type?->label() ?? 'Interview')
                ->map->count()
                ->all(),
            'items' => $sessions->map(fn (InterviewSession $s) => [
                'title' => $s->title ?: ($s->type?->label() ?? 'Mock interview'),
                'type' => $s->type?->label(),
                'mode' => $s->mode?->value,
                'score' => ($s->score ?? $s->overall_score) !== null ? round((float) ($s->score ?? $s->overall_score), 1) : null,
                'date' => $s->completed_at?->toDateString(),
            ])->all(),
        ];
    }

    /**
     * Attendance against the closed sessions held during the window.
     * A session with no record for the fellow counts as an absence.
     */
    protected function attendance(User $fellow, Carbon $start, Carbon $end): ?array
    {
        $sessionIds = AttendanceSession::where('status', 'closed')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return null;
        }

        $records = AttendanceRecord::where('fellow_id', $fellow->id)
            ->whereIn('session_id', $sessionIds)
            ->get();

        $present = $records->where('status', 'present')->count();
        $late = $records->where('status', 'late')->count();
        $onLeave = $records->where('status', 'on_leave')->count();
        $sessions = $sessionIds->count();
        $absent = $sessions - $present - $late - $onLeave;

        $minutes = $records
            ->filter(fn (AttendanceRecord $r) => $r->clock_in_time && $r->clock_out_time)
            ->sum(fn (AttendanceRecord $r) => max(0, $r->clock_in_time->diffInMinutes($r->clock_out_time)));

        // Approved leave is not held against the fellow.
        $countable = $sessions - $onLeave;

        return [
            'sessions' => $sessions,
            'present' => $present,
            'late' => $late,
            'absent' => max(0, $absent),
            'on_leave' => $onLeave,
            'rate' => $countable > 0 ? round((($present + $late) / $countable) * 100, 1) : null,
            'hours' => (int) round($minutes / 60),
        ];
    }

    /**
     * Technologies evidenced by approved work. Self-declared profile skills
     * are left out: nothing unreviewed goes on an attestation.
     */
    protected function technologies(array $projects): array
    {
        return collect($projects)
            ->pluck('tech_stack')
            ->flatten()
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique(fn ($v) => mb_strtolower($v))
            ->take(24)
            ->values()
            ->all();
    }

    protected function badges(User $fellow, Carbon $start, Carbon $end): array
    {
        return FellowBadge::where('fellow_id', $fellow->id)
            ->whereBetween('earned_at', [$start, $end])
            ->orderBy('earned_at')
            ->get()
            ->map(fn (FellowBadge $b) => [
                'name' => $b->badge_name,
                'earned_at' => $b->earned_at?->toDateString(),
            ])
            ->all();
    }

    protected function highlights(User $fellow): array
    {
        $ledPod = MentorshipPod::where('lead_id', $fellow->id)->latest()->first();

        return [
            'pod_lead' => $ledPod !== null,
            'pod_name' => $ledPod?->display_name,
            'longest_streak_weeks' => (int) FellowStreak::where('fellow_id', $fellow->id)->max('longest_streak'),
        ];
    }
}
