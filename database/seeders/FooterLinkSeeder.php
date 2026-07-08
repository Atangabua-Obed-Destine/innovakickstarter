<?php

namespace Database\Seeders;

use App\Models\FooterLink;
use Illuminate\Database\Seeder;

/**
 * Seed default footer links.
 */
class FooterLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            // Product Column
            ['column' => 'product', 'label' => 'Career Tracks', 'route_name' => 'tracks.index', 'sort_order' => 1],
            ['column' => 'product', 'label' => 'For Fellows', 'url' => '/fellows', 'sort_order' => 2],
            ['column' => 'product', 'label' => 'For Recruiters', 'url' => '/recruiters', 'sort_order' => 3],
            ['column' => 'product', 'label' => 'Pricing', 'url' => '/pricing', 'sort_order' => 4],
            ['column' => 'product', 'label' => 'Success Stories', 'url' => '/success-stories', 'sort_order' => 5],

            // Company Column
            ['column' => 'company', 'label' => 'About I-NNOVA', 'url' => '/about', 'sort_order' => 1],
            ['column' => 'company', 'label' => 'Our Team', 'url' => '/team', 'sort_order' => 2],
            ['column' => 'company', 'label' => 'Careers', 'url' => '/careers', 'sort_order' => 3],
            ['column' => 'company', 'label' => 'Blog', 'url' => '/blog', 'sort_order' => 4],
            ['column' => 'company', 'label' => 'Press', 'url' => '/press', 'sort_order' => 5],

            // Resources Column
            ['column' => 'resources', 'label' => 'Help Center', 'url' => '/help', 'sort_order' => 1],
            ['column' => 'resources', 'label' => 'FAQ', 'route_name' => 'faq', 'sort_order' => 2],
            ['column' => 'resources', 'label' => 'Community', 'url' => '/community', 'sort_order' => 3],
            ['column' => 'resources', 'label' => 'Documentation', 'url' => '/docs', 'sort_order' => 4],
            ['column' => 'resources', 'label' => 'Contact Us', 'url' => '/contact', 'sort_order' => 5],

            // Legal Column
            ['column' => 'legal', 'label' => 'Privacy Policy', 'url' => '/privacy', 'sort_order' => 1],
            ['column' => 'legal', 'label' => 'Terms of Service', 'url' => '/terms', 'sort_order' => 2],
            ['column' => 'legal', 'label' => 'Cookie Policy', 'url' => '/cookies', 'sort_order' => 3],
            ['column' => 'legal', 'label' => 'Data Protection', 'url' => '/data-protection', 'sort_order' => 4],

            // Social Media
            ['column' => 'social', 'label' => 'Twitter', 'url' => 'https://twitter.com/innovacmr', 'icon' => 'twitter', 'is_external' => true, 'open_new_tab' => true, 'sort_order' => 1],
            ['column' => 'social', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/company/innovacmr', 'icon' => 'linkedin', 'is_external' => true, 'open_new_tab' => true, 'sort_order' => 2],
            ['column' => 'social', 'label' => 'GitHub', 'url' => 'https://github.com/innovacmr', 'icon' => 'github', 'is_external' => true, 'open_new_tab' => true, 'sort_order' => 3],
            ['column' => 'social', 'label' => 'YouTube', 'url' => 'https://youtube.com/@innovacmr', 'icon' => 'youtube', 'is_external' => true, 'open_new_tab' => true, 'sort_order' => 4],
        ];

        foreach ($links as $link) {
            FooterLink::updateOrCreate(
                ['column' => $link['column'], 'label' => $link['label']],
                array_merge(['is_active' => true], $link)
            );
        }
    }
}
