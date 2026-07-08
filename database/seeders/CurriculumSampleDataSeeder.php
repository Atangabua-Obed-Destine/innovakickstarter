<?php

namespace Database\Seeders;

use App\Enums\BadgeType;
use App\Enums\CurriculumStatus;
use App\Models\AccountabilityPair;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds realistic sample data for the curriculum system.
 * 
 * Creates progress records, streaks, accountability pairs, and badges
 * so you can immediately see the system in action across every status.
 * 
 * Scenario: SWE Track, 6 fellows at different stages of their journey.
 * 
 *  Fellow        | Stage Description
 * ───────────────┼──────────────────────────────────────────────
 *  Alex Johnson  | Power user — Milestone 1 done, deep into M2
 *  Sarah Chen    | Just started — M1 halfway, some submitted
 *  Michael Brown | Active reviewer — has peer reviews pending
 *  Emily Davis   | Completed M1, starting M2
 *  David Wilson  | Behind schedule — overdue items, late penalty
 *  Kento Java    | Brand new — just enrolled, everything locked/available
 */
class CurriculumSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Seeding curriculum sample data...');

        // ── Fetch SWE Track & Fellows ──────────────────────────────
        $track = Track::where('slug', 'software-engineering')->firstOrFail();
        $milestones = TrackMilestone::where('track_id', $track->id)
            ->orderBy('sequence_order')
            ->get();

        // Get one cohort for pairs
        $cohort = \App\Models\Cohort::first();

        // Fetch fellows (by integer ID — they're enrolled in SWE)
        $alex    = User::find(2);  // Alex Johnson
        $sarah   = User::find(3);  // Sarah Chen
        $michael = User::find(4);  // Michael Brown
        $emily   = User::find(5);  // Emily Davis
        $david   = User::find(6);  // David Wilson
        $kento   = User::find(11); // Kento Java

        if (!$alex || !$sarah || !$michael || !$emily || !$david || !$kento) {
            $this->command->error('Required fellows not found. Skipping.');
            return;
        }

        // Clean previous sample data (idempotent)
        FellowCurriculumProgress::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->forceDelete();
        FellowStreak::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->delete();
        AccountabilityPair::where('track_id', $track->id)->delete();
        FellowBadge::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->delete();

        // Build lookup: milestone_order => activities[]
        $milestoneActivities = [];
        foreach ($milestones as $m) {
            $milestoneActivities[$m->sequence_order] = TrackCurriculumActivity::where('milestone_id', $m->id)
                ->orderBy('sequence_order')
                ->get();
        }

        $now = Carbon::now();

        // ────────────────────────────────────────────────────────────
        // 1. ALEX JOHNSON — Power User (M1 complete, M2 in progress)
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Alex Johnson: M1 completed, M2 in progress');
        
        // M1: All 3 activities completed
        foreach ($milestoneActivities[1] as $i => $activity) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $alex->id,
                'curriculum_activity_id' => $activity->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->subDays(20 - $i * 3),
                'grace_deadline_at'      => $now->copy()->subDays(17 - $i * 3),
                'started_at'             => $now->copy()->subDays(30 - $i * 3),
                'submitted_at'           => $now->copy()->subDays(22 - $i * 3),
                'completed_at'           => $now->copy()->subDays(21 - $i * 3),
                'reviewed_at'            => $now->copy()->subDays(21 - $i * 3),
                'reviewer_id'            => 1, // admin
                'review_notes'           => 'Great work! Clean implementation.',
                'evidence'               => [
                    ['type' => 'github_url', 'value' => 'https://github.com/alexj/portfolio-scaffold'],
                    ['type' => 'screenshot', 'value' => 'screenshots/milestone1-activity' . ($i + 1) . '.png'],
                ],
                'submission_notes'       => 'Completed with all requirements met.',
                'rubric_scores'          => ['quality' => 9, 'completeness' => 10, 'timeliness' => 9, 'presentation' => 8],
                'score_awarded'          => 88 + $i * 2,
                'points_awarded'         => $activity->points,
                'late_penalty_applied'   => false,
                'attempt_number'         => 1,
            ]);
        }

        // M2: Mixed statuses — 1 completed, 1 under_review, 1 in_progress, 1 available
        $m2Activities = $milestoneActivities[2];
        if ($m2Activities->count() >= 4) {
            // A1: Completed
            FellowCurriculumProgress::create([
                'fellow_id'              => $alex->id,
                'curriculum_activity_id' => $m2Activities[0]->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->addDays(5),
                'grace_deadline_at'      => $now->copy()->addDays(8),
                'started_at'             => $now->copy()->subDays(10),
                'submitted_at'           => $now->copy()->subDays(5),
                'completed_at'           => $now->copy()->subDays(3),
                'reviewed_at'            => $now->copy()->subDays(3),
                'reviewer_id'            => 1,
                'review_notes'           => 'Well-designed REST API. Good error handling.',
                'evidence'               => [
                    ['type' => 'github_url', 'value' => 'https://github.com/alexj/rest-api-project'],
                    ['type' => 'live_url', 'value' => 'https://api.alexj.dev/docs'],
                ],
                'rubric_scores'          => ['quality' => 9, 'completeness' => 9, 'timeliness' => 10, 'presentation' => 8],
                'score_awarded'          => 90,
                'points_awarded'         => $m2Activities[0]->points,
                'attempt_number'         => 1,
            ]);

            // A2: Under Review (submitted, waiting for admin)
            FellowCurriculumProgress::create([
                'fellow_id'              => $alex->id,
                'curriculum_activity_id' => $m2Activities[1]->id,
                'status'                 => CurriculumStatus::UNDER_REVIEW,
                'deadline_at'            => $now->copy()->addDays(10),
                'grace_deadline_at'      => $now->copy()->addDays(13),
                'started_at'             => $now->copy()->subDays(7),
                'submitted_at'           => $now->copy()->subDays(1),
                'evidence'               => [
                    ['type' => 'github_url', 'value' => 'https://github.com/alexj/db-design-challenge'],
                    ['type' => 'document', 'value' => 'ERD-diagram-v2.pdf'],
                ],
                'submission_notes'       => 'Created a normalized schema with 12 tables and wrote all migrations.',
                'attempt_number'         => 1,
            ]);

            // A3: In Progress
            FellowCurriculumProgress::create([
                'fellow_id'              => $alex->id,
                'curriculum_activity_id' => $m2Activities[2]->id,
                'status'                 => CurriculumStatus::IN_PROGRESS,
                'deadline_at'            => $now->copy()->addDays(14),
                'grace_deadline_at'      => $now->copy()->addDays(17),
                'started_at'             => $now->copy()->subDays(2),
                'attempt_number'         => 1,
            ]);

            // A4: Available (not started yet)
            FellowCurriculumProgress::create([
                'fellow_id'              => $alex->id,
                'curriculum_activity_id' => $m2Activities[3]->id,
                'status'                 => CurriculumStatus::AVAILABLE,
                'deadline_at'            => $now->copy()->addDays(21),
                'grace_deadline_at'      => $now->copy()->addDays(24),
                'attempt_number'         => 1,
            ]);
        }

        // M3-M5: Locked for Alex
        foreach ([3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $alex->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // 2. SARAH CHEN — Halfway through M1
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Sarah Chen: M1 halfway through');

        $m1Activities = $milestoneActivities[1];
        if ($m1Activities->count() >= 3) {
            // A1: Completed
            FellowCurriculumProgress::create([
                'fellow_id'              => $sarah->id,
                'curriculum_activity_id' => $m1Activities[0]->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->subDays(5),
                'started_at'             => $now->copy()->subDays(12),
                'submitted_at'           => $now->copy()->subDays(7),
                'completed_at'           => $now->copy()->subDays(6),
                'reviewed_at'            => $now->copy()->subDays(6),
                'reviewer_id'            => 1,
                'review_notes'           => 'Good setup!',
                'evidence'               => [['type' => 'screenshot', 'value' => 'my-dev-env.png']],
                'rubric_scores'          => ['quality' => 8, 'completeness' => 9, 'timeliness' => 10, 'presentation' => 7],
                'score_awarded'          => 82,
                'points_awarded'         => $m1Activities[0]->points,
                'attempt_number'         => 1,
            ]);

            // A2: Submitted (waiting for peer review)
            FellowCurriculumProgress::create([
                'fellow_id'              => $sarah->id,
                'curriculum_activity_id' => $m1Activities[1]->id,
                'status'                 => CurriculumStatus::PEER_REVIEW,
                'deadline_at'            => $now->copy()->addDays(3),
                'grace_deadline_at'      => $now->copy()->addDays(6),
                'started_at'             => $now->copy()->subDays(8),
                'submitted_at'           => $now->copy()->subDays(2),
                'peer_reviewer_id'       => $alex->id,
                'evidence'               => [
                    ['type' => 'github_url', 'value' => 'https://github.com/sarachen/git-challenge'],
                ],
                'submission_notes'       => 'Practiced rebasing, cherry-picking, and conflict resolution.',
                'attempt_number'         => 1,
            ]);

            // A3: In Progress
            FellowCurriculumProgress::create([
                'fellow_id'              => $sarah->id,
                'curriculum_activity_id' => $m1Activities[2]->id,
                'status'                 => CurriculumStatus::IN_PROGRESS,
                'deadline_at'            => $now->copy()->addDays(10),
                'grace_deadline_at'      => $now->copy()->addDays(13),
                'started_at'             => $now->copy()->subDays(1),
                'attempt_number'         => 1,
            ]);
        }

        // M2-M5: Locked for Sarah
        foreach ([2, 3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $sarah->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // 3. MICHAEL BROWN — Has pending peer reviews to give
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Michael Brown: M1 complete, reviewing peers');

        // M1: All completed
        foreach ($milestoneActivities[1] as $i => $activity) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $michael->id,
                'curriculum_activity_id' => $activity->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->subDays(15 - $i * 3),
                'started_at'             => $now->copy()->subDays(25 - $i * 3),
                'submitted_at'           => $now->copy()->subDays(18 - $i * 3),
                'completed_at'           => $now->copy()->subDays(16 - $i * 3),
                'reviewed_at'            => $now->copy()->subDays(16 - $i * 3),
                'reviewer_id'            => 1,
                'review_notes'           => 'Solid work.',
                'evidence'               => [['type' => 'github_url', 'value' => 'https://github.com/mbrown/swe-m1']],
                'rubric_scores'          => ['quality' => 8, 'completeness' => 8, 'timeliness' => 9, 'presentation' => 7],
                'score_awarded'          => 80 + $i * 3,
                'points_awarded'         => $activity->points,
                'attempt_number'         => 1,
            ]);
        }

        // M2: A1 submitted, A2 available, rest locked
        if ($m2Activities->count() >= 4) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $michael->id,
                'curriculum_activity_id' => $m2Activities[0]->id,
                'status'                 => CurriculumStatus::SUBMITTED,
                'deadline_at'            => $now->copy()->addDays(7),
                'grace_deadline_at'      => $now->copy()->addDays(10),
                'started_at'             => $now->copy()->subDays(6),
                'submitted_at'           => $now->copy()->subDays(1),
                'evidence'               => [
                    ['type' => 'github_url', 'value' => 'https://github.com/mbrown/api-challenge'],
                ],
                'submission_notes'       => 'Built a REST API with full CRUD and validation.',
                'attempt_number'         => 1,
            ]);

            FellowCurriculumProgress::create([
                'fellow_id'              => $michael->id,
                'curriculum_activity_id' => $m2Activities[1]->id,
                'status'                 => CurriculumStatus::AVAILABLE,
                'deadline_at'            => $now->copy()->addDays(14),
                'grace_deadline_at'      => $now->copy()->addDays(17),
                'attempt_number'         => 1,
            ]);

            for ($i = 2; $i < $m2Activities->count(); $i++) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $michael->id,
                    'curriculum_activity_id' => $m2Activities[$i]->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // M3-M5: Locked for Michael  
        foreach ([3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $michael->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // 4. EMILY DAVIS — M1 complete, just starting M2
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Emily Davis: M1 complete, starting M2');

        foreach ($milestoneActivities[1] as $i => $activity) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $emily->id,
                'curriculum_activity_id' => $activity->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->subDays(10 - $i * 2),
                'started_at'             => $now->copy()->subDays(20 - $i * 2),
                'submitted_at'           => $now->copy()->subDays(13 - $i * 2),
                'completed_at'           => $now->copy()->subDays(11 - $i * 2),
                'reviewed_at'            => $now->copy()->subDays(11 - $i * 2),
                'reviewer_id'            => 1,
                'review_notes'           => 'Well done, Emily. Keep it up!',
                'evidence'               => [['type' => 'github_url', 'value' => 'https://github.com/emilyd/swe-m1-a' . ($i + 1)]],
                'rubric_scores'          => ['quality' => 9, 'completeness' => 10, 'timeliness' => 10, 'presentation' => 9],
                'score_awarded'          => 92 + $i,
                'points_awarded'         => $activity->points,
                'attempt_number'         => 1,
            ]);
        }

        // M2: A1 available, rest locked
        if ($m2Activities->count() >= 1) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $emily->id,
                'curriculum_activity_id' => $m2Activities[0]->id,
                'status'                 => CurriculumStatus::AVAILABLE,
                'deadline_at'            => $now->copy()->addDays(14),
                'grace_deadline_at'      => $now->copy()->addDays(17),
                'attempt_number'         => 1,
            ]);

            for ($i = 1; $i < $m2Activities->count(); $i++) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $emily->id,
                    'curriculum_activity_id' => $m2Activities[$i]->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // M3-M5: Locked
        foreach ([3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $emily->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // 5. DAVID WILSON — Behind Schedule (has overdue + rejected)
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → David Wilson: Behind schedule, has overdue & rejected');

        if ($milestoneActivities[1]->count() >= 3) {
            // A1: Completed (but late — penalty applied)
            FellowCurriculumProgress::create([
                'fellow_id'              => $david->id,
                'curriculum_activity_id' => $m1Activities[0]->id,
                'status'                 => CurriculumStatus::COMPLETED,
                'deadline_at'            => $now->copy()->subDays(20),
                'grace_deadline_at'      => $now->copy()->subDays(17),
                'started_at'             => $now->copy()->subDays(28),
                'submitted_at'           => $now->copy()->subDays(15), // submitted AFTER deadline
                'completed_at'           => $now->copy()->subDays(14),
                'reviewed_at'            => $now->copy()->subDays(14),
                'reviewer_id'            => 1,
                'review_notes'           => 'Acceptable but submitted late. 10% penalty applied.',
                'evidence'               => [['type' => 'screenshot', 'value' => 'setup-complete.png']],
                'rubric_scores'          => ['quality' => 7, 'completeness' => 8, 'timeliness' => 4, 'presentation' => 6],
                'score_awarded'          => 63,
                'points_awarded'         => (int)($m1Activities[0]->points * 0.9), // 10% penalty
                'late_penalty_applied'   => true,
                'attempt_number'         => 1,
            ]);

            // A2: Rejected (needs to resubmit)
            FellowCurriculumProgress::create([
                'fellow_id'              => $david->id,
                'curriculum_activity_id' => $m1Activities[1]->id,
                'status'                 => CurriculumStatus::REJECTED,
                'deadline_at'            => $now->copy()->subDays(10),
                'grace_deadline_at'      => $now->copy()->subDays(7),
                'started_at'             => $now->copy()->subDays(18),
                'submitted_at'           => $now->copy()->subDays(11),
                'reviewed_at'            => $now->copy()->subDays(9),
                'reviewer_id'            => 1,
                'review_notes'           => 'Missing rebase exercise. Please redo the conflict resolution section and resubmit.',
                'evidence'               => [['type' => 'github_url', 'value' => 'https://github.com/dwilson/git-attempt1']],
                'rubric_scores'          => ['quality' => 5, 'completeness' => 4, 'timeliness' => 6, 'presentation' => 5],
                'score_awarded'          => 0,
                'points_awarded'         => 0,
                'attempt_number'         => 1,
            ]);

            // A3: Overdue (deadline passed, not submitted)
            FellowCurriculumProgress::create([
                'fellow_id'              => $david->id,
                'curriculum_activity_id' => $m1Activities[2]->id,
                'status'                 => CurriculumStatus::OVERDUE,
                'deadline_at'            => $now->copy()->subDays(3),
                'grace_deadline_at'      => $now->copy()->subDay(),
                'started_at'             => $now->copy()->subDays(10),
                'attempt_number'         => 1,
            ]);
        }

        // M2-M5: Locked for David
        foreach ([2, 3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $david->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // 6. KENTO JAVA — Brand New (just enrolled)
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Kento Java: Just enrolled, M1 available');

        // M1: A1 available, A2-A3 locked
        if ($milestoneActivities[1]->count() >= 1) {
            FellowCurriculumProgress::create([
                'fellow_id'              => $kento->id,
                'curriculum_activity_id' => $m1Activities[0]->id,
                'status'                 => CurriculumStatus::AVAILABLE,
                'deadline_at'            => $now->copy()->addDays(14),
                'grace_deadline_at'      => $now->copy()->addDays(17),
                'attempt_number'         => 1,
            ]);

            for ($i = 1; $i < $milestoneActivities[1]->count(); $i++) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $kento->id,
                    'curriculum_activity_id' => $m1Activities[$i]->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // M2-M5: All locked for Kento
        foreach ([2, 3, 4, 5] as $mOrder) {
            if (!isset($milestoneActivities[$mOrder])) continue;
            foreach ($milestoneActivities[$mOrder] as $activity) {
                FellowCurriculumProgress::create([
                    'fellow_id'              => $kento->id,
                    'curriculum_activity_id' => $activity->id,
                    'status'                 => CurriculumStatus::LOCKED,
                    'attempt_number'         => 1,
                ]);
            }
        }

        // ────────────────────────────────────────────────────────────
        // STREAKS
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Creating streaks...');

        FellowStreak::create([
            'fellow_id'           => $alex->id,
            'track_id'            => $track->id,
            'current_streak'      => 4,
            'longest_streak'      => 4,
            'multiplier'          => 1.25,
            'last_completed_week' => $now->copy()->startOfWeek()->subWeek(),
        ]);

        FellowStreak::create([
            'fellow_id'           => $sarah->id,
            'track_id'            => $track->id,
            'current_streak'      => 2,
            'longest_streak'      => 2,
            'multiplier'          => 1.10,
            'last_completed_week' => $now->copy()->startOfWeek()->subWeek(),
        ]);

        FellowStreak::create([
            'fellow_id'           => $michael->id,
            'track_id'            => $track->id,
            'current_streak'      => 3,
            'longest_streak'      => 5,
            'multiplier'          => 1.10,
            'last_completed_week' => $now->copy()->startOfWeek(),
        ]);

        FellowStreak::create([
            'fellow_id'           => $emily->id,
            'track_id'            => $track->id,
            'current_streak'      => 5,
            'longest_streak'      => 5,
            'multiplier'          => 1.25,
            'last_completed_week' => $now->copy()->startOfWeek(),
        ]);

        FellowStreak::create([
            'fellow_id'           => $david->id,
            'track_id'            => $track->id,
            'current_streak'      => 0,
            'longest_streak'      => 1,
            'multiplier'          => 1.00,
            'last_completed_week' => $now->copy()->subWeeks(3),
            'streak_broken_at'    => $now->copy()->subWeeks(2),
        ]);

        FellowStreak::create([
            'fellow_id'           => $kento->id,
            'track_id'            => $track->id,
            'current_streak'      => 0,
            'longest_streak'      => 0,
            'multiplier'          => 1.00,
        ]);

        // ────────────────────────────────────────────────────────────
        // ACCOUNTABILITY PAIRS
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Creating accountability pairs...');

        AccountabilityPair::create([
            'fellow_a_id'        => $alex->id,
            'fellow_b_id'        => $sarah->id,
            'track_id'           => $track->id,
            'cohort_id'          => $cohort?->id,
            'milestone_id'       => $milestones->firstWhere('sequence_order', 1)?->id,
            'is_active'          => true,
            'paired_at'          => $now->copy()->subDays(14),
            'reviews_exchanged'  => 3,
            'bonus_points_earned' => 15,
        ]);

        AccountabilityPair::create([
            'fellow_a_id'        => $michael->id,
            'fellow_b_id'        => $emily->id,
            'track_id'           => $track->id,
            'cohort_id'          => $cohort?->id,
            'milestone_id'       => $milestones->firstWhere('sequence_order', 1)?->id,
            'is_active'          => true,
            'paired_at'          => $now->copy()->subDays(14),
            'reviews_exchanged'  => 2,
            'bonus_points_earned' => 10,
        ]);

        AccountabilityPair::create([
            'fellow_a_id'        => $david->id,
            'fellow_b_id'        => $kento->id,
            'track_id'           => $track->id,
            'cohort_id'          => $cohort?->id,
            'milestone_id'       => $milestones->firstWhere('sequence_order', 1)?->id,
            'is_active'          => true,
            'paired_at'          => $now->copy()->subDays(7),
            'reviews_exchanged'  => 0,
            'bonus_points_earned' => 0,
        ]);

        // ────────────────────────────────────────────────────────────
        // BADGES
        // ────────────────────────────────────────────────────────────
        $this->command->info('  → Creating badges...');

        // Alex: Milestone badge for M1 + Streak badge
        FellowBadge::create([
            'fellow_id'         => $alex->id,
            'badge_type'        => BadgeType::MILESTONE,
            'badge_name'        => 'Foundation Sprint Champion',
            'badge_icon'        => '🏗️',
            'badge_color'       => '#10B981',
            'badge_description' => 'Completed the Foundation Sprint milestone with flying colors!',
            'earned_at'         => $now->copy()->subDays(18),
            'milestone_id'      => $milestones->firstWhere('sequence_order', 1)?->id,
            'track_id'          => $track->id,
            'shareable_url'     => Str::uuid()->toString(),
            'metadata'          => ['avg_score' => 90, 'activities_completed' => 3],
        ]);

        FellowBadge::create([
            'fellow_id'         => $alex->id,
            'badge_type'        => BadgeType::STREAK,
            'badge_name'        => 'On Fire! (4-Week Streak)',
            'badge_icon'        => '🔥',
            'badge_color'       => '#F59E0B',
            'badge_description' => 'Maintained a 4-week activity streak. Keep burning!',
            'earned_at'         => $now->copy()->subDays(3),
            'track_id'          => $track->id,
            'metadata'          => ['streak_weeks' => 4],
        ]);

        // Michael: Milestone badge for M1
        FellowBadge::create([
            'fellow_id'         => $michael->id,
            'badge_type'        => BadgeType::MILESTONE,
            'badge_name'        => 'Foundation Sprint Champion',
            'badge_icon'        => '🏗️',
            'badge_color'       => '#10B981',
            'badge_description' => 'Completed the Foundation Sprint milestone!',
            'earned_at'         => $now->copy()->subDays(14),
            'milestone_id'      => $milestones->firstWhere('sequence_order', 1)?->id,
            'track_id'          => $track->id,
            'shareable_url'     => Str::uuid()->toString(),
            'metadata'          => ['avg_score' => 83, 'activities_completed' => 3],
        ]);

        // Emily: Milestone badge for M1 + Streak badge
        FellowBadge::create([
            'fellow_id'         => $emily->id,
            'badge_type'        => BadgeType::MILESTONE,
            'badge_name'        => 'Foundation Sprint Champion',
            'badge_icon'        => '🏗️',
            'badge_color'       => '#10B981',
            'badge_description' => 'Completed the Foundation Sprint milestone with top marks!',
            'earned_at'         => $now->copy()->subDays(8),
            'milestone_id'      => $milestones->firstWhere('sequence_order', 1)?->id,
            'track_id'          => $track->id,
            'shareable_url'     => Str::uuid()->toString(),
            'metadata'          => ['avg_score' => 93, 'activities_completed' => 3],
        ]);

        FellowBadge::create([
            'fellow_id'         => $emily->id,
            'badge_type'        => BadgeType::STREAK,
            'badge_name'        => 'Unstoppable! (5-Week Streak)',
            'badge_icon'        => '💎',
            'badge_color'       => '#8B5CF6',
            'badge_description' => 'Completed activities for 5 consecutive weeks!',
            'earned_at'         => $now->copy()->subDays(1),
            'track_id'          => $track->id,
            'metadata'          => ['streak_weeks' => 5],
        ]);

        // Peer Champion badge for Alex (reviewed 3 peers)
        FellowBadge::create([
            'fellow_id'         => $alex->id,
            'badge_type'        => BadgeType::PEER_CHAMPION,
            'badge_name'        => 'Peer Champion',
            'badge_icon'        => '🤝',
            'badge_color'       => '#3B82F6',
            'badge_description' => 'Reviewed 3 peer submissions with detailed feedback.',
            'earned_at'         => $now->copy()->subDays(5),
            'track_id'          => $track->id,
            'metadata'          => ['reviews_given' => 3],
        ]);

        // ────────────────────────────────────────────────────────────
        // SUMMARY
        // ────────────────────────────────────────────────────────────
        $progressCount = FellowCurriculumProgress::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->count();
        $streakCount   = FellowStreak::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->count();
        $pairCount     = AccountabilityPair::where('track_id', $track->id)->count();
        $badgeCount    = FellowBadge::whereIn('fellow_id', [2, 3, 4, 5, 6, 11])->count();

        $this->command->newLine();
        $this->command->info('✅ Curriculum sample data seeded successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Progress Records', $progressCount],
                ['Streaks', $streakCount],
                ['Accountability Pairs', $pairCount],
                ['Badges', $badgeCount],
            ]
        );

        $this->command->newLine();
        $this->command->info('📋 Use Cases to Test:');
        $this->command->line('  1. Admin → Tracks → SWE → Manage Curriculum (see milestones & analytics)');
        $this->command->line('  2. Admin → Curriculum Reviews (see pending submissions)');
        $this->command->line('  3. Fellow (Alex) → Curriculum Dashboard (M1 done, M2 in progress)');
        $this->command->line('  4. Fellow (David) → Curriculum Dashboard (overdue + rejected items)');
        $this->command->line('  5. Fellow (Kento) → Curriculum Dashboard (fresh start, M1 available)');
        $this->command->line('  6. Admin → Review Alex\'s M2-A2 submission (under_review status)');
        $this->command->line('  7. Admin → Review Michael\'s M2-A1 submission (submitted status)');
        $this->command->line('  8. Peer Review → Sarah\'s M1-A2 is waiting for Alex to review');
    }
}
