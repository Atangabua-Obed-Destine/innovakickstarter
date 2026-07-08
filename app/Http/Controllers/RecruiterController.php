<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RecruiterService;
use App\Services\TalentMarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recruiter Controller
 * 
 * Handles recruiter-specific functionality including:
 * - Dashboard
 * - Talent marketplace
 * - Subscription management
 * - Shortlist management
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RecruiterController extends Controller
{
    public function __construct(
        protected RecruiterService $recruiterService,
        protected TalentMarketplaceService $marketplaceService
    ) {}

    /**
     * Display the recruiter dashboard.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        
        $stats = $this->recruiterService->getDashboardStats($user);
        
        // Featured talent
        $featuredTalent = $this->marketplaceService->getFeatured(4);
        
        // Recent shortlist
        $shortlist = $this->recruiterService->getShortlist($user)->take(5);
        
        // Marketplace stats
        $marketplaceStats = $this->marketplaceService->getStatistics();

        return view('recruiter.dashboard', [
            'stats' => $stats,
            'featuredTalent' => $featuredTalent,
            'shortlist' => $shortlist,
            'marketplaceStats' => $marketplaceStats,
        ]);
    }

    /**
     * Display recruiter onboarding.
     */
    public function onboarding(Request $request): View
    {
        $user = $request->user();

        return view('recruiter.onboarding', [
            'user' => $user,
        ]);
    }

    /**
     * Display the talent marketplace.
     */
    public function marketplace(Request $request): View
    {
        $user = $request->user();
        
        // Check subscription
        $canAccess = $this->recruiterService->canPerformAction($user, 'view_profile');
        
        // Get filtered talent
        $talent = $this->marketplaceService->search($request->all());
        
        // Get filter options
        $tracks = $this->marketplaceService->getAvailableTracks();
        $skills = array_slice($this->marketplaceService->getAvailableSkills(), 0, 50);
        
        // Featured talent for sidebar
        $featured = $this->marketplaceService->getFeatured(3);
        
        // Rising stars
        $risingStars = $this->marketplaceService->getRisingStars(3);

        // Marketplace stats for display
        $marketplaceStats = $this->marketplaceService->getStatistics();

        // Shortlist count
        $shortlistCount = $this->recruiterService->getShortlist($user)->count();

        return view('recruiter.marketplace.index', [
            'talent' => $talent,
            'tracks' => $tracks,
            'skills' => $skills,
            'featured' => $featured,
            'risingStars' => $risingStars,
            'canAccess' => $canAccess,
            'filters' => $request->all(),
            'totalTalent' => $marketplaceStats['total_talent'] ?? 0,
            'highPerformers' => ($marketplaceStats['by_tier']['elite'] ?? 0) + ($marketplaceStats['by_tier']['professional'] ?? 0),
            'shortlistCount' => $shortlistCount,
            'trackCount' => $tracks->count(),
        ]);
    }

    /**
     * View a talent profile.
     */
    public function viewProfile(Request $request, User $user): View
    {
        $recruiter = $request->user();
        
        // Check if can view
        $canView = $this->recruiterService->canPerformAction($recruiter, 'view_profile');
        
        if (!$canView['allowed']) {
            return redirect()->route('recruiter.subscription.index')
                ->with('warning', $canView['reason']);
        }

        // Record the view
        $this->recruiterService->recordProfileView($recruiter, $user);

        // Load profile data
        $user->load([
            'fellowTracks.track',
            'activities' => fn($q) => $q->where('status', 'approved')
                ->where('is_public', true)
                ->latest()
                ->limit(10),
        ]);

        $primaryTrack = $user->primaryTrack;
        
        // Check if shortlisted
        $isShortlisted = $recruiter->recruiterActions()
            ->where('fellow_id', $user->id)
            ->where('action', 'shortlist')
            ->exists();
        
        // Similar talent
        $similarTalent = $this->marketplaceService->getSimilarTalent($user, 4);

        return view('recruiter.talent.show', [
            'talent' => $user,
            'primaryTrack' => $primaryTrack,
            'isShortlisted' => $isShortlisted,
            'similarTalent' => $similarTalent,
        ]);
    }

    /**
     * Add to shortlist.
     */
    public function addToShortlist(Request $request, User $user): RedirectResponse
    {
        $recruiter = $request->user();
        
        $notes = $request->input('notes');
        
        $this->recruiterService->addToShortlist($recruiter, $user, $notes);

        return redirect()->back()
            ->with('success', "{$user->name} added to your shortlist!");
    }

    /**
     * Remove from shortlist.
     */
    public function removeFromShortlist(Request $request, User $user): RedirectResponse
    {
        $recruiter = $request->user();
        
        $this->recruiterService->removeFromShortlist($recruiter, $user);

        return redirect()->back()
            ->with('success', 'Removed from shortlist.');
    }

    /**
     * View shortlist.
     */
    public function shortlist(Request $request): View
    {
        $recruiter = $request->user();
        
        $shortlist = $this->recruiterService->getShortlist($recruiter);

        // Calculate shortlist stats
        $stats = [
            'total' => $shortlist->count(),
            'contacted' => $shortlist->where('status', 'contacted')->count(),
            'interviewing' => $shortlist->where('status', 'interviewing')->count(),
            'hired' => $shortlist->where('status', 'hired')->count(),
        ];

        return view('recruiter.shortlist.index', [
            'shortlist' => $shortlist,
            'stats' => $stats,
        ]);
    }

    /**
     * Contact a talent.
     */
    public function contact(Request $request, User $user): RedirectResponse
    {
        $recruiter = $request->user();
        
        // Check if can contact
        $canContact = $this->recruiterService->canPerformAction($recruiter, 'contact');
        
        if (!$canContact['allowed']) {
            return redirect()->back()
                ->with('warning', $canContact['reason']);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $this->recruiterService->requestContact($recruiter, $user, $validated['message']);

        return redirect()->back()
            ->with('success', 'Contact request sent successfully!');
    }

    /**
     * Display subscription options.
     */
    public function subscriptionIndex(Request $request): View
    {
        $user = $request->user();
        $currentSubscription = $user->activeSubscription();
        
        $pricing = app(\App\Services\AdminSettingsService::class)->getSubscriptionPricing();

        return view('recruiter.subscription.index', [
            'currentSubscription' => $currentSubscription,
            'pricing' => $pricing,
        ]);
    }

    /**
     * Start a free trial.
     */
    public function startTrial(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->recruiterService->startTrial($user);
            
            return redirect()->route('recruiter.dashboard')
                ->with('success', 'Your free trial has started! Explore our talent marketplace.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'tier' => ['required', 'in:free,partner,premium'],
            'months' => ['required', 'integer', 'in:1,6,12'],
            'payment_reference' => ['nullable', 'string'],
        ]);

        try {
            $tier = \App\Enums\SubscriptionTier::from($validated['tier']);
            
            $this->recruiterService->subscribe(
                $user,
                $tier,
                $validated['months'],
                $validated['payment_reference'] ?? null
            );
            
            return redirect()->route('recruiter.dashboard')
                ->with('success', 'Subscription activated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Upgrade subscription.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'tier' => ['required', 'in:partner,premium'],
        ]);

        try {
            $tier = \App\Enums\SubscriptionTier::from($validated['tier']);
            
            $this->recruiterService->upgrade($user, $tier);
            
            return redirect()->route('recruiter.subscription.index')
                ->with('success', 'Subscription upgraded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->recruiterService->cancelActive($user);

        return redirect()->route('recruiter.subscription.index')
            ->with('success', 'Subscription cancelled.');
    }
}
