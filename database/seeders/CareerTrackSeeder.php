<?php

namespace Database\Seeders;

use App\Enums\TrackCategory;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Career Track Seeder
 * 
 * Seeds the 12 career tracks from the IKS Master Engineering Prompt.
 * Each track has its own scoring weights for the 5 scoring rubric dimensions.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CareerTrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tracks = [
            [
                'name' => 'Software Engineering',
                'slug' => 'software-engineering',
                'category' => TrackCategory::TECHNICAL,
                'description' => 'Master modern software development practices including full-stack development, DevOps, and cloud computing. Build real-world applications and contribute to open-source projects.',
                'short_description' => 'Full-stack development, DevOps, and cloud computing',
                'icon' => 'code',
                'color' => '#7C3AED',
                'scoring_rubric' => [
                    'technical' => 30,
                    'interview' => 25,
                    'portfolio' => 20,
                    'collaboration' => 15,
                    'learning' => 10,
                ],
                'requirements' => ['Technical Project', 'Code Review', 'System Design'],
                'outcomes' => ['DSA', 'System Design', 'Behavioral'],
            ],
            [
                'name' => 'Data Science',
                'slug' => 'data-science',
                'category' => TrackCategory::TECHNICAL,
                'description' => 'Learn to extract insights from data using statistical analysis, machine learning, and data visualization. Work with real datasets to solve business problems.',
                'short_description' => 'Statistical analysis, ML, and data visualization',
                'icon' => 'chart-bar',
                'color' => '#14B8A6',
                'scoring_rubric' => [
                    'technical' => 30,
                    'interview' => 20,
                    'portfolio' => 25,
                    'collaboration' => 10,
                    'learning' => 15,
                ],
                'requirements' => ['Data Analysis Project', 'ML Model', 'Data Visualization'],
                'outcomes' => ['Statistics', 'ML Concepts', 'Case Study'],
            ],
            [
                'name' => 'AI/ML Engineering',
                'slug' => 'ai-ml-engineering',
                'category' => TrackCategory::TECHNICAL,
                'description' => 'Specialize in building and deploying AI/ML systems at scale. Focus on deep learning, NLP, computer vision, and MLOps.',
                'short_description' => 'Deep learning, NLP, computer vision, and MLOps',
                'icon' => 'cpu',
                'color' => '#F59E0B',
                'scoring_rubric' => [
                    'technical' => 35,
                    'interview' => 20,
                    'portfolio' => 20,
                    'collaboration' => 10,
                    'learning' => 15,
                ],
                'requirements' => ['ML Project', 'Research Paper Review', 'Model Deployment'],
                'outcomes' => ['ML Theory', 'Deep Learning', 'System Design'],
            ],
            [
                'name' => 'Cloud Engineering',
                'slug' => 'cloud-engineering',
                'category' => TrackCategory::TECHNICAL,
                'description' => 'Master cloud platforms (AWS, Azure, GCP), infrastructure as code, containerization, and DevOps practices.',
                'short_description' => 'AWS, Azure, GCP, IaC, and containerization',
                'icon' => 'cloud',
                'color' => '#3B82F6',
                'scoring_rubric' => [
                    'technical' => 30,
                    'interview' => 20,
                    'portfolio' => 20,
                    'collaboration' => 15,
                    'learning' => 15,
                ],
                'requirements' => ['Cloud Project', 'Infrastructure Setup', 'Security Audit'],
                'outcomes' => ['Cloud Platforms', 'DevOps', 'System Design'],
            ],
            [
                'name' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'category' => TrackCategory::TECHNICAL,
                'description' => 'Learn to protect systems and data from cyber threats. Focus on penetration testing, security auditing, and incident response.',
                'short_description' => 'Penetration testing, security auditing, incident response',
                'icon' => 'shield-check',
                'color' => '#EF4444',
                'scoring_rubric' => [
                    'technical' => 35,
                    'interview' => 20,
                    'portfolio' => 15,
                    'collaboration' => 10,
                    'learning' => 20,
                ],
                'requirements' => ['Security Audit', 'CTF Competition', 'Vulnerability Report'],
                'outcomes' => ['Security Concepts', 'Threat Analysis', 'Practical'],
            ],
            [
                'name' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'category' => TrackCategory::HYBRID,
                'description' => 'Create beautiful and functional digital experiences. Master user research, prototyping, and design systems.',
                'short_description' => 'User research, prototyping, and design systems',
                'icon' => 'color-swatch',
                'color' => '#EC4899',
                'scoring_rubric' => [
                    'technical' => 20,
                    'interview' => 20,
                    'portfolio' => 35,
                    'collaboration' => 15,
                    'learning' => 10,
                ],
                'requirements' => ['Design Project', 'User Research', 'Design System'],
                'outcomes' => ['Portfolio Review', 'Design Critique', 'Case Study'],
            ],
            [
                'name' => 'Product Management',
                'slug' => 'product-management',
                'category' => TrackCategory::HYBRID,
                'description' => 'Learn to define, build, and launch successful products. Focus on user research, roadmap planning, and stakeholder management.',
                'short_description' => 'User research, roadmap planning, stakeholder management',
                'icon' => 'cube',
                'color' => '#8B5CF6',
                'scoring_rubric' => [
                    'technical' => 15,
                    'interview' => 25,
                    'portfolio' => 20,
                    'collaboration' => 30,
                    'learning' => 10,
                ],
                'requirements' => ['Product Spec', 'User Interview', 'Market Analysis'],
                'outcomes' => ['Product Sense', 'Analytics', 'Behavioral'],
            ],
            [
                'name' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'category' => TrackCategory::NON_TECHNICAL,
                'description' => 'Master digital marketing channels including SEO, SEM, social media, content marketing, and analytics.',
                'short_description' => 'SEO, SEM, social media, and content marketing',
                'icon' => 'megaphone',
                'color' => '#06B6D4',
                'scoring_rubric' => [
                    'technical' => 15,
                    'interview' => 15,
                    'portfolio' => 30,
                    'collaboration' => 20,
                    'learning' => 20,
                ],
                'requirements' => ['Marketing Campaign', 'Analytics Report', 'Content Strategy'],
                'outcomes' => ['Marketing Strategy', 'Analytics', 'Case Study'],
            ],
            [
                'name' => 'Business Development',
                'slug' => 'business-development',
                'category' => TrackCategory::NON_TECHNICAL,
                'description' => 'Develop skills in sales, partnerships, and business growth. Focus on B2B sales, negotiation, and strategic partnerships.',
                'short_description' => 'B2B sales, negotiation, and strategic partnerships',
                'icon' => 'trending-up',
                'color' => '#10B981',
                'scoring_rubric' => [
                    'technical' => 10,
                    'interview' => 25,
                    'portfolio' => 15,
                    'collaboration' => 35,
                    'learning' => 15,
                ],
                'requirements' => ['Sales Project', 'Partnership Proposal', 'Market Research'],
                'outcomes' => ['Sales Scenarios', 'Negotiation', 'Behavioral'],
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'category' => TrackCategory::NON_TECHNICAL,
                'description' => 'Build expertise in financial analysis, investment, and corporate finance. Focus on fintech applications.',
                'short_description' => 'Financial analysis, investment, and corporate finance',
                'icon' => 'currency-dollar',
                'color' => '#059669',
                'scoring_rubric' => [
                    'technical' => 30,
                    'interview' => 25,
                    'portfolio' => 15,
                    'collaboration' => 15,
                    'learning' => 15,
                ],
                'requirements' => ['Financial Analysis', 'Investment Case', 'Valuation Model'],
                'outcomes' => ['Technical Finance', 'Case Study', 'Behavioral'],
            ],
            [
                'name' => 'Project Management',
                'slug' => 'project-management',
                'category' => TrackCategory::NON_TECHNICAL,
                'description' => 'Master project planning, execution, and delivery. Focus on Agile, Scrum, and various project management methodologies.',
                'short_description' => 'Agile, Scrum, and project management methodologies',
                'icon' => 'clipboard-list',
                'color' => '#F97316',
                'scoring_rubric' => [
                    'technical' => 15,
                    'interview' => 20,
                    'portfolio' => 15,
                    'collaboration' => 35,
                    'learning' => 15,
                ],
                'requirements' => ['Project Plan', 'Sprint Retrospective', 'Risk Analysis'],
                'outcomes' => ['PM Scenarios', 'Leadership', 'Behavioral'],
            ],
            [
                'name' => 'Entrepreneurship',
                'slug' => 'entrepreneurship',
                'category' => TrackCategory::HYBRID,
                'description' => 'Learn to ideate, validate, and launch startups. Focus on lean methodology, fundraising, and scaling.',
                'short_description' => 'Lean methodology, fundraising, and scaling',
                'icon' => 'light-bulb',
                'color' => '#FBBF24',
                'scoring_rubric' => [
                    'technical' => 15,
                    'interview' => 20,
                    'portfolio' => 25,
                    'collaboration' => 25,
                    'learning' => 15,
                ],
                'requirements' => ['Business Plan', 'Pitch Deck', 'MVP Development'],
                'outcomes' => ['Pitch', 'Business Model', 'Behavioral'],
            ],
        ];

        foreach ($tracks as $index => $trackData) {
            Track::updateOrCreate(
                ['slug' => $trackData['slug']],
                [
                    'name' => $trackData['name'],
                    'category' => $trackData['category'],
                    'description' => $trackData['description'],
                    'short_description' => $trackData['short_description'],
                    'icon' => $trackData['icon'],
                    'color' => $trackData['color'],
                    'scoring_rubric' => $trackData['scoring_rubric'],
                    'requirements' => $trackData['requirements'],
                    'outcomes' => $trackData['outcomes'],
                    'is_active' => true,
                    'is_featured' => $index < 6, // First 6 tracks are featured
                    'order' => $index + 1,
                ]
            );
        }

        $this->command->info('✓ Seeded ' . count($tracks) . ' career tracks.');
    }
}
