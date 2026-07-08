<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\FooterLink;
use App\Models\SiteContent;
use App\Models\Testimonial;
use App\Models\Track;
use Illuminate\View\View;

/**
 * Home Controller
 * 
 * Handles the public landing page with dynamic content from the admin CMS.
 * All content is loaded from database models for admin customization.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class HomeController extends Controller
{
    /**
     * Display the landing page with dynamic content.
     */
    public function index(): View
    {
        // Load all site content for the landing page, grouped by section
        $content = SiteContent::where('section', '!=', 'meta')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->getValue()];
            });

        // Load meta content separately
        $meta = SiteContent::where('section', 'meta')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->getValue()];
            });

        // Load active testimonials
        $testimonials = Testimonial::getActive(6);

        // Load active tracks for display
        $tracks = Track::where('is_active', true)
            ->orderBy('order')
            ->limit(6)
            ->get();

        // Load featured FAQs for landing page
        $faqs = FAQ::getFeatured(6);

        // Load footer links
        $footerLinks = FooterLink::getGrouped();

        // Platform statistics (from settings or calculated)
        $stats = [
            'fellows_count' => $content['stat_fellows'] ?? '500+',
            'placement_rate' => $content['stat_placement'] ?? '85%',
            'tracks_count' => $tracks->count() ?: ($content['stat_tracks'] ?? '12'),
        ];

        return view('welcome', compact(
            'content',
            'meta',
            'testimonials',
            'tracks',
            'faqs',
            'footerLinks',
            'stats'
        ));
    }
}
