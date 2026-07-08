<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\InterviewSession;
use App\Models\WeeklyProgress;
use App\Models\User;
use App\Models\Track;
use App\Enums\ActivityType;
use App\Enums\ActivityStatus;
use App\Enums\CareerCapitalCategory;
use App\Enums\InterviewType;
use App\Enums\InterviewMode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Demo Data Seeder
 * 
 * Seeds demo activities, interviews, and weekly progress entries
 * for testing and demonstration purposes.
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding demo activities...');
        $this->seedActivities();
        
        $this->command->info('Seeding demo interviews...');
        $this->seedInterviews();
        
        $this->command->info('Seeding demo weekly progress...');
        $this->seedWeeklyProgress();
        
        $this->command->info('Updating fellow scores...');
        $this->updateFellowScores();
        
        $this->command->info('Demo data seeding complete!');
    }

    /**
     * Seed demo activities for all fellows.
     */
    private function seedActivities(): void
    {
        $fellows = User::role('fellow')->with('fellowTracks')->get();
        
        $activityTemplates = [
            [
                'type' => ActivityType::PROJECT,
                'category' => CareerCapitalCategory::TECHNICAL,
                'title' => 'Built a REST API with Laravel',
                'description' => 'Developed a complete REST API with authentication, rate limiting, and comprehensive documentation.',
                'points' => 20,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::BLOG_POST,
                'category' => CareerCapitalCategory::PORTFOLIO,
                'title' => 'Published article on Clean Code principles',
                'description' => 'Wrote a comprehensive guide on writing maintainable code that was featured on Dev.to.',
                'points' => 10,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::CERTIFICATION,
                'category' => CareerCapitalCategory::TECHNICAL,
                'title' => 'AWS Solutions Architect Associate',
                'description' => 'Passed the AWS SAA-C03 certification exam with a score of 850/1000.',
                'points' => 25,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::HACKATHON,
                'category' => CareerCapitalCategory::COLLABORATION,
                'title' => 'Participated in Google DevFest Hackathon',
                'description' => 'Led a team of 4 to build a sustainable farming app. Won 2nd place.',
                'points' => 30,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::OPEN_SOURCE,
                'category' => CareerCapitalCategory::TECHNICAL,
                'title' => 'Contributed to Laravel Framework',
                'description' => 'Fixed a bug in the Eloquent ORM. PR was merged into main branch.',
                'points' => 15,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::MENTORING,
                'category' => CareerCapitalCategory::LEARNING,
                'title' => 'Mentored junior developers',
                'description' => 'Conducted 5 mentoring sessions with 3 junior developers on best practices.',
                'points' => 15,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::PROJECT,
                'category' => CareerCapitalCategory::PORTFOLIO,
                'title' => 'E-commerce Platform Development',
                'description' => 'Building a full-stack e-commerce platform with Vue.js frontend.',
                'points' => 0,
                'status' => ActivityStatus::PENDING,
            ],
            [
                'type' => ActivityType::WORKSHOP,
                'category' => CareerCapitalCategory::LEARNING,
                'title' => 'Docker & Kubernetes Workshop',
                'description' => 'Attended an intensive 2-day workshop on containerization.',
                'points' => 10,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::SPEAKING,
                'category' => CareerCapitalCategory::PORTFOLIO,
                'title' => 'Tech Talk at Local Meetup',
                'description' => 'Gave a presentation on microservices architecture to 50+ developers.',
                'points' => 18,
                'status' => ActivityStatus::APPROVED,
            ],
            [
                'type' => ActivityType::CODE_REVIEW,
                'category' => CareerCapitalCategory::COLLABORATION,
                'title' => 'Reviewed team PRs',
                'description' => 'Conducted thorough code reviews for 10 pull requests this month.',
                'points' => 8,
                'status' => ActivityStatus::APPROVED,
            ],
        ];
        
        $count = 0;
        foreach ($fellows as $fellow) {
            // Get fellow's primary track
            $fellowTrack = $fellow->fellowTracks->where('is_primary', true)->first();
            if (!$fellowTrack) continue;
            
            // Assign 3-7 random activities to each fellow
            $numActivities = rand(3, 7);
            $selectedTemplates = collect($activityTemplates)->shuffle()->take($numActivities);
            
            foreach ($selectedTemplates as $index => $template) {
                $submittedAt = Carbon::now()->subDays(rand(1, 60));
                
                Activity::create([
                    'fellow_id' => $fellow->id,
                    'track_id' => $fellowTrack->track_id,
                    'type' => $template['type'],
                    'category' => $template['category'],
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'points_earned' => $template['points'],
                    'points_requested' => $template['points'] > 0 ? $template['points'] : rand(10, 25),
                    'status' => $template['status'],
                    'submitted_at' => $submittedAt,
                    'reviewed_at' => $template['status'] !== ActivityStatus::PENDING ? $submittedAt->addDays(rand(1, 3)) : null,
                    'approved_at' => $template['status'] === ActivityStatus::APPROVED ? $submittedAt->addDays(rand(1, 3)) : null,
                    'is_public' => true,
                    'is_featured' => rand(1, 10) > 8,
                ]);
                $count++;
            }
        }
        
        $this->command->info("  Created {$count} activities");
    }

    /**
     * Seed demo interview sessions.
     */
    private function seedInterviews(): void
    {
        $fellows = User::role('fellow')->with('fellowTracks')->get();
        $mentors = User::role('mentor')->get();
        
        $interviewTypes = [
            InterviewType::BEHAVIORAL,
            InterviewType::TECHNICAL_CODING,
            InterviewType::SYSTEM_DESIGN,
            InterviewType::PRODUCT_CASE,
        ];
        
        $count = 0;
        foreach ($fellows as $fellow) {
            $fellowTrack = $fellow->fellowTracks->where('is_primary', true)->first();
            if (!$fellowTrack) continue;
            
            // Create 1-3 completed interviews
            $numCompleted = rand(1, 3);
            for ($i = 0; $i < $numCompleted; $i++) {
                $scheduledAt = Carbon::now()->subDays(rand(5, 30));
                $mentor = $mentors->random();
                
                InterviewSession::create([
                    'fellow_id' => $fellow->id,
                    'track_id' => $fellowTrack->track_id,
                    'interviewer_id' => rand(1, 10) > 5 ? $mentor->id : null,
                    'type' => $interviewTypes[array_rand($interviewTypes)],
                    'mode' => rand(1, 10) > 6 ? InterviewMode::HUMAN : InterviewMode::AI,
                    'status' => 'completed',
                    'scheduled_at' => $scheduledAt,
                    'started_at' => $scheduledAt,
                    'completed_at' => $scheduledAt->addMinutes(rand(25, 45)),
                    'duration_minutes' => rand(30, 45),
                    'score' => rand(60, 95),
                    'ai_feedback' => 'Good performance overall. Consider working on time management and structured responses.',
                    'interviewer_notes' => 'Candidate showed strong problem-solving skills. Communication was clear.',
                ]);
                $count++;
            }
            
            // Create 0-2 upcoming interviews for some fellows
            if (rand(1, 10) > 6) {
                $numUpcoming = rand(1, 2);
                for ($i = 0; $i < $numUpcoming; $i++) {
                    InterviewSession::create([
                        'fellow_id' => $fellow->id,
                        'track_id' => $fellowTrack->track_id,
                        'type' => $interviewTypes[array_rand($interviewTypes)],
                        'mode' => rand(1, 10) > 5 ? InterviewMode::AI : InterviewMode::HUMAN,
                        'status' => 'scheduled',
                        'scheduled_at' => Carbon::now()->addDays(rand(1, 14))->setHour(rand(9, 17)),
                        'duration_minutes' => 30,
                    ]);
                    $count++;
                }
            }
        }
        
        $this->command->info("  Created {$count} interview sessions");
    }

    /**
     * Seed demo weekly progress entries.
     */
    private function seedWeeklyProgress(): void
    {
        $fellows = User::role('fellow')->with('fellowTracks')->get();
        
        $count = 0;
        foreach ($fellows as $fellow) {
            $fellowTrack = $fellow->fellowTracks->where('is_primary', true)->first();
            if (!$fellowTrack) continue;
            
            // Create 2-6 weekly progress entries
            $numEntries = rand(2, 6);
            
            for ($i = 0; $i < $numEntries; $i++) {
                $weekStart = Carbon::now()->subWeeks($i + 1)->startOfWeek();
                $allComplete = rand(1, 10) > 3; // 70% chance of full completion
                
                $buildPoints = $allComplete ? rand(10, 20) : rand(0, 10);
                $brandPoints = $allComplete ? rand(8, 15) : rand(0, 8);
                $interviewPoints = $allComplete ? rand(10, 15) : rand(0, 10);
                $collaboratePoints = $allComplete ? rand(5, 12) : rand(0, 5);
                
                DB::table('weekly_progress')->insert([
                    'id' => (string) Str::uuid(),
                    'fellow_id' => $fellow->id,
                    'track_id' => $fellowTrack->track_id,
                    'week_start' => $weekStart,
                    'week_end' => $weekStart->copy()->endOfWeek(),
                    'year' => $weekStart->year,
                    'week_number' => $weekStart->weekOfYear,
                    'build_completed' => $allComplete || rand(0, 1),
                    'brand_completed' => $allComplete || rand(0, 1),
                    'interview_completed' => $allComplete || rand(0, 1),
                    'collaborate_completed' => $allComplete || rand(0, 1),
                    'build_points' => $buildPoints,
                    'brand_points' => $brandPoints,
                    'interview_points' => $interviewPoints,
                    'collaborate_points' => $collaboratePoints,
                    'total_points' => $buildPoints + $brandPoints + $interviewPoints + $collaboratePoints,
                    'all_pillars_completed' => $allComplete,
                    'score_frozen' => !$allComplete && rand(1, 10) > 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }
        
        $this->command->info("  Created {$count} weekly progress entries");
    }

    /**
     * Update fellow scores based on activities.
     */
    private function updateFellowScores(): void
    {
        $fellowTracks = DB::table('fellow_tracks')->get();
        
        foreach ($fellowTracks as $ft) {
            // Calculate total approved points
            $totalPoints = Activity::where('fellow_id', $ft->fellow_id)
                ->where('track_id', $ft->track_id)
                ->where('status', ActivityStatus::APPROVED)
                ->sum('points_earned');
            
            // Calculate interview score
            $avgInterviewScore = InterviewSession::where('fellow_id', $ft->fellow_id)
                ->where('status', 'completed')
                ->whereNotNull('score')
                ->avg('score') ?? 0;
            
            // Calculate composite score (simplified)
            $technicalScore = min(100, $totalPoints * 2);
            $interviewScore = $avgInterviewScore;
            $compositeScore = round(($technicalScore * 0.6) + ($interviewScore * 0.4));
            
            // Determine tier
            $tier = match(true) {
                $compositeScore >= 75 => 'elite',
                $compositeScore >= 50 => 'professional',
                $compositeScore >= 25 => 'intern',
                default => 'rookie',
            };
            
            DB::table('fellow_tracks')
                ->where('id', $ft->id)
                ->update([
                    'score' => $compositeScore,
                    'tier' => $tier,
                    'technical_score' => $technicalScore,
                    'interview_score' => round($interviewScore),
                    'total_points_earned' => $totalPoints,
                    'last_active_at' => now(),
                    'updated_at' => now(),
                ]);
        }
        
        $this->command->info("  Updated scores for " . count($fellowTracks) . " fellow-track relationships");
    }
}
