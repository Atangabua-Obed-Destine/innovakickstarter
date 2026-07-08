<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Demo Users Seeder
 * 
 * Creates demo users for testing and demonstration purposes.
 * Includes: admin, fellows, recruiters, and mentors.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist
        $this->createRoles();

        $tracks = Track::where('is_active', true)->get();

        // Create Admin
        $admin = $this->createAdmin();

        // Create Fellows
        $fellows = $this->createFellows($tracks);

        // Create Recruiters
        $recruiters = $this->createRecruiters();

        // Create Mentors
        $mentors = $this->createMentors();

        $this->command->info('✓ Created demo users:');
        $this->command->info("  - 1 Admin: {$admin->email}");
        $this->command->info("  - " . count($fellows) . " Fellows");
        $this->command->info("  - " . count($recruiters) . " Recruiters");
        $this->command->info("  - " . count($mentors) . " Mentors");
        $this->command->newLine();
        $this->command->info('Login credentials (password for all: "password"):');
        $this->command->info("  Admin: admin@iks-innova.com");
        $this->command->info("  Fellow: alex.johnson@demo.com");
        $this->command->info("  Recruiter: recruiter@techcorp.com");
        $this->command->info("  Mentor: mentor.david@iks-innova.com");
    }

    /**
     * Create roles if they don't exist.
     */
    protected function createRoles(): void
    {
        $roles = ['admin', 'fellow', 'recruiter', 'mentor'];
        
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    /**
     * Create admin user.
     */
    protected function createAdmin(): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@iks-innova.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'IKS Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone' => '+237 600 000 001',
                'is_active' => true,
            ]
        );

        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * Create demo fellows.
     */
    protected function createFellows($tracks): array
    {
        $fellows = [
            [
                'name' => 'Alex Johnson',
                'email' => 'alex.johnson@demo.com',
                'username' => 'alex-johnson',
                'phone' => '+237 670 123 456',
                'bio' => 'Passionate full-stack developer with expertise in React, Node.js, and cloud technologies. Currently building scalable applications and contributing to open-source projects.',
                'track' => 'software-engineering',
            ],
            [
                'name' => 'Sarah Chen',
                'email' => 'sarah.chen@demo.com',
                'username' => 'sarah-chen',
                'phone' => '+237 680 234 567',
                'bio' => 'Data scientist specializing in machine learning and statistical analysis. Passionate about using data to drive business decisions.',
                'track' => 'data-science',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@demo.com',
                'username' => 'michael-brown',
                'phone' => '+237 690 345 678',
                'bio' => 'DevOps engineer with strong background in cloud infrastructure, CI/CD, and containerization. AWS certified.',
                'track' => 'cloud-engineering',
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@demo.com',
                'username' => 'emily-davis',
                'phone' => '+237 655 456 789',
                'bio' => 'Product manager with experience in B2B SaaS. Skilled in user research, roadmap planning, and agile methodologies.',
                'track' => 'product-management',
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@demo.com',
                'username' => 'david-wilson',
                'phone' => '+237 675 567 890',
                'bio' => 'UX/UI designer passionate about creating intuitive and accessible digital experiences. Figma expert.',
                'track' => 'ui-ux-design',
            ],
        ];

        $createdFellows = [];

        foreach ($fellows as $fellowData) {
            $track = $tracks->where('slug', $fellowData['track'])->first();

            $fellow = User::updateOrCreate(
                ['email' => $fellowData['email']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $fellowData['name'],
                    'username' => $fellowData['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => $fellowData['phone'],
                    'bio' => $fellowData['bio'],
                    'location' => 'Yaoundé, Cameroon',
                    'availability' => 'immediate',
                    'is_active' => true,
                    'is_public' => true,
                    'open_to_opportunities' => true,
                ]
            );

            $fellow->assignRole('fellow');

            // Assign to track
            if ($track) {
                $score = rand(70, 95);
                $tier = match(true) {
                    $score >= 75 => 'elite',
                    $score >= 50 => 'professional',
                    $score >= 25 => 'intern',
                    default => 'rookie',
                };

                $fellow->fellowTracks()->updateOrCreate(
                    ['track_id' => $track->id],
                    [
                        'is_primary' => true,
                        'started_at' => now()->subMonths(rand(1, 6)),
                        'score' => $score,
                        'tier' => $tier,
                    ]
                );
            }

            $createdFellows[] = $fellow;
        }

        return $createdFellows;
    }

    /**
     * Create demo recruiters.
     */
    protected function createRecruiters(): array
    {
        $recruiters = [
            [
                'name' => 'TechCorp Recruiter',
                'email' => 'recruiter@techcorp.com',
                'username' => 'techcorp-hr',
                'phone' => '+237 650 111 222',
            ],
            [
                'name' => 'StartupHub Talent',
                'email' => 'talent@startuphub.com',
                'username' => 'startuphub-talent',
                'phone' => '+237 660 222 333',
            ],
        ];

        $createdRecruiters = [];

        foreach ($recruiters as $recruiterData) {
            $recruiter = User::updateOrCreate(
                ['email' => $recruiterData['email']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $recruiterData['name'],
                    'username' => $recruiterData['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => $recruiterData['phone'],
                    'is_active' => true,
                ]
            );

            $recruiter->assignRole('recruiter');

            $createdRecruiters[] = $recruiter;
        }

        return $createdRecruiters;
    }

    /**
     * Create demo mentors.
     */
    protected function createMentors(): array
    {
        $mentors = [
            [
                'name' => 'David Mentor',
                'email' => 'mentor.david@iks-innova.com',
                'username' => 'mentor-david',
                'phone' => '+237 680 444 555',
                'bio' => 'Senior Software Engineer at Microsoft with 10+ years of experience. Passionate about mentoring the next generation of African tech talent.',
            ],
            [
                'name' => 'Sarah Mentor',
                'email' => 'mentor.sarah@iks-innova.com',
                'username' => 'mentor-sarah',
                'phone' => '+237 690 555 666',
                'bio' => 'Product Lead at a fintech startup. Former consultant at McKinsey. Helping fellows navigate their product careers.',
            ],
        ];

        $createdMentors = [];

        foreach ($mentors as $mentorData) {
            $mentor = User::updateOrCreate(
                ['email' => $mentorData['email']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $mentorData['name'],
                    'username' => $mentorData['username'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => $mentorData['phone'],
                    'bio' => $mentorData['bio'],
                    'is_active' => true,
                ]
            );

            $mentor->assignRole('mentor');

            $createdMentors[] = $mentor;
        }

        return $createdMentors;
    }
}
