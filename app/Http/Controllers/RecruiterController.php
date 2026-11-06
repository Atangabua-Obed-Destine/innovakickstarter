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
    public function viewProfile(Request $request, User $user): View|RedirectResponse
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
     * Payment methods a recruiter can declare for a plan request.
     */
    public const PAYMENT_METHODS = [
        'mtn_momo' => 'MTN Mobile Money',
        'orange_money' => 'Orange Money',
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Cash (at the office)',
    ];

    /**
     * Plans, the current plan, and any request awaiting payment confirmation.
     */
    public function subscriptionIndex(Request $request): View
    {
        $user = $request->user();
        $settings = app(\App\Services\AdminSettingsService::class);

        return view('recruiter.subscription.index', [
            'currentSubscription' => $user->activeSubscription(),
            'pendingSubscription' => $user->pendingSubscription(),
            'lastRejected' => $user->subscriptions()->whereNotNull('rejection_reason')->latest('verified_at')->first(),
            'trialUsed' => $user->subscriptions()->whereNotNull('trial_ends_at')->exists(),
            'pricing' => $settings->getSubscriptionPricing(),
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    /**
     * Start a free trial.
     */
    public function startTrial(Request $request): RedirectResponse
    {
        try {
            $this->recruiterService->startTrial($request->user());

            return redirect()->route('recruiter.dashboard')
                ->with('success', 'Your free trial has started! Explore our talent marketplace.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Choose a plan. The free plan starts at once; a paid plan is sent for
     * payment confirmation with the recruiter's payment reference.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $paid = in_array($request->input('tier'), ['partner', 'premium'], true);

        $validated = $request->validate([
            'tier' => ['required', 'in:free,partner,premium'],
            'months' => [$paid ? 'required' : 'nullable', 'integer', 'in:1,6,12'],
            'payment_method' => [$paid ? 'required' : 'nullable', 'in:' . implode(',', array_keys(self::PAYMENT_METHODS))],
            'payment_reference' => [$paid ? 'required' : 'nullable', 'string', 'max:120'],
        ], [
            'payment_reference.required' => 'Enter the transaction reference of your payment.',
        ]);

        try {
            $tier = \App\Enums\SubscriptionTier::from($validated['tier']);

            $this->recruiterService->subscribe(
                $request->user(),
                $tier,
                (int) ($validated['months'] ?? 12),
                $validated['payment_reference'] ?? null,
                $validated['payment_method'] ?? null
            );

            return $paid
                ? redirect()->route('recruiter.subscription.index')
                    ->with('success', "Request received. Your {$tier->label()} plan starts as soon as we confirm your payment.")
                : redirect()->route('recruiter.dashboard')->with('success', 'You are on the Free plan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Request an upgrade of the current paid plan.
     */
    public function upgrade(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'in:partner,premium'],
            'payment_method' => ['required', 'in:' . implode(',', array_keys(self::PAYMENT_METHODS))],
            'payment_reference' => ['required', 'string', 'max:120'],
        ], [
            'payment_reference.required' => 'Enter the transaction reference of your payment.',
        ]);

        try {
            $tier = \App\Enums\SubscriptionTier::from($validated['tier']);

            $this->recruiterService->upgrade(
                $request->user(),
                $tier,
                $validated['payment_reference'],
                $validated['payment_method']
            );

            return redirect()->route('recruiter.subscription.index')
                ->with('success', "Upgrade requested. Your {$tier->label()} plan starts as soon as we confirm your payment.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the current plan.
     */
    public function cancelSubscription(Request $request): RedirectResponse
    {
        $this->recruiterService->cancelActive($request->user());

        return redirect()->route('recruiter.subscription.index')
            ->with('success', 'Subscription cancelled.');
    }

    /**
     * Withdraw a plan request that is awaiting payment confirmation.
     */
    public function cancelRequest(Request $request): RedirectResponse
    {
        $this->recruiterService->cancelPending($request->user());

        return redirect()->route('recruiter.subscription.index')
            ->with('success', 'Plan request withdrawn.');
    }
}
