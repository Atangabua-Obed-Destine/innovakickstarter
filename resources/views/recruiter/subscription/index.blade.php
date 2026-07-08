@extends('layouts.app')

@section('title', 'Subscription & Billing')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Subscription & Billing</h1>
        <p class="text-dark-400 mt-1">Manage your subscription to access the I-NNOVA talent marketplace.</p>
    </div>

    <!-- Current Plan -->
    @if($currentSubscription)
    <div class="card p-6 border-l-4 border-green-500">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600/20 text-green-400">Active</span>
                <h2 class="text-xl font-bold text-white mt-2">{{ ucfirst($currentSubscription->plan ?? 'Standard') }} Plan</h2>
                <p class="text-dark-400 text-sm">Renews {{ $currentSubscription->expires_at?->format('M j, Y') ?? 'N/A' }}</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('recruiter.subscription.cancel') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to cancel your subscription?')">Cancel Plan</button>
                </form>
                <form method="POST" action="{{ route('recruiter.subscription.upgrade') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Upgrade</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Pricing Cards -->
    <div class="grid md:grid-cols-3 gap-6">
        @php
            $plans = [
                ['name' => 'Free', 'tier' => 'free', 'price' => $pricing['free'] ?? 0, 'period' => 'Forever', 'features' => ['Browse talent directory', 'View public profiles', 'Basic search filters', '20 profile views/month', '5 resume downloads/month'], 'color' => 'gray', 'action' => 'trial', 'label' => 'Start Free'],
                ['name' => 'Partner', 'tier' => 'partner', 'price' => $pricing['partner'] ?? 300000, 'period' => '/year', 'features' => ['Everything in Free', 'Unlimited profile views', 'Advanced search & filters', '5 warm introductions/month', 'Unlimited downloads', 'Pipeline management', 'Analytics dashboard'], 'color' => 'primary', 'featured' => true, 'action' => 'subscribe', 'label' => 'Subscribe Now'],
                ['name' => 'Premium', 'tier' => 'premium', 'price' => $pricing['premium'] ?? 1200000, 'period' => '/year', 'features' => ['Everything in Partner', 'Unlimited introductions', 'Priority talent access', 'Dedicated account manager', 'Custom talent reports', 'API access for ATS integration', 'Co-branded events'], 'color' => 'accent', 'action' => 'subscribe', 'label' => 'Contact Sales'],
            ];
        @endphp

        @foreach($plans as $plan)
        <div class="card p-6 relative {{ ($plan['featured'] ?? false) ? 'border-2 border-primary-500 ring-1 ring-primary-500/20' : '' }}">
            @if($plan['featured'] ?? false)
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-primary-600 text-white text-xs font-bold rounded-full">Most Popular</div>
            @endif
            <div class="text-center mb-6">
                <h3 class="text-lg font-bold text-white">{{ $plan['name'] }}</h3>
                <div class="mt-3">
                    <span class="text-4xl font-bold text-white">${{ $plan['price'] }}</span>
                    <span class="text-dark-400 text-sm">{{ $plan['period'] }}</span>
                </div>
            </div>
            <ul class="space-y-3 mb-8">
                @foreach($plan['features'] as $feature)
                <li class="flex items-center gap-2 text-sm text-dark-300">
                    <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
            @if(!$currentSubscription)
            <form method="POST" action="{{ $plan['action'] === 'trial' ? route('recruiter.subscription.trial') : route('recruiter.subscription.subscribe') }}">
                @csrf
                <input type="hidden" name="tier" value="{{ $plan['tier'] }}">
                <button type="submit" class="btn {{ ($plan['featured'] ?? false) ? 'btn-primary' : 'btn-secondary' }} w-full">{{ $plan['label'] }}</button>
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
                <h4 class="text-dark-200 font-medium">Can I cancel anytime?</h4>
                <p class="text-dark-400 text-sm mt-1">Yes, you can cancel your subscription at any time. You'll retain access until the end of your billing period.</p>
            </div>
            <div>
                <h4 class="text-dark-200 font-medium">What payment methods do you accept?</h4>
                <p class="text-dark-400 text-sm mt-1">We accept major credit cards, Mobile Money (MTN, Orange), and bank transfers for enterprise plans.</p>
            </div>
        </div>
    </div>
</div>
@endsection
