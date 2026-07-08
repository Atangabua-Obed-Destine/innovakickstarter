<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use App\Models\FooterLink;
use App\Models\SiteContent;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Content Management Controller
 * 
 * Handles all CMS functionality for the admin panel including:
 * - Landing page content (hero, stats, pillars, etc.)
 * - Testimonials CRUD
 * - FAQs CRUD
 * - Footer links management
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ContentController extends Controller
{
    /**
     * Display content management dashboard.
     */
    public function index(): View
    {
        // Get site content grouped by section
        $siteContentGrouped = SiteContent::orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        // Get testimonials
        $testimonials = Testimonial::orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        // Get FAQs grouped by category
        $faqsGrouped = FAQ::getGroupedByCategory();

        // Get footer links grouped
        $footerLinks = FooterLink::getGrouped();

        return view('admin.content.index', compact(
            'siteContentGrouped',
            'testimonials',
            'faqsGrouped',
            'footerLinks'
        ));
    }

    /**
     * Update a section of site content.
     */
    public function updateSection(Request $request, string $section): RedirectResponse
    {
        $contentData = $request->input('content', []);

        foreach ($contentData as $key => $value) {
            $content = SiteContent::where('key', $key)->first();
            
            if ($content) {
                // Validate JSON if type is json
                if ($content->type === 'json') {
                    $decoded = json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return back()
                            ->withErrors(['content.' . $key => 'Invalid JSON format for ' . $content->label])
                            ->withInput();
                    }
                }

                $content->update([
                    'value' => $value,
                    'updated_by' => auth()->id(),
                ]);
            }
        }

        return back()->with('success', ucfirst(str_replace('_', ' ', $section)) . ' content updated successfully.');
    }

    /**
     * Seed initial content (if not exists).
     */
    public function seed(): RedirectResponse
    {
        try {
            Artisan::call('db:seed', ['--class' => 'SiteContentSeeder']);
            Artisan::call('db:seed', ['--class' => 'TestimonialSeeder']);
            Artisan::call('db:seed', ['--class' => 'FooterLinkSeeder']);
            Artisan::call('db:seed', ['--class' => 'FAQSeeder']);

            return back()->with('success', 'Content initialized successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initialize content: ' . $e->getMessage());
        }
    }

    // ==========================================
    // TESTIMONIALS
    // ==========================================

    /**
     * Store a new testimonial.
     */
    public function storeTestimonial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'quote' => 'required|string|max:2000',
            'image_url' => 'nullable|url|max:500',
            'rating' => 'required|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = Testimonial::max('sort_order') + 1;

        Testimonial::create($validated);

        return redirect()
            ->route('admin.content.index', ['tab' => 'testimonials'])
            ->with('success', 'Testimonial added successfully.');
    }

    /**
     * Toggle testimonial active status.
     */
    public function toggleTestimonial(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);

        return back()->with('success', 'Testimonial ' . ($testimonial->is_active ? 'activated' : 'deactivated') . '.');
    }

    /**
     * Delete a testimonial.
     */
    public function destroyTestimonial(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted.');
    }

    // ==========================================
    // FAQs
    // ==========================================

    /**
     * Store a new FAQ.
     */
    public function storeFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(FAQ::CATEGORIES)),
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:10000',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = FAQ::where('category', $validated['category'])->max('sort_order') + 1;

        FAQ::create($validated);

        return redirect()
            ->route('admin.content.index', ['tab' => 'faqs'])
            ->with('success', 'FAQ added successfully.');
    }

    /**
     * Toggle FAQ active status.
     */
    public function toggleFaq(FAQ $faq): RedirectResponse
    {
        $faq->update(['is_active' => !$faq->is_active]);

        return back()->with('success', 'FAQ ' . ($faq->is_active ? 'activated' : 'deactivated') . '.');
    }

    /**
     * Delete a FAQ.
     */
    public function destroyFaq(FAQ $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    // ==========================================
    // FOOTER LINKS
    // ==========================================

    /**
     * Store a new footer link.
     */
    public function storeFooterLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'column' => 'required|string|in:' . implode(',', array_keys(FooterLink::COLUMNS)),
            'label' => 'required|string|max:100',
            'url' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_external' => 'boolean',
            'open_new_tab' => 'boolean',
        ]);

        $validated['is_external'] = $request->has('is_external');
        $validated['open_new_tab'] = $request->has('open_new_tab');
        $validated['is_active'] = true;
        $validated['sort_order'] = FooterLink::where('column', $validated['column'])->max('sort_order') + 1;

        FooterLink::create($validated);

        return redirect()
            ->route('admin.content.index', ['tab' => 'footer'])
            ->with('success', 'Footer link added.');
    }

    /**
     * Delete a footer link.
     */
    public function destroyFooterLink(FooterLink $footerLink): RedirectResponse
    {
        $footerLink->delete();

        return back()->with('success', 'Footer link deleted.');
    }
}
