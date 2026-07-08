<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seed default testimonials for landing page.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Amara Diallo',
                'role' => 'Full-Stack Developer',
                'company' => 'TechCorp Africa',
                'quote' => 'The IKS program transformed my career. Within 3 months of completing the program, I landed my dream job. The mock interviews and portfolio projects made all the difference.',
                'rating' => 5,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Kwame Asante',
                'role' => 'Data Scientist',
                'company' => 'FinTech Solutions',
                'quote' => 'The Career Capital approach is genius. Instead of just learning, I was building real projects that employers actually cared about. My score gave recruiters confidence in my abilities.',
                'rating' => 5,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Fatima Mensah',
                'role' => 'UX Designer',
                'company' => 'Design Studio Accra',
                'quote' => 'The AI mock interviews were game-changing. I practiced dozens of times before my real interviews, and it paid off. I felt so confident walking into each interview.',
                'rating' => 5,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Emmanuel Okonkwo',
                'role' => 'DevOps Engineer',
                'company' => 'CloudFirst Nigeria',
                'quote' => 'What sets IKS apart is the recruiter marketplace. Companies could see my verified skills and projects. I received 4 interview requests within my first week of being listed.',
                'rating' => 5,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Sarah Kimani',
                'role' => 'Product Manager',
                'company' => 'StartupHub Kenya',
                'quote' => 'The mentorship and community support were incredible. Having experienced professionals review my work and guide my career decisions was invaluable.',
                'rating' => 4,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Olivier Ndongo',
                'role' => 'Mobile Developer',
                'company' => 'AppWorks Cameroon',
                'quote' => 'I went from struggling to get interviews to having multiple offers. The structured approach to building career capital really works!',
                'rating' => 5,
                'is_featured' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name'], 'company' => $testimonial['company']],
                $testimonial
            );
        }
    }
}
