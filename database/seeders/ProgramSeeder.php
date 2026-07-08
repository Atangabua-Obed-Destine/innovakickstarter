<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user as creator
        $admin = User::role('admin')->first() ?? User::first();

        // Create sample programs
        $programs = [
            [
                'name' => 'IKS Fellowship 2025',
                'description' => 'The flagship Innova Kickstarter Fellowship program for 2025. A comprehensive 12-week intensive program designed to transform career trajectories through practical skill-building, mentorship, and industry connections.',
                'start_date' => '2025-01-15',
                'end_date' => '2025-04-15',
                'graduation_date' => '2025-04-20',
                'max_fellows' => 100,
                'status' => Program::STATUS_ACTIVE,
                'sponsor_name' => 'Tech Innovation Foundation',
                'certificate_template' => 'IKS-2025',
                'milestones' => [
                    [
                        'name' => 'Onboarding Complete',
                        'target_date' => '2025-01-22',
                        'description' => 'Complete orientation and setup development environment',
                    ],
                    [
                        'name' => 'First Project Submitted',
                        'target_date' => '2025-02-15',
                        'description' => 'Submit first major project for review',
                    ],
                    [
                        'name' => 'Portfolio Website Live',
                        'target_date' => '2025-03-15',
                        'description' => 'Deploy personal portfolio website',
                    ],
                    [
                        'name' => 'Capstone Project Complete',
                        'target_date' => '2025-04-10',
                        'description' => 'Complete and present final capstone project',
                    ],
                ],
            ],
            [
                'name' => 'IKS Fellowship Q2 2025',
                'description' => 'The second cohort of the 2025 IKS Fellowship program, starting in April. Focus on emerging technologies and advanced project work.',
                'start_date' => '2025-04-20',
                'end_date' => '2025-07-20',
                'graduation_date' => '2025-07-25',
                'max_fellows' => 75,
                'status' => Program::STATUS_ENROLLING,
                'certificate_template' => 'IKS-2025-Q2',
                'milestones' => [
                    [
                        'name' => 'Onboarding Complete',
                        'target_date' => '2025-04-27',
                        'description' => 'Complete orientation week',
                    ],
                    [
                        'name' => 'Midterm Review',
                        'target_date' => '2025-06-01',
                        'description' => 'Midterm project review and feedback',
                    ],
                    [
                        'name' => 'Final Project',
                        'target_date' => '2025-07-15',
                        'description' => 'Final capstone project completion',
                    ],
                ],
            ],
            [
                'name' => 'IKS Fellowship 2024',
                'description' => 'The completed 2024 IKS Fellowship program. Our most successful batch with 85% employment rate within 3 months of graduation.',
                'start_date' => '2024-01-15',
                'end_date' => '2024-04-15',
                'graduation_date' => '2024-04-20',
                'max_fellows' => 80,
                'status' => Program::STATUS_GRADUATED,
                'sponsor_name' => 'Digital Skills Initiative',
                'certificate_template' => 'IKS-2024',
                'milestones' => [
                    [
                        'name' => 'Onboarding Complete',
                        'target_date' => '2024-01-22',
                        'description' => 'Complete orientation',
                    ],
                    [
                        'name' => 'Final Project',
                        'target_date' => '2024-04-10',
                        'description' => 'Capstone project completion',
                    ],
                ],
            ],
            [
                'name' => 'IKS Summer Intensive 2025',
                'description' => 'An accelerated summer program for students looking to gain tech skills during the break. 8-week intensive with focus on practical projects.',
                'start_date' => '2025-06-01',
                'end_date' => '2025-07-31',
                'graduation_date' => '2025-08-05',
                'max_fellows' => 50,
                'status' => Program::STATUS_UPCOMING,
                'certificate_template' => 'IKS-SUM-2025',
                'milestones' => [
                    [
                        'name' => 'Week 4 Checkpoint',
                        'target_date' => '2025-06-28',
                        'description' => 'Midpoint evaluation',
                    ],
                    [
                        'name' => 'Final Presentation',
                        'target_date' => '2025-07-28',
                        'description' => 'Demo day presentation',
                    ],
                ],
            ],
            [
                'name' => 'IKS Women in Tech 2025',
                'description' => 'A dedicated fellowship program focused on supporting women entering the tech industry. Includes additional mentorship and networking opportunities.',
                'start_date' => '2025-03-01',
                'end_date' => '2025-05-31',
                'graduation_date' => '2025-06-05',
                'max_fellows' => 40,
                'status' => Program::STATUS_DRAFT,
                'sponsor_name' => 'Women in Tech Foundation',
                'certificate_template' => 'IKS-WIT-2025',
                'milestones' => [],
            ],
        ];

        foreach ($programs as $data) {
            $data['slug'] = Str::slug($data['name']);
            $data['created_by'] = $admin?->id;
            
            // Ensure unique slug
            $baseSlug = $data['slug'];
            $counter = 1;
            while (Program::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $baseSlug . '-' . $counter++;
            }

            Program::create($data);
        }

        $this->command->info('Created ' . count($programs) . ' sample programs.');
    }
}
