<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Enums\DifficultyLevel;
use App\Enums\EvidenceType;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Curriculum Template Seeder
 * 
 * Seeds the structured curriculum system with milestones and activities
 * for select tracks. Provides a rich, real-world curriculum progression.
 * Currently seeds: Software Engineering (full), Data Science (full).
 * 
 * Run standalone: php artisan db:seed --class=CurriculumTemplateSeeder
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CurriculumTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Seeding Curriculum Templates...');

        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $adminId = $admin?->id;

        $this->seedSoftwareEngineering($adminId);
        $this->seedDataScience($adminId);

        $this->command->info('✅ Curriculum templates seeded.');
    }

    // ==========================================
    // SOFTWARE ENGINEERING TRACK
    // ==========================================

    protected function seedSoftwareEngineering(?string $adminId): void
    {
        $track = Track::where('slug', 'software-engineering')->first();
        if (!$track) {
            $this->command->warn('  ⏭ Software Engineering track not found, skipping.');
            return;
        }

        // Check if already seeded
        if (TrackMilestone::where('track_id', $track->id)->exists()) {
            $this->command->info('  ⏭ Software Engineering curriculum already exists, skipping.');
            return;
        }

        $this->command->info('  📚 Seeding Software Engineering curriculum...');

        // --- Milestone 1: Foundation Sprint ---
        $m1 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Foundation Sprint',
            'description' => 'Build your developer toolkit: environment setup, Git mastery, and your first project scaffold. Prove you can ship working code from day one.',
            'sequence_order' => 1,
            'is_required' => true,
            'estimated_duration_days' => 14,
            'badge_name' => 'Foundation Builder',
            'badge_icon' => '🏗️',
            'badge_color' => '#3B82F6',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m1, [
            'title' => 'Dev Environment & Toolchain Setup',
            'description' => 'Set up a professional development environment: IDE configuration, terminal customization, package managers, and essential tools. Document your setup as a reproducible guide.',
            'instructions' => "1. Install and configure VS Code (or your preferred IDE) with essential extensions\n2. Set up Git with SSH keys and GPG signing\n3. Install Node.js (via nvm), PHP 8.2+, Composer\n4. Configure terminal (Oh My Zsh, Starship, etc.)\n5. Write a README.md documenting your full setup\n6. Push to a public GitHub repo",
            'activity_type' => ActivityType::DOCUMENTATION,
            'difficulty_level' => DifficultyLevel::BEGINNER,
            'base_points' => 50,
            'order' => 1,
            'estimated_hours' => 3,
            'deadline_days' => 3,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
            'evaluation_rubric' => [
                ['criterion' => 'Completeness', 'description' => 'All required tools installed and configured', 'weight' => 40],
                ['criterion' => 'Documentation', 'description' => 'Clear, reproducible setup guide', 'weight' => 35],
                ['criterion' => 'Professionalism', 'description' => 'Git config, SSH keys, IDE extensions', 'weight' => 25],
            ],
        ], $adminId);

        $this->createActivity($track, $m1, [
            'title' => 'Git Workflow Mastery Challenge',
            'description' => 'Demonstrate Git proficiency by completing a branching challenge: feature branches, merge conflicts, rebasing, and a clean git history.',
            'instructions' => "1. Fork the provided challenge repo\n2. Create feature branches for 3 tasks\n3. Intentionally create and resolve a merge conflict\n4. Use interactive rebase to squash commits\n5. Write meaningful commit messages following Conventional Commits\n6. Create a pull request with a descriptive summary",
            'activity_type' => ActivityType::COMPETITION,
            'difficulty_level' => DifficultyLevel::BEGINNER,
            'base_points' => 75,
            'order' => 2,
            'estimated_hours' => 2,
            'deadline_days' => 4,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Git History', 'description' => 'Clean, logical commit history', 'weight' => 30],
                ['criterion' => 'Branching', 'description' => 'Proper use of feature branches', 'weight' => 25],
                ['criterion' => 'Conflict Resolution', 'description' => 'Successfully resolved merge conflict', 'weight' => 25],
                ['criterion' => 'Commit Messages', 'description' => 'Clear, conventional commit messages', 'weight' => 20],
            ],
        ], $adminId);

        $this->createActivity($track, $m1, [
            'title' => 'Personal Portfolio — Project Scaffold',
            'description' => 'Scaffold a personal portfolio website using a modern framework. Deploy it live. This will be your evolving showcase throughout the program.',
            'instructions' => "1. Choose a framework (Next.js, Nuxt, Laravel, etc.)\n2. Create project with proper structure\n3. Add: Hero section, About, Projects (empty), Contact\n4. Implement responsive design\n5. Deploy to Vercel, Netlify, or similar\n6. Post your live URL",
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 150,
            'bonus_points' => 30,
            'order' => 3,
            'estimated_hours' => 8,
            'deadline_days' => 7,
            'grace_period_days' => 2,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::URL->value, EvidenceType::SCREENSHOT->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Code Quality', 'description' => 'Clean, well-structured code', 'weight' => 25],
                ['criterion' => 'Design', 'description' => 'Professional, responsive design', 'weight' => 25],
                ['criterion' => 'Deployment', 'description' => 'Successfully deployed and accessible', 'weight' => 25],
                ['criterion' => 'Content', 'description' => 'Meaningful sections, good copy', 'weight' => 25],
            ],
        ], $adminId);

        // --- Milestone 2: Backend Mastery ---
        $m2 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Backend Mastery',
            'description' => 'Deep dive into server-side engineering: RESTful APIs, databases, authentication, and testing. Build production-ready backend services.',
            'sequence_order' => 2,
            'is_required' => true,
            'estimated_duration_days' => 21,
            'unlock_after_milestone_id' => $m1->id,
            'badge_name' => 'Backend Architect',
            'badge_icon' => '⚡',
            'badge_color' => '#10B981',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m2, [
            'title' => 'REST API Design & Implementation',
            'description' => 'Design and build a fully functional RESTful API with CRUD operations, validation, error handling, and proper HTTP status codes.',
            'instructions' => "1. Choose a domain (e.g., Task Manager, Bookstore, Recipe API)\n2. Design endpoints following REST conventions\n3. Implement CRUD operations\n4. Add request validation\n5. Implement proper error handling with meaningful messages\n6. Use proper HTTP status codes\n7. Write API documentation (Swagger/OpenAPI or README)\n8. Add pagination for list endpoints",
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 150,
            'order' => 1,
            'estimated_hours' => 10,
            'deadline_days' => 7,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
            'evaluation_rubric' => [
                ['criterion' => 'API Design', 'description' => 'RESTful conventions, proper routes', 'weight' => 30],
                ['criterion' => 'Implementation', 'description' => 'Working CRUD, validation, errors', 'weight' => 30],
                ['criterion' => 'Documentation', 'description' => 'Clear API docs, usage examples', 'weight' => 20],
                ['criterion' => 'Code Quality', 'description' => 'Clean code, proper patterns', 'weight' => 20],
            ],
        ], $adminId);

        $this->createActivity($track, $m2, [
            'title' => 'Database Design & Migrations',
            'description' => 'Design a normalized database schema with relationships, write migrations, and implement an efficient query layer.',
            'activity_type' => ActivityType::COMPETITION,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 100,
            'order' => 2,
            'estimated_hours' => 5,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
            'evaluation_rubric' => [
                ['criterion' => 'Schema Design', 'description' => 'Normalized, proper relationships', 'weight' => 40],
                ['criterion' => 'Migrations', 'description' => 'Reversible, well-structured', 'weight' => 30],
                ['criterion' => 'Query Efficiency', 'description' => 'Indexes, eager loading', 'weight' => 30],
            ],
        ], $adminId);

        $this->createActivity($track, $m2, [
            'title' => 'Authentication & Authorization System',
            'description' => 'Implement a complete auth system with registration, login, role-based access control, and API token management.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 175,
            'order' => 3,
            'estimated_hours' => 8,
            'deadline_days' => 7,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Security', 'description' => 'Hashed passwords, token handling, CSRF', 'weight' => 35],
                ['criterion' => 'RBAC', 'description' => 'Proper role/permission implementation', 'weight' => 30],
                ['criterion' => 'UX', 'description' => 'Good auth flow, error messages', 'weight' => 20],
                ['criterion' => 'Testing', 'description' => 'Auth tests coverage', 'weight' => 15],
            ],
        ], $adminId);

        $this->createActivity($track, $m2, [
            'title' => 'Write a Technical Blog Post',
            'description' => 'Write and publish a technical blog post about a backend concept you learned. Teach what you know to solidify understanding.',
            'activity_type' => ActivityType::LINKEDIN_POST,
            'difficulty_level' => DifficultyLevel::BEGINNER,
            'base_points' => 60,
            'bonus_points' => 20,
            'order' => 4,
            'estimated_hours' => 3,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::URL->value, EvidenceType::SOCIAL_POST->value],
            'evaluation_rubric' => [
                ['criterion' => 'Technical Depth', 'description' => 'Accurate, insightful content', 'weight' => 40],
                ['criterion' => 'Clarity', 'description' => 'Well-written, easy to follow', 'weight' => 30],
                ['criterion' => 'Engagement', 'description' => 'Published publicly, shareable', 'weight' => 30],
            ],
        ], $adminId);

        // --- Milestone 3: Full-Stack Integration ---
        $m3 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Full-Stack Integration',
            'description' => 'Connect frontend and backend into a cohesive application. Master state management, API integration, and real-time features.',
            'sequence_order' => 3,
            'is_required' => true,
            'estimated_duration_days' => 21,
            'unlock_after_milestone_id' => $m2->id,
            'badge_name' => 'Full-Stack Warrior',
            'badge_icon' => '🗡️',
            'badge_color' => '#8B5CF6',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m3, [
            'title' => 'Full-Stack CRUD Application',
            'description' => 'Build a complete full-stack application with frontend consuming your API. Include state management, form handling, and loading states.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 200,
            'bonus_points' => 50,
            'order' => 1,
            'estimated_hours' => 15,
            'deadline_days' => 10,
            'grace_period_days' => 3,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::URL->value, EvidenceType::VIDEO->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Integration', 'description' => 'Seamless frontend-backend communication', 'weight' => 25],
                ['criterion' => 'UX/UI', 'description' => 'Loading states, error handling, responsive', 'weight' => 25],
                ['criterion' => 'Code Architecture', 'description' => 'Clean separation, reusable components', 'weight' => 25],
                ['criterion' => 'Functionality', 'description' => 'All CRUD operations working', 'weight' => 25],
            ],
        ], $adminId);

        $this->createActivity($track, $m3, [
            'title' => 'Debug Challenge: Fix the Broken App',
            'description' => 'Given a full-stack app with 10 intentional bugs (5 frontend, 5 backend), find and fix them all. Document each bug and your fix.',
            'activity_type' => ActivityType::DEBUG_CHALLENGE,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 120,
            'order' => 2,
            'estimated_hours' => 4,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::TEXT->value],
            'evaluation_rubric' => [
                ['criterion' => 'Bugs Found', 'description' => 'Number of bugs correctly identified', 'weight' => 40],
                ['criterion' => 'Fix Quality', 'description' => 'Proper, non-hacky fixes', 'weight' => 35],
                ['criterion' => 'Documentation', 'description' => 'Clear explanation of each bug/fix', 'weight' => 25],
            ],
        ], $adminId);

        $this->createActivity($track, $m3, [
            'title' => 'Peer Teaching Session',
            'description' => 'Prepare and deliver a 15-minute teaching session to your accountability partner on a topic from the curriculum.',
            'activity_type' => ActivityType::PEER_TEACHING,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 80,
            'order' => 3,
            'estimated_hours' => 3,
            'deadline_days' => 7,
            'collaboration_type' => 'pair',
            'evidence_types' => [EvidenceType::VIDEO->value, EvidenceType::PRESENTATION->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Content Accuracy', 'description' => 'Technically correct explanations', 'weight' => 30],
                ['criterion' => 'Teaching Quality', 'description' => 'Clear, engaging delivery', 'weight' => 30],
                ['criterion' => 'Preparation', 'description' => 'Slides, code demos, examples', 'weight' => 25],
                ['criterion' => 'Q&A Handling', 'description' => 'Answered questions effectively', 'weight' => 15],
            ],
        ], $adminId);

        // --- Milestone 4: Interview Readiness ---
        $m4 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Interview Readiness',
            'description' => 'Prepare for technical interviews: algorithms, system design, behavioral questions, and mock interviews with real feedback.',
            'sequence_order' => 4,
            'is_required' => true,
            'estimated_duration_days' => 14,
            'unlock_after_milestone_id' => $m3->id,
            'badge_name' => 'Interview Ready',
            'badge_icon' => '🎯',
            'badge_color' => '#EF4444',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m4, [
            'title' => 'Algorithm Challenge Sprint',
            'description' => 'Solve 10 curated algorithm problems across different categories: arrays, strings, trees, graphs, dynamic programming.',
            'activity_type' => ActivityType::COMPETITION,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 150,
            'order' => 1,
            'estimated_hours' => 10,
            'deadline_days' => 7,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value, EvidenceType::TEXT->value],
            'evaluation_rubric' => [
                ['criterion' => 'Problems Solved', 'description' => 'At least 8/10 correct solutions', 'weight' => 40],
                ['criterion' => 'Code Quality', 'description' => 'Clean, readable solutions', 'weight' => 20],
                ['criterion' => 'Time Complexity', 'description' => 'Optimal or near-optimal solutions', 'weight' => 25],
                ['criterion' => 'Explanations', 'description' => 'Approach documented for each', 'weight' => 15],
            ],
        ], $adminId);

        $this->createActivity($track, $m4, [
            'title' => 'System Design Case Study',
            'description' => 'Write a system design document for a real-world system (e.g., URL shortener, chat app, social feed). Include architecture diagrams.',
            'activity_type' => ActivityType::CASE_STUDY,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 130,
            'order' => 2,
            'estimated_hours' => 6,
            'deadline_days' => 7,
            'evidence_types' => [EvidenceType::TEXT->value, EvidenceType::FILE_UPLOAD->value],
            'evaluation_rubric' => [
                ['criterion' => 'Architecture', 'description' => 'Sound design decisions', 'weight' => 30],
                ['criterion' => 'Scalability', 'description' => 'Addresses scale, bottlenecks', 'weight' => 25],
                ['criterion' => 'Diagrams', 'description' => 'Clear architecture diagrams', 'weight' => 20],
                ['criterion' => 'Trade-offs', 'description' => 'Discusses pros/cons of choices', 'weight' => 25],
            ],
        ], $adminId);

        $this->createActivity($track, $m4, [
            'title' => 'Mock Technical Interview',
            'description' => 'Complete a mock technical interview: 45 min coding + 15 min behavioral. Use the platform interview module or external service.',
            'activity_type' => ActivityType::MOCK_INTERVIEW,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 100,
            'order' => 3,
            'estimated_hours' => 2,
            'deadline_days' => 7,
            'evidence_types' => [EvidenceType::INTERVIEW_SESSION->value, EvidenceType::TEXT->value],
            'interview_config' => [
                'type' => 'technical_coding',
                'mode' => 'ai',
                'min_score' => 70,
                'count' => 2,
                'difficulty' => 'intermediate',
            ],
            'evaluation_rubric' => [
                ['criterion' => 'Problem Solving', 'description' => 'Approach, communication, solution', 'weight' => 35],
                ['criterion' => 'Communication', 'description' => 'Clear thought process', 'weight' => 25],
                ['criterion' => 'Code Quality', 'description' => 'Working, clean code', 'weight' => 25],
                ['criterion' => 'Behavioral', 'description' => 'Professional, STAR format', 'weight' => 15],
            ],
        ], $adminId);

        // --- Milestone 5: Capstone Project ---
        $m5 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Capstone Project',
            'description' => 'Your flagship project: a production-quality application that demonstrates everything you\'ve learned. This is your portfolio centerpiece.',
            'sequence_order' => 5,
            'is_required' => true,
            'estimated_duration_days' => 28,
            'unlock_after_milestone_id' => $m4->id,
            'badge_name' => 'SWE Track Champion',
            'badge_icon' => '🏆',
            'badge_color' => '#F59E0B',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m5, [
            'title' => 'Capstone Proposal & Architecture',
            'description' => 'Submit a capstone project proposal with problem statement, tech stack, architecture plan, and timeline. Get mentor approval before building.',
            'activity_type' => ActivityType::RESEARCH,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 80,
            'order' => 1,
            'estimated_hours' => 4,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::TEXT->value, EvidenceType::FILE_UPLOAD->value],
            'evaluation_rubric' => [
                ['criterion' => 'Problem Statement', 'description' => 'Clear, meaningful problem', 'weight' => 25],
                ['criterion' => 'Technical Plan', 'description' => 'Appropriate tech stack, architecture', 'weight' => 35],
                ['criterion' => 'Feasibility', 'description' => 'Achievable in the timeline', 'weight' => 20],
                ['criterion' => 'Impact', 'description' => 'Demonstrates growth and ambition', 'weight' => 20],
            ],
        ], $adminId);

        $this->createActivity($track, $m5, [
            'title' => 'Capstone Implementation',
            'description' => 'Build your capstone project. Deploy it live. Write comprehensive documentation. This is the culmination of your journey.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::EXPERT,
            'base_points' => 400,
            'bonus_points' => 100,
            'order' => 2,
            'estimated_hours' => 40,
            'deadline_days' => 21,
            'grace_period_days' => 5,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::URL->value, EvidenceType::VIDEO->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Functionality', 'description' => 'Feature-complete, working app', 'weight' => 25],
                ['criterion' => 'Code Quality', 'description' => 'Clean architecture, patterns', 'weight' => 20],
                ['criterion' => 'Deployment', 'description' => 'Live, accessible, CI/CD', 'weight' => 15],
                ['criterion' => 'Documentation', 'description' => 'README, API docs, setup guide', 'weight' => 15],
                ['criterion' => 'UI/UX', 'description' => 'Professional, responsive design', 'weight' => 15],
                ['criterion' => 'Innovation', 'description' => 'Creative, goes beyond basics', 'weight' => 10],
            ],
        ], $adminId);

        $this->createActivity($track, $m5, [
            'title' => 'Capstone Presentation & Demo',
            'description' => 'Present your capstone project in a 10-minute demo video or live session. Showcase features, architecture decisions, and learnings.',
            'activity_type' => ActivityType::PRESENTATION,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 100,
            'order' => 3,
            'estimated_hours' => 3,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::VIDEO->value, EvidenceType::PRESENTATION->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Demo Quality', 'description' => 'Smooth, well-rehearsed demo', 'weight' => 30],
                ['criterion' => 'Technical Depth', 'description' => 'Architecture, challenges, decisions', 'weight' => 30],
                ['criterion' => 'Storytelling', 'description' => 'Problem→Solution narrative', 'weight' => 20],
                ['criterion' => 'Professionalism', 'description' => 'Polished, confident delivery', 'weight' => 20],
            ],
        ], $adminId);

        $total = TrackCurriculumActivity::where('track_id', $track->id)->count();
        $this->command->info("    ✓ Software Engineering: 5 milestones, {$total} activities");
    }

    // ==========================================
    // DATA SCIENCE TRACK
    // ==========================================

    protected function seedDataScience(?string $adminId): void
    {
        $track = Track::where('slug', 'data-science')->first();
        if (!$track) {
            $this->command->warn('  ⏭ Data Science track not found, skipping.');
            return;
        }

        if (TrackMilestone::where('track_id', $track->id)->exists()) {
            $this->command->info('  ⏭ Data Science curriculum already exists, skipping.');
            return;
        }

        $this->command->info('  📊 Seeding Data Science curriculum...');

        // --- Milestone 1: Data Foundations ---
        $m1 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Data Foundations',
            'description' => 'Master the fundamentals: Python for data science, statistics, data wrangling with pandas, and visualization with matplotlib/seaborn.',
            'sequence_order' => 1,
            'is_required' => true,
            'estimated_duration_days' => 14,
            'badge_name' => 'Data Explorer',
            'badge_icon' => '🔍',
            'badge_color' => '#06B6D4',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m1, [
            'title' => 'Python Data Science Environment',
            'description' => 'Set up Jupyter notebooks, install key libraries (pandas, numpy, matplotlib, seaborn, scikit-learn), and complete a getting-started notebook.',
            'activity_type' => ActivityType::DOCUMENTATION,
            'difficulty_level' => DifficultyLevel::BEGINNER,
            'base_points' => 40,
            'order' => 1,
            'estimated_hours' => 2,
            'deadline_days' => 3,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::SCREENSHOT->value],
        ], $adminId);

        $this->createActivity($track, $m1, [
            'title' => 'Exploratory Data Analysis (EDA) Project',
            'description' => 'Perform a complete EDA on a real-world dataset: cleaning, statistics, visualizations, and insights. Present findings in a Jupyter notebook.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 120,
            'order' => 2,
            'estimated_hours' => 8,
            'deadline_days' => 7,
            'grace_period_days' => 2,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::FILE_UPLOAD->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Data Cleaning', 'description' => 'Handled missing values, outliers, types', 'weight' => 25],
                ['criterion' => 'Analysis Depth', 'description' => 'Statistical insights, correlations', 'weight' => 30],
                ['criterion' => 'Visualizations', 'description' => 'Clear, informative charts', 'weight' => 25],
                ['criterion' => 'Narrative', 'description' => 'Story told through the notebook', 'weight' => 20],
            ],
        ], $adminId);

        $this->createActivity($track, $m1, [
            'title' => 'Statistics & Probability Challenge',
            'description' => 'Complete a set of statistics problems covering descriptive stats, distributions, hypothesis testing, and confidence intervals.',
            'activity_type' => ActivityType::COMPETITION,
            'difficulty_level' => DifficultyLevel::INTERMEDIATE,
            'base_points' => 80,
            'order' => 3,
            'estimated_hours' => 4,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::TEXT->value],
        ], $adminId);

        // --- Milestone 2: Machine Learning ---
        $m2 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Machine Learning Essentials',
            'description' => 'Build, train, and evaluate ML models: regression, classification, clustering. Learn feature engineering and model selection.',
            'sequence_order' => 2,
            'is_required' => true,
            'estimated_duration_days' => 21,
            'unlock_after_milestone_id' => $m1->id,
            'badge_name' => 'ML Practitioner',
            'badge_icon' => '🤖',
            'badge_color' => '#8B5CF6',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m2, [
            'title' => 'Supervised Learning: Prediction Challenge',
            'description' => 'Build a supervised ML model (regression or classification) on a dataset. Evaluate with proper metrics, cross-validation, and comparison.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 175,
            'order' => 1,
            'estimated_hours' => 12,
            'deadline_days' => 10,
            'grace_period_days' => 3,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::FILE_UPLOAD->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Feature Engineering', 'description' => 'Meaningful feature creation', 'weight' => 25],
                ['criterion' => 'Model Selection', 'description' => 'Multiple models compared', 'weight' => 25],
                ['criterion' => 'Evaluation', 'description' => 'Proper metrics, cross-validation', 'weight' => 25],
                ['criterion' => 'Interpretation', 'description' => 'Feature importance, explanations', 'weight' => 25],
            ],
        ], $adminId);

        $this->createActivity($track, $m2, [
            'title' => 'Data Science Blog Post',
            'description' => 'Write about an ML concept, explaining it visually and intuitively. Publish on Medium, LinkedIn, or a personal blog.',
            'activity_type' => ActivityType::LINKEDIN_POST,
            'difficulty_level' => DifficultyLevel::BEGINNER,
            'base_points' => 60,
            'order' => 2,
            'estimated_hours' => 3,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::URL->value, EvidenceType::SOCIAL_POST->value],
        ], $adminId);

        // --- Milestone 3: Data Science Capstone ---
        $m3 = TrackMilestone::create([
            'track_id' => $track->id,
            'title' => 'Data Science Capstone',
            'description' => 'End-to-end data science project: problem framing, data collection, modeling, evaluation, deployment, and presentation.',
            'sequence_order' => 3,
            'is_required' => true,
            'estimated_duration_days' => 28,
            'unlock_after_milestone_id' => $m2->id,
            'badge_name' => 'Data Science Champion',
            'badge_icon' => '🏆',
            'badge_color' => '#F59E0B',
            'created_by' => $adminId,
        ]);

        $this->createActivity($track, $m3, [
            'title' => 'Capstone: End-to-End ML Pipeline',
            'description' => 'Build a complete ML project with data pipeline, model training, deployment (Streamlit/Flask app), and documentation.',
            'activity_type' => ActivityType::PROJECT,
            'difficulty_level' => DifficultyLevel::EXPERT,
            'base_points' => 350,
            'bonus_points' => 75,
            'order' => 1,
            'estimated_hours' => 35,
            'deadline_days' => 21,
            'grace_period_days' => 5,
            'evidence_types' => [EvidenceType::GITHUB_REPO->value, EvidenceType::URL->value, EvidenceType::VIDEO->value],
            'requires_peer_review' => true,
            'evaluation_rubric' => [
                ['criterion' => 'Problem Framing', 'description' => 'Clear, impactful problem', 'weight' => 15],
                ['criterion' => 'Data Pipeline', 'description' => 'Collection, cleaning, feature eng.', 'weight' => 20],
                ['criterion' => 'Modeling', 'description' => 'Multiple models, proper evaluation', 'weight' => 25],
                ['criterion' => 'Deployment', 'description' => 'Live app or API', 'weight' => 20],
                ['criterion' => 'Presentation', 'description' => 'Clear narrative, visualizations', 'weight' => 20],
            ],
        ], $adminId);

        $this->createActivity($track, $m3, [
            'title' => 'Capstone Presentation',
            'description' => 'Present your data science capstone in a 10-minute video or live session. Show insights, methodology, and impact.',
            'activity_type' => ActivityType::PRESENTATION,
            'difficulty_level' => DifficultyLevel::ADVANCED,
            'base_points' => 100,
            'order' => 2,
            'estimated_hours' => 3,
            'deadline_days' => 5,
            'evidence_types' => [EvidenceType::VIDEO->value, EvidenceType::PRESENTATION->value],
            'requires_peer_review' => true,
        ], $adminId);

        $total = TrackCurriculumActivity::where('track_id', $track->id)->count();
        $this->command->info("    ✓ Data Science: 3 milestones, {$total} activities");
    }

    // ==========================================
    // HELPER
    // ==========================================

    protected function createActivity(Track $track, TrackMilestone $milestone, array $data, ?string $adminId): TrackCurriculumActivity
    {
        // Remap convenience keys to actual DB column names
        $keyMap = [
            'activity_type' => 'type',
            'base_points' => 'points',
            'order' => 'sequence_order',
            'evidence_types' => 'evidence_requirements',
        ];

        foreach ($keyMap as $old => $new) {
            if (array_key_exists($old, $data)) {
                $data[$new] = $data[$old];
                unset($data[$old]);
            }
        }

        // Handle collaboration_type → is_collaborative
        if (isset($data['collaboration_type'])) {
            $data['is_collaborative'] = true;
            unset($data['collaboration_type']);
        }

        // Remove keys that don't exist in DB
        unset($data['estimated_hours'], $data['bonus_points']);

        $activityData = array_merge([
            'track_id' => $track->id,
            'milestone_id' => $milestone->id,
            'is_required' => true,
            'is_active' => true,
            'deadline_days' => 7,
            'grace_period_days' => 2,
            'late_penalty_percent' => 20,
            'requires_peer_review' => false,
            'created_by' => $adminId,
        ], $data);

        // Convert enum values for storage
        if (isset($activityData['type']) && $activityData['type'] instanceof ActivityType) {
            $activityData['type'] = $activityData['type']->value;
        }
        if (isset($activityData['difficulty_level']) && $activityData['difficulty_level'] instanceof DifficultyLevel) {
            $activityData['difficulty_level'] = $activityData['difficulty_level']->value;
        }

        return TrackCurriculumActivity::create($activityData);
    }
}
