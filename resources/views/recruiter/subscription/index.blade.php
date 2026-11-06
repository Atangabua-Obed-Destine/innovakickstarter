@extends('layouts.app')

@section('title', 'Subscription & Billing')

@section('content')
@php
    $xaf = fn ($amount) => number_format((int) $amount, 0, '.', ' ') . ' XAF';
    // Same rule as RecruiterService::calculatePrice: annual price, pro rata, rounded to 100 XAF.
    $priceFor = fn (int $annual, int $months) => (int) (round($annual * $months / 12 / 100) * 100);
    $limit = fn ($value, $unit) => (int) $value < 0 ? "Unlimited {$unit}" : ((int) $value . " {$unit}");

    $plans = [
        'free' => [
            'name' => 'Free',
            'annual' => 0,
            'features' => [
                'Browse the talent directory',
                $limit($pricing['free']['profile_views'] ?? 20, 'profile views / month'),
                $limit($pricing['free']['downloads'] ?? 5, 'resume downloads / month'),
                'Tier and track of each fellow',
            ],
        ],
        'partner' => [
            'name' => 'Partner',
            'annual' => (int) ($pricing['partner']['price'] ?? 300000),
            'featured' => true,
            'features' => [
                'Everything in Free',
                $limit($pricing['partner']['profile_views'] ?? -1, 'profile views'),
                $limit($pricing['partner']['intros'] ?? 5, 'warm introductions / month'),
                'Advanced search & filters',
                'Shortlists and pipeline',
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'annual' => (int) ($pricing['premium']['price'] ?? 1200000),
            'features' => [
                'Everything in Partner',
                $limit($pricing['premium']['intros'] ?? -1, 'introductions'),
                'Priority access to new Elite fellows',
                'Custom talent reports',
                'Dedicated account manager',
            ],
        ],
    ];

    $currentTier = $currentSubscription?->tier?->value;
    $isPaidCurrent = $currentSubscription && $currentTier !== 'free' && $currentSubscription->status->value !== 'trial';
    $instructions = trim((string) ($pricing['payment_instructions'] ?? ''));
@endphp

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Subscription & Billing</h1>
        <p class="text-dark-400 mt-1">Choose how you access the I-NNOVA talent marketplace.</p>
    </div>

    @if($errors->any())
        <div class="card p-4 border border-red-500/40 text-sm text-red-300">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Request awaiting payment confirmation -->
    @if($pendingSubscription)
        <div class="card p-6 border-l-4 border-amber-500">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-500/20 text-amber-400">Awaiting payment confirmation</span>
                    <h2 class="text-xl font-bold text-white mt-2">{{ $pendingSubscription->tier->label() }} plan requested</h2>
                    <p class="text-dark-400 text-sm mt-1">
                        {{ $xaf($pendingSubscription->amount) }}
                        @if($pendingSubscription->requested_months)
                            for {{ $pendingSubscription->requested_months }} month{{ $pendingSubscription->requested_months > 1 ? 's' : '' }}
                        @endif
                        · {{ $paymentMethods[$pendingSubscription->payment_method] ?? 'Payment' }} ref. <span class="font-mono text-dark-200">{{ $pendingSubscription->payment_reference }}</span>
                    </p>
                    <p class="text-dark-500 text-sm mt-1">The plan starts as soon as our team confirms your payment. You will be notified.</p>
                </div>
                <form method="POST" action="{{ route('recruiter.subscription.cancel-request') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Withdraw this plan request?')">Withdraw request</button>
                </form>
            </div>
        </div>
    @elseif($lastRejected && $lastRejected->verified_at?->gt(now()->subDays(30)) && (!$currentSubscription || $lastRejected->verified_at->gt($currentSubscription->started_at)))
        <div class="card p-4 border-l-4 border-red-500 text-sm">
            <p class="text-red-300 font-medium">Your {{ $lastRejected->tier->label() }} plan request was not approved.</p>
            <p class="text-dark-300 mt-1">{{ $lastRejected->rejection_reason }}</p>
            <p class="text-dark-500 mt-1">You can submit a new request below with the correct payment reference.</p>
        </div>
    @endif

    <!-- Current Plan -->
    @if($currentSubscription)
        <div class="card p-6 border-l-4 border-green-500" x-data="{ upgrading: {{ $errors->any() && old('upgrade') ? 'true' : 'false' }} }">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $currentSubscription->status->badgeClass() }}">{{ $currentSubscription->status->label() }}</span>
                    <h2 class="text-xl font-bold text-white mt-2">{{ $currentSubscription->tier->label() }} Plan</h2>
                    <p class="text-dark-400 text-sm">
                        @if($currentSubscription->expires_at)
                            {{ $currentSubscription->status->value === 'trial' ? 'Trial ends' : 'Active until' }} {{ $currentSubscription->expires_at->format('M j, Y') }}
                        @else
                            No end date
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    @if($currentTier === 'partner')
                        <button type="button" @click="upgrading = !upgrading" class="btn btn-primary">Upgrade to Premium</button>
                    @endif
                    <form method="POST" action="{{ route('recruiter.subscription.cancel') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Cancel your plan now? Marketplace access stops immediately.')">Cancel Plan</button>
                    </form>
                </div>
            </div>

            @if($currentTier === 'partner')
                @php
                    $daysLeft = $currentSubscription->expires_at ? max(0, (int) now()->diffInDays($currentSubscription->expires_at, false)) : 0;
                    $upgradeCost = (int) round(max(0, ($plans['premium']['annual'] - $plans['partner']['annual']) / 360 * $daysLeft));
                @endphp
                <form x-show="upgrading" x-cloak method="POST" action="{{ route('recruiter.subscription.upgrade') }}" class="mt-6 border-t border-dark-700 pt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="tier" value="premium">
                    <input type="hidden" name="upgrade" value="1">
                    <p class="text-dark-300 text-sm">Upgrade for the remaining {{ $daysLeft }} days of your plan: <span class="text-white font-semibold">about {{ $xaf($upgradeCost) }}</span>. Our team confirms the exact amount with the payment.</p>
                    @include('recruiter.subscription._payment-fields', ['instructions' => $instructions, 'paymentMethods' => $paymentMethods])
                    <button type="submit" class="btn btn-primary">Send upgrade request</button>
                </form>
            @endif
        </div>
    @endif

    <!-- Plans -->
    <div class="grid md:grid-cols-3 gap-6">
        @foreach($plans as $tier => $plan)
            <div class="card p-6 relative flex flex-col {{ ($plan['featured'] ?? false) ? 'border-2 border-primary-500 ring-1 ring-primary-500/20' : '' }}"
                 x-data="{ open: {{ old('tier') === $tier && !old('upgrade') && $errors->any() ? 'true' : 'false' }}, months: {{ (int) old('months', 12) }} }">
                @if($plan['featured'] ?? false)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary-600 text-white text-xs font-bold rounded-full">Most Popular</div>
                @endif

                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold text-white">{{ $plan['name'] }}</h3>
                    <div class="mt-3">
                        @if($plan['annual'] > 0)
                            <span class="text-3xl font-bold text-white">{{ number_format($plan['annual'], 0, '.', ' ') }}</span>
                            <span class="text-dark-400 text-sm">XAF / year</span>
                        @else
                            <span class="text-3xl font-bold text-white">0</span>
                            <span class="text-dark-400 text-sm">XAF</span>
                        @endif
                    </div>
                </div>

                <ul class="space-y-3 mb-6 flex-1">
                    @foreach($plan['features'] as $feature)
                        <li class="flex items-center gap-2 text-sm text-dark-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                @if($currentTier === $tier && $isPaidCurrent)
                    <p class="text-center text-sm text-green-400">Your current plan</p>
                @elseif($tier === 'free')
                    @if(!$currentSubscription && !$trialUsed && (int) ($pricing['trial_days'] ?? 0) > 0)
                        <form method="POST" action="{{ route('recruiter.subscription.trial') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-full">Start {{ (int) $pricing['trial_days'] }}-day free trial</button>
                        </form>
                    @elseif($currentTier !== 'free' || ($currentSubscription && $currentSubscription->status->value === 'trial'))
                        <form method="POST" action="{{ route('recruiter.subscription.subscribe') }}"
                              @if($isPaidCurrent) onsubmit="return confirm('Switch to the Free plan? Your current plan ends now.')" @endif>
                            @csrf
                            <input type="hidden" name="tier" value="free">
                            <button type="submit" class="btn btn-secondary w-full">Use the Free plan</button>
                        </form>
                    @else
                        <p class="text-center text-sm text-green-400">Your current plan</p>
                    @endif
                @else
                    <button type="button" @click="open = !open" class="btn {{ ($plan['featured'] ?? false) ? 'btn-primary' : 'btn-secondary' }} w-full">
                        Request {{ $plan['name'] }}
                    </button>

                    <form x-show="open" x-cloak method="POST" action="{{ route('recruiter.subscription.subscribe') }}" class="mt-4 space-y-4 text-left">
                        @csrf
                        <input type="hidden" name="tier" value="{{ $tier }}">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Period</label>
                            <select name="months" x-model.number="months" class="form-input w-full">
                                @foreach([12 => '12 months', 6 => '6 months', 1 => '1 month'] as $m => $label)
                                    <option value="{{ $m }}">{{ $label }} · {{ $xaf($priceFor($plan['annual'], $m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @include('recruiter.subscription._payment-fields', ['instructions' => $instructions, 'paymentMethods' => $paymentMethods])
                        <button type="submit" class="btn btn-primary w-full">Send request</button>
                        <p class="text-xs text-dark-500">Your plan starts when our team confirms the payment.{{ $pendingSubscription ? ' This replaces your current request.' : '' }}</p>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <!-- FAQ -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <div>
                <h4 class="text-dark-200 font-medium">How do I pay?</h4>
                <p class="text-dark-400 text-sm mt-1">Pay by MTN Mobile Money, Orange Money, bank transfer or cash at our office, then send the request with your transaction reference. Your plan starts as soon as we confirm the payment, usually within one working day.</p>
            </div>
            <div>
                <h4 class="text-dark-200 font-medium">Can I cancel anytime?</h4>
                <p class="text-dark-400 text-sm mt-1">Yes. Cancelling ends marketplace access immediately. Contact us about any refund for the unused period.</p>
            </div>
        </div>
    </div>
</div>
@endsection
