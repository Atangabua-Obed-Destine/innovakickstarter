<?php

namespace Database\Seeders;

use App\Models\FAQ;
use Illuminate\Database\Seeder;

/**
 * Seed default FAQs.
 */
class FAQSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            // General
            [
                'category' => 'general',
                'question' => 'What is the IKS Career Capital Platform?',
                'answer' => '<p>The IKS (I-NNOVA Kickstarter) Career Capital Platform is a comprehensive program designed to bridge the gap between education and employment. We help aspiring tech professionals build verifiable career capital through hands-on projects, mock interviews, mentorship, and direct connections with employers.</p>',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'general',
                'question' => 'How long is the program?',
                'answer' => '<p>The core program runs for 12 weeks, but fellows have lifetime access to resources, the community, and the talent marketplace. Many fellows continue building their career capital well beyond the initial program.</p>',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'general',
                'question' => 'Is the program fully online?',
                'answer' => '<p>Yes, the entire program is delivered online, making it accessible to participants across Africa and beyond. We have live sessions, recorded content, and async collaboration tools.</p>',
                'sort_order' => 3,
            ],

            // For Fellows
            [
                'category' => 'fellows',
                'question' => 'What are the prerequisites to join as a Fellow?',
                'answer' => '<p>We look for motivated individuals with basic programming knowledge or relevant skills in their chosen track. Each track has specific prerequisites, but we value dedication and growth mindset over extensive prior experience.</p>',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'fellows',
                'question' => 'How does the Career Capital scoring work?',
                'answer' => '<p>Your Career Capital Score is calculated across four pillars: Technical Excellence (30%), Interview Readiness (25%), Portfolio Quality (25%), and Collaboration (20%). You earn points by completing activities in each category, which are reviewed and verified by mentors.</p>',
                'sort_order' => 2,
            ],
            [
                'category' => 'fellows',
                'question' => 'Can I work on the program part-time?',
                'answer' => '<p>Yes! While the program is intensive, it is designed to accommodate working professionals. Expect to dedicate 15-20 hours per week for optimal progress. You can complete activities at your own pace within weekly deadlines.</p>',
                'sort_order' => 3,
            ],
            [
                'category' => 'fellows',
                'question' => 'What happens after I complete the program?',
                'answer' => '<p>Upon reaching Professional or Elite tier, your profile becomes visible in our Talent Marketplace where vetted recruiters can discover and contact you. You also gain access to our alumni network and continued mentorship opportunities.</p>',
                'sort_order' => 4,
            ],

            // For Recruiters
            [
                'category' => 'recruiters',
                'question' => 'How does the Talent Marketplace work?',
                'answer' => '<p>Our Talent Marketplace gives you access to pre-vetted tech professionals with verified skills. You can search by track, tier, skills, and more. Career Capital scores give you objective data on candidate readiness beyond traditional resumes.</p>',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'recruiters',
                'question' => 'What subscription plans are available?',
                'answer' => '<p>We offer three tiers: <strong>Free</strong> (20 profile views/month), <strong>Partner</strong> (unlimited views + 5 intros/month), and <strong>Premium</strong> (unlimited everything + custom assessments + API access). Contact us for enterprise pricing.</p>',
                'sort_order' => 2,
            ],
            [
                'category' => 'recruiters',
                'question' => 'Can I request custom assessments for candidates?',
                'answer' => '<p>Yes! Premium tier subscribers can request custom technical assessments tailored to their specific job requirements. Our mentors will design and administer the assessments, providing you with detailed reports.</p>',
                'sort_order' => 3,
            ],

            // Pricing
            [
                'category' => 'pricing',
                'question' => 'Is the Fellow program free?',
                'answer' => '<p>We offer both free and premium tracks. The core curriculum and community access are free. Premium features like unlimited AI interviews, priority mentor sessions, and career coaching are available through our Pro plan.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'pricing',
                'question' => 'What payment methods do you accept?',
                'answer' => '<p>We accept Mobile Money (MTN, Orange), bank transfers, and international cards (Visa, Mastercard). All payments are processed securely. We also offer scholarships for qualifying candidates.</p>',
                'sort_order' => 2,
            ],

            // Technical
            [
                'category' => 'technical',
                'question' => 'What tech stack is used in the platform?',
                'answer' => '<p>The platform is built with Laravel (PHP), Tailwind CSS, and Alpine.js. We use OpenAI for AI-powered mock interviews and various APIs for integrations. The codebase follows best practices for security and scalability.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'technical',
                'question' => 'Is there an API for integration?',
                'answer' => '<p>Yes, Premium tier recruiters have access to our REST API for ATS integration. You can programmatically search candidates, track applications, and sync data with your hiring tools. API documentation is available upon subscription.</p>',
                'sort_order' => 2,
            ],
        ];

        foreach ($faqs as $faq) {
            FAQ::updateOrCreate(
                ['question' => $faq['question']],
                array_merge(['is_active' => true], $faq)
            );
        }
    }
}
