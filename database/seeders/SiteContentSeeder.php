<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

/**
 * Seed default site content for landing page.
 * All this content becomes admin-editable after seeding.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            // ==========================================
            // META / SEO
            // ==========================================
            [
                'key' => 'meta_title',
                'section' => 'meta',
                'label' => 'Page Title',
                'value' => 'IKS Career Capital Platform | Transform Learning Into Career Capital',
                'type' => 'text',
                'description' => 'Browser tab title and SEO title',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'meta_description',
                'section' => 'meta',
                'label' => 'Meta Description',
                'value' => 'Join the I-NNOVA CMR Kickstarter Program to build career capital through projects, interviews, and mentorship. Connect with top African employers.',
                'type' => 'text',
                'description' => 'SEO description (150-160 characters)',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'meta_keywords',
                'section' => 'meta',
                'label' => 'Meta Keywords',
                'value' => 'career capital, tech careers, Africa jobs, mentorship, coding bootcamp, job placement',
                'type' => 'text',
                'description' => 'SEO keywords (comma-separated)',
                'sort_order' => 3,
            ],

            // ==========================================
            // HERO SECTION
            // ==========================================
            [
                'key' => 'hero_badge',
                'section' => 'hero',
                'label' => 'Hero Badge Text',
                'value' => 'I-NNOVA CMR Kickstarter Program',
                'type' => 'text',
                'description' => 'Small badge text above the main title',
                'sort_order' => 1,
            ],
            [
                'key' => 'hero_title',
                'section' => 'hero',
                'label' => 'Hero Title',
                'value' => 'Transform Your Learning Into',
                'type' => 'text',
                'description' => 'Main hero headline (first line)',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'hero_title_highlight',
                'section' => 'hero',
                'label' => 'Hero Title Highlight',
                'value' => 'Career Capital',
                'type' => 'text',
                'description' => 'The highlighted/colored part of title',
                'is_required' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'hero_subtitle',
                'section' => 'hero',
                'label' => 'Hero Subtitle',
                'value' => 'A 12-week intensive program that bridges education to employment through hands-on projects, mock interviews, and direct recruiter access.',
                'type' => 'text',
                'description' => 'Subtitle text below the main headline',
                'is_required' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'hero_cta_primary_text',
                'section' => 'hero',
                'label' => 'Primary CTA Text',
                'value' => 'Apply as Fellow',
                'type' => 'text',
                'sort_order' => 5,
            ],
            [
                'key' => 'hero_cta_primary_url',
                'section' => 'hero',
                'label' => 'Primary CTA URL',
                'value' => '/register?role=fellow',
                'type' => 'text',
                'sort_order' => 6,
            ],
            [
                'key' => 'hero_cta_secondary_text',
                'section' => 'hero',
                'label' => 'Secondary CTA Text',
                'value' => 'Hire Talent',
                'type' => 'text',
                'sort_order' => 7,
            ],
            [
                'key' => 'hero_cta_secondary_url',
                'section' => 'hero',
                'label' => 'Secondary CTA URL',
                'value' => '/register?role=recruiter',
                'type' => 'text',
                'sort_order' => 8,
            ],

            // ==========================================
            // STATS SECTION
            // ==========================================
            [
                'key' => 'stats_data',
                'section' => 'stats',
                'label' => 'Statistics',
                'value' => json_encode([
                    ['value' => '500+', 'label' => 'Fellows Trained', 'icon' => '👥'],
                    ['value' => '85%', 'label' => 'Placement Rate', 'icon' => '📈'],
                    ['value' => '12', 'label' => 'Career Tracks', 'icon' => '🎯'],
                    ['value' => '50+', 'label' => 'Hiring Partners', 'icon' => '🤝'],
                ]),
                'type' => 'json',
                'description' => 'Statistics displayed in the hero section (JSON array)',
                'sort_order' => 1,
            ],

            // ==========================================
            // PILLARS SECTION
            // ==========================================
            [
                'key' => 'pillars_title',
                'section' => 'pillars',
                'label' => 'Pillars Section Title',
                'value' => 'The Four Pillars of Career Capital',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'pillars_subtitle',
                'section' => 'pillars',
                'label' => 'Pillars Section Subtitle',
                'value' => 'Our holistic approach measures what actually matters to employers',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'pillars_data',
                'section' => 'pillars',
                'label' => 'Pillars Data',
                'value' => json_encode([
                    [
                        'title' => 'Technical Excellence',
                        'description' => 'Master in-demand skills through real projects and continuous learning',
                        'percentage' => 30,
                        'icon' => '💻',
                        'color' => 'purple',
                    ],
                    [
                        'title' => 'Interview Readiness',
                        'description' => 'Ace any interview with AI-powered practice and mentor coaching',
                        'percentage' => 25,
                        'icon' => '🎯',
                        'color' => 'blue',
                    ],
                    [
                        'title' => 'Portfolio Quality',
                        'description' => 'Build a portfolio that showcases your best work to recruiters',
                        'percentage' => 25,
                        'icon' => '📁',
                        'color' => 'teal',
                    ],
                    [
                        'title' => 'Collaboration',
                        'description' => 'Demonstrate teamwork through mentoring, code reviews, and open source',
                        'percentage' => 20,
                        'icon' => '🤝',
                        'color' => 'green',
                    ],
                ]),
                'type' => 'json',
                'description' => 'Four pillars with title, description, percentage, icon, color',
                'sort_order' => 3,
            ],

            // ==========================================
            // HOW IT WORKS SECTION
            // ==========================================
            [
                'key' => 'how_it_works_title',
                'section' => 'how_it_works',
                'label' => 'Section Title',
                'value' => 'How It Works',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'how_it_works_subtitle',
                'section' => 'how_it_works',
                'label' => 'Section Subtitle',
                'value' => 'Your journey from learning to earning in four simple steps',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'how_it_works_steps',
                'section' => 'how_it_works',
                'label' => 'Steps',
                'value' => json_encode([
                    [
                        'number' => '01',
                        'title' => 'Choose Your Track',
                        'description' => 'Select from 12 career paths including Full-Stack, Data Science, UX Design, and more',
                        'icon' => '🎯',
                    ],
                    [
                        'number' => '02',
                        'title' => 'Build Your Portfolio',
                        'description' => 'Complete weekly projects, earn points, and build a compelling portfolio',
                        'icon' => '🚀',
                    ],
                    [
                        'number' => '03',
                        'title' => 'Practice Interviews',
                        'description' => 'Master technical and behavioral interviews with AI and human mentors',
                        'icon' => '🎤',
                    ],
                    [
                        'number' => '04',
                        'title' => 'Get Hired',
                        'description' => 'Connect with recruiters and land your dream job in tech',
                        'icon' => '🏆',
                    ],
                ]),
                'type' => 'json',
                'description' => 'Steps with number, title, description, icon',
                'sort_order' => 3,
            ],

            // ==========================================
            // INTERVIEWS SECTION
            // ==========================================
            [
                'key' => 'interviews_title',
                'section' => 'interviews',
                'label' => 'Section Title',
                'value' => 'AI-Powered Mock Interviews',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'interviews_subtitle',
                'section' => 'interviews',
                'label' => 'Section Subtitle',
                'value' => 'Practice anytime with our AI interviewer, then get feedback from real hiring managers',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'interviews_features',
                'section' => 'interviews',
                'label' => 'Features List',
                'value' => json_encode([
                    'Unlimited AI practice sessions for technical and behavioral interviews',
                    'Real-time feedback on communication, technical accuracy, and confidence',
                    'Human mentor sessions for personalized coaching',
                    'Recorded sessions for self-review and improvement',
                    'Industry-specific questions tailored to your track',
                ]),
                'type' => 'json',
                'description' => 'Bullet points for the interview feature',
                'sort_order' => 3,
            ],

            // ==========================================
            // CTA SECTIONS
            // ==========================================
            [
                'key' => 'cta_fellow_title',
                'section' => 'cta',
                'label' => 'Fellow CTA Title',
                'value' => 'Ready to Build Your Career Capital?',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'cta_fellow_subtitle',
                'section' => 'cta',
                'label' => 'Fellow CTA Subtitle',
                'value' => 'Join hundreds of fellows who have transformed their careers through our program',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'cta_fellow_button_text',
                'section' => 'cta',
                'label' => 'Fellow CTA Button',
                'value' => 'Start Your Journey',
                'type' => 'text',
                'sort_order' => 3,
            ],
            [
                'key' => 'cta_fellow_button_url',
                'section' => 'cta',
                'label' => 'Fellow CTA URL',
                'value' => '/register?role=fellow',
                'type' => 'text',
                'sort_order' => 4,
            ],

            // ==========================================
            // RECRUITER CTA
            // ==========================================
            [
                'key' => 'recruiter_cta_title',
                'section' => 'recruiter_cta',
                'label' => 'Recruiter Section Title',
                'value' => 'Hire Pre-Vetted Tech Talent',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'recruiter_cta_subtitle',
                'section' => 'recruiter_cta',
                'label' => 'Recruiter Section Subtitle',
                'value' => 'Access our talent marketplace of job-ready graduates with verified skills',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'recruiter_cta_stats',
                'section' => 'recruiter_cta',
                'label' => 'Recruiter Stats',
                'value' => json_encode([
                    ['value' => '85%', 'label' => 'Faster Hiring'],
                    ['value' => '500+', 'label' => 'Active Fellows'],
                    ['value' => '4.8/5', 'label' => 'Employer Rating'],
                    ['value' => '3 Months', 'label' => 'Avg. Time to Hire'],
                ]),
                'type' => 'json',
                'sort_order' => 3,
            ],
            [
                'key' => 'recruiter_cta_button_text',
                'section' => 'recruiter_cta',
                'label' => 'Recruiter CTA Button',
                'value' => 'Explore Talent Marketplace',
                'type' => 'text',
                'sort_order' => 4,
            ],
            [
                'key' => 'recruiter_cta_button_url',
                'section' => 'recruiter_cta',
                'label' => 'Recruiter CTA URL',
                'value' => '/register?role=recruiter',
                'type' => 'text',
                'sort_order' => 5,
            ],

            // ==========================================
            // FOOTER
            // ==========================================
            [
                'key' => 'footer_brand_description',
                'section' => 'footer',
                'label' => 'Footer Brand Description',
                'value' => 'Transforming African tech talent through structured career development and direct employer connections.',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'footer_copyright',
                'section' => 'footer',
                'label' => 'Copyright Text',
                'value' => '© {year} I-NNOVA CMR. All rights reserved.',
                'type' => 'text',
                'description' => 'Use {year} for dynamic year',
                'sort_order' => 2,
            ],
            [
                'key' => 'footer_social_links',
                'section' => 'footer',
                'label' => 'Social Media Links',
                'value' => json_encode([
                    ['platform' => 'twitter', 'url' => 'https://twitter.com/innovacmr', 'icon' => 'twitter'],
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/innovacmr', 'icon' => 'linkedin'],
                    ['platform' => 'github', 'url' => 'https://github.com/innovacmr', 'icon' => 'github'],
                    ['platform' => 'youtube', 'url' => 'https://youtube.com/@innovacmr', 'icon' => 'youtube'],
                ]),
                'type' => 'json',
                'description' => 'Social media links with platform, url, icon',
                'sort_order' => 3,
            ],
        ];

        foreach ($contents as $content) {
            SiteContent::updateOrCreate(
                ['key' => $content['key']],
                $content
            );
        }
    }
}
