<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RecruiterController;
use App\Models\Subscription;
use App\Services\RecruiterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recruiter plan requests.
 *
 * Paid plans start only when an admin confirms the recruiter's payment
 * here (the reference they entered against the MoMo / bank statement).
 */
class SubscriptionController extends Controller
{
    public function __construct(protected RecruiterService $recruiterService)
    {
    }

    /**
     * Requests awaiting confirmation, and the latest decisions.
     */
    public function index(Request $request): View
    {
        $pending = Subscription::pending()
            ->with('recruiter')
            ->whereHas('recruiter')
            ->oldest()
            ->get();

        $recent = Subscription::whereNotNull('verified_at')
            ->with(['recruiter', 'verifier'])
            ->latest('verified_at')
            ->limit(20)
            ->get();

        return view('admin.subscriptions.index', [
            'pending' => $pending,
            'recent' => $recent,
            'paymentMethods' => RecruiterController::PAYMENT_METHODS,
        ]);
    }

    /**
     * Confirm the payment and start the plan.
     */
    public function approve(Request $request, Subscription $subscription): RedirectResponse
    {
        try {
            $subscription = $this->recruiterService->approvePending($subscription, $request->user());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$subscription->tier->label()} plan of {$subscription->recruiter->name} is now active until {$subscription->expires_at->format('M j, Y')}.");
    }

    /**
     * Turn down the request, with a reason the recruiter sees.
     */
    public function reject(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        abort_unless($subscription->status === SubscriptionStatus::PENDING, 422, 'This request has already been handled.');

        $this->recruiterService->rejectPending($subscription, $request->user(), $validated['reason']);

        return back()->with('success', 'Request rejected. The recruiter has been notified.');
    }
}
