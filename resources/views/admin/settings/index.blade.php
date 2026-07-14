@extends('layouts.app')

@section('title', 'Platform Settings')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Platform Settings</h1>
            <p class="text-dark-400 mt-1">Configure system-wide settings and parameters</p>
        </div>
        <form action="{{ route('admin.settings.initialize') }}" method="POST">
            @csrf
            <button type="submit" class="btn-secondary" onclick="return confirm('This will reset all settings to defaults. Continue?')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset to Defaults
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Settings Tabs -->
    <div x-data="{ activeTab: 'platform' }" class="space-y-6">
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 border-b border-dark-700 pb-4">
            <button @click="activeTab = 'platform'" 
                    :class="activeTab === 'platform' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Platform
            </button>
            <button @click="activeTab = 'scoring'" 
                    :class="activeTab === 'scoring' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Scoring & Tiers
            </button>
            <button @click="activeTab = 'activities'" 
                    :class="activeTab === 'activities' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Activity Points
            </button>
            <button @click="activeTab = 'interviews'" 
                    :class="activeTab === 'interviews' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Interviews
            </button>
            <button @click="activeTab = 'subscriptions'" 
                    :class="activeTab === 'subscriptions' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Subscriptions
            </button>
            <button @click="activeTab = 'email'" 
                    :class="activeTab === 'email' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Email
            </button>
            <button @click="activeTab = 'branding'" 
                    :class="activeTab === 'branding' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                Branding
            </button>
        </div>

        <!-- Platform Settings -->
        <div x-show="activeTab === 'platform'" x-cloak>
            <form action="{{ route('admin.settings.update', 'platform') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-6">Platform Settings</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="site_name" class="block text-sm font-medium text-dark-300 mb-2">Site Name</label>
                        <input type="text" name="site_name" id="site_name" 
                               value="{{ $platformSettings['site_name'] ?? 'IKS Career Capital Platform' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label for="site_tagline" class="block text-sm font-medium text-dark-300 mb-2">Site Tagline</label>
                        <input type="text" name="site_tagline" id="site_tagline" 
                               value="{{ $platformSettings['site_tagline'] ?? '' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-dark-300 mb-2">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" 
                               value="{{ $platformSettings['contact_email'] ?? '' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label for="platform_timezone" class="block text-sm font-medium text-dark-300 mb-2">Platform Timezone</label>
                        <select name="platform_timezone" id="platform_timezone" class="input-field w-full">
                            @foreach(['Africa/Douala', 'Africa/Lagos', 'UTC', 'America/New_York', 'Europe/London'] as $tz)
                                <option value="{{ $tz }}" {{ ($platformSettings['platform_timezone'] ?? 'Africa/Douala') === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="max_tracks_per_fellow" class="block text-sm font-medium text-dark-300 mb-2">Max Tracks per Fellow</label>
                        <input type="number" name="max_tracks_per_fellow" id="max_tracks_per_fellow" 
                               value="{{ $platformSettings['max_tracks_per_fellow'] ?? 3 }}"
                               min="1" max="10"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label for="activity_approval_sla_hours" class="block text-sm font-medium text-dark-300 mb-2">Activity Approval SLA (hours)</label>
                        <input type="number" name="activity_approval_sla_hours" id="activity_approval_sla_hours" 
                               value="{{ $platformSettings['activity_approval_sla_hours'] ?? 48 }}"
                               min="1" max="168"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label for="weekly_reminder_day" class="block text-sm font-medium text-dark-300 mb-2">Weekly Reminder Day</label>
                        <select name="weekly_reminder_day" id="weekly_reminder_day" class="input-field w-full">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <option value="{{ $day }}" {{ ($platformSettings['weekly_reminder_day'] ?? 'friday') === $day ? 'selected' : '' }}>
                                    {{ ucfirst($day) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Platform Settings</button>
                </div>
            </form>
        </div>

        <!-- Scoring & Tiers Settings -->
        <div x-show="activeTab === 'scoring'" x-cloak>
            <div class="space-y-6">
                <!-- Tier Thresholds -->
                <form action="{{ route('admin.settings.update', 'tiers') }}" method="POST" class="card p-6">
                    @csrf
                    @method('PATCH')
                    
                    <h3 class="text-lg font-semibold text-white mb-4">Tier Thresholds</h3>
                    <p class="text-dark-400 text-sm mb-6">Set the Career Capital score thresholds for each tier</p>
                    
                    <div class="grid md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-amber-400 mr-2"></span>Elite
                            </label>
                            <input type="number" name="tier_elite_min" 
                                   value="{{ $tierThresholds['elite'] ?? 75 }}"
                                   min="0" max="100"
                                   class="input-field w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-purple-400 mr-2"></span>Professional
                            </label>
                            <input type="number" name="tier_professional_min" 
                                   value="{{ $tierThresholds['professional'] ?? 50 }}"
                                   min="0" max="100"
                                   class="input-field w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-blue-400 mr-2"></span>Intern
                            </label>
                            <input type="number" name="tier_intern_min" 
                                   value="{{ $tierThresholds['intern'] ?? 25 }}"
                                   min="0" max="100"
                                   class="input-field w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-gray-400 mr-2"></span>Rookie
                            </label>
                            <input type="number" name="tier_rookie_min" 
                                   value="{{ $tierThresholds['rookie'] ?? 0 }}"
                                   min="0" max="100"
                                   class="input-field w-full">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">Save Tier Thresholds</button>
                    </div>
                </form>

                <!-- Category Weights -->
                <form action="{{ route('admin.settings.update', 'weights') }}" method="POST" class="card p-6">
                    @csrf
                    @method('PATCH')
                    
                    <h3 class="text-lg font-semibold text-white mb-4">Category Weights</h3>
                    <p class="text-dark-400 text-sm mb-6">Configure how each category contributes to the Career Capital score (must total 100%)</p>
                    
                    <div class="grid md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Technical Execution</label>
                            <div class="relative">
                                <input type="number" name="weight_technical" 
                                       value="{{ $categoryWeights['technical'] ?? 30 }}"
                                       min="0" max="100"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400">%</span>
                            </div>
                            <div class="mt-2 text-xs text-dark-500 leading-tight">
                                {{ collect(\App\Enums\ActivityType::cases())->filter(fn($t) => $t->category() === \App\Enums\CareerCapitalCategory::TECHNICAL)->map(fn($t) => $t->label())->join(', ') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Interview Readiness</label>
                            <div class="relative">
                                <input type="number" name="weight_interview" 
                                       value="{{ $categoryWeights['interview'] ?? 25 }}"
                                       min="0" max="100"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400">%</span>
                            </div>
                            <div class="mt-2 text-xs text-dark-500 leading-tight">
                                {{ collect(\App\Enums\ActivityType::cases())->filter(fn($t) => $t->category() === \App\Enums\CareerCapitalCategory::INTERVIEW)->map(fn($t) => $t->label())->join(', ') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Portfolio Quality</label>
                            <div class="relative">
                                <input type="number" name="weight_portfolio" 
                                       value="{{ $categoryWeights['portfolio'] ?? 20 }}"
                                       min="0" max="100"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400">%</span>
                            </div>
                            <div class="mt-2 text-xs text-dark-500 leading-tight">
                                {{ collect(\App\Enums\ActivityType::cases())->filter(fn($t) => $t->category() === \App\Enums\CareerCapitalCategory::PORTFOLIO)->map(fn($t) => $t->label())->join(', ') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Collaboration</label>
                            <div class="relative">
                                <input type="number" name="weight_collaboration" 
                                       value="{{ $categoryWeights['collaboration'] ?? 15 }}"
                                       min="0" max="100"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400">%</span>
                            </div>
                            <div class="mt-2 text-xs text-dark-500 leading-tight">
                                {{ collect(\App\Enums\ActivityType::cases())->filter(fn($t) => $t->category() === \App\Enums\CareerCapitalCategory::COLLABORATION)->map(fn($t) => $t->label())->join(', ') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Continuous Learning</label>
                            <div class="relative">
                                <input type="number" name="weight_learning" 
                                       value="{{ $categoryWeights['learning'] ?? 10 }}"
                                       min="0" max="100"
                                       class="input-field w-full pr-8">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400">%</span>
                            </div>
                            <div class="mt-2 text-xs text-dark-500 leading-tight">
                                {{ collect(\App\Enums\ActivityType::cases())->filter(fn($t) => $t->category() === \App\Enums\CareerCapitalCategory::LEARNING)->map(fn($t) => $t->label())->join(', ') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">Save Category Weights</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Interview Settings -->
        <div x-show="activeTab === 'interviews'" x-cloak>
            <form action="{{ route('admin.settings.update', 'interviews') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-4">Interview Limits</h3>
                <p class="text-dark-400 text-sm mb-6">Configure interview session limits for fellows</p>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">AI Interviews (Weekly)</label>
                        <input type="number" name="ai_interview_weekly_limit" 
                               value="{{ $interviewLimits['ai_weekly'] ?? 5 }}"
                               min="0" max="100"
                               class="input-field w-full">
                        <p class="text-dark-500 text-xs mt-1">0 = unlimited</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Human Interviews (Weekly)</label>
                        <input type="number" name="human_interview_weekly_limit" 
                               value="{{ $interviewLimits['human_weekly'] ?? 2 }}"
                               min="0" max="100"
                               class="input-field w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Daily Limit (All Types)</label>
                        <input type="number" name="daily_interview_limit" 
                               value="{{ $interviewLimits['daily'] ?? 3 }}"
                               min="0" max="20"
                               class="input-field w-full">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Interview Settings</button>
                </div>
            </form>
        </div>

        <!-- Subscription Settings -->
        <div x-show="activeTab === 'subscriptions'" x-cloak>
            <form action="{{ route('admin.settings.update', 'subscriptions') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-4">Recruiter Subscription Pricing</h3>
                <p class="text-dark-400 text-sm mb-6">Configure subscription tiers for recruiter access</p>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Free Plan -->
                    <div class="bg-dark-800 rounded-lg p-4 border border-dark-700">
                        <h4 class="font-semibold text-white mb-4">Free Plan</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Price (XAF)</label>
                                <input type="number" step="1" name="free_price" 
                                       value="{{ $subscriptionPricing['free']['price'] ?? 0 }}"
                                       min="0"
                                       class="input-field w-full" disabled>
                                <p class="text-dark-500 text-xs mt-1">Free tier is always free</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Profile Views/Month</label>
                                <input type="number" name="free_profile_views" 
                                       value="{{ $subscriptionPricing['free']['profile_views'] ?? 20 }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Downloads/Month</label>
                                <input type="number" name="free_downloads" 
                                       value="{{ $subscriptionPricing['free']['downloads'] ?? 5 }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Partner Plan -->
                    <div class="bg-dark-800 rounded-lg p-4 border border-primary-500/50">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-white">Partner Plan</h4>
                            <span class="text-xs bg-primary-500/20 text-primary-400 px-2 py-1 rounded">Popular</span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Annual Price (XAF)</label>
                                <input type="number" step="1" name="partner_price" 
                                       value="{{ $subscriptionPricing['partner']['price'] ?? 300000 }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Profile Views/Month</label>
                                <input type="number" name="partner_profile_views" 
                                       value="{{ $subscriptionPricing['partner']['profile_views'] ?? -1 }}"
                                       class="input-field w-full">
                                <p class="text-dark-500 text-xs mt-1">-1 = unlimited</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Intro Requests/Month</label>
                                <input type="number" name="partner_intros" 
                                       value="{{ $subscriptionPricing['partner']['intros'] ?? 5 }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Premium Plan -->
                    <div class="bg-dark-800 rounded-lg p-4 border border-amber-500/50">
                        <h4 class="font-semibold text-amber-400 mb-4">Premium Plan</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Annual Price (XAF)</label>
                                <input type="number" step="1" name="premium_price" 
                                       value="{{ $subscriptionPricing['premium']['price'] ?? 1200000 }}"
                                       min="0"
                                       class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Profile Views/Month</label>
                                <input type="number" name="premium_profile_views" 
                                       value="{{ $subscriptionPricing['premium']['profile_views'] ?? -1 }}"
                                       class="input-field w-full">
                                <p class="text-dark-500 text-xs mt-1">-1 = unlimited</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Intro Requests/Month</label>
                                <input type="number" name="premium_intros" 
                                       value="{{ $subscriptionPricing['premium']['intros'] ?? -1 }}"
                                       class="input-field w-full">
                                <p class="text-dark-500 text-xs mt-1">-1 = unlimited</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-dark-300 mb-2">Trial Period (days)</label>
                    <input type="number" name="recruiter_trial_days" 
                           value="{{ $subscriptionPricing['trial_days'] ?? 14 }}"
                           min="0" max="90"
                           class="input-field w-48">
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Subscription Settings</button>
                </div>
            </form>
        </div>

        <!-- Activity Points Settings -->
        <div x-show="activeTab === 'activities'" x-cloak>
            <form action="{{ route('admin.settings.update', 'activities') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-6">Activity Point Values</h3>
                <p class="text-dark-400 mb-6">Configure the default point values awarded for each activity type.</p>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $activityTypes = [
                            'course_completion' => ['label' => 'Course Completion', 'default' => 50],
                            'certification' => ['label' => 'Certification', 'default' => 100],
                            'project' => ['label' => 'Project Completion', 'default' => 75],
                            'blog_post' => ['label' => 'Blog Post', 'default' => 30],
                            'open_source' => ['label' => 'Open Source Contribution', 'default' => 60],
                            'speaking' => ['label' => 'Speaking Engagement', 'default' => 80],
                            'mentoring' => ['label' => 'Mentoring Session', 'default' => 40],
                            'networking' => ['label' => 'Networking Event', 'default' => 25],
                            'workshop' => ['label' => 'Workshop Attendance', 'default' => 35],
                            'publication' => ['label' => 'Publication', 'default' => 90],
                            'hackathon' => ['label' => 'Hackathon', 'default' => 70],
                            'competition' => ['label' => 'Competition', 'default' => 85],
                        ];
                    @endphp
                    
                    @foreach($activityTypes as $key => $info)
                        <div class="bg-dark-800 rounded-lg p-4 border border-dark-700">
                            <label class="block text-sm font-medium text-dark-300 mb-2">{{ $info['label'] }}</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="activity_points_{{ $key }}" 
                                       value="{{ $settings['activity_points'][$key] ?? $info['default'] }}"
                                       min="0" max="500"
                                       class="input-field w-full">
                                <span class="text-dark-400">pts</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-8 pt-6 border-t border-dark-700">
                    <h4 class="text-md font-semibold text-white mb-4">Point Modifiers</h4>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Quality Bonus Multiplier</label>
                            <input type="number" step="0.1" name="quality_bonus_multiplier" 
                                   value="{{ $settings['quality_bonus_multiplier'] ?? 1.5 }}"
                                   min="1" max="3"
                                   class="input-field w-48">
                            <p class="text-dark-500 text-xs mt-1">Applied to exceptional quality activities</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">First Activity Bonus</label>
                            <input type="number" name="first_activity_bonus" 
                                   value="{{ $settings['first_activity_bonus'] ?? 25 }}"
                                   min="0" max="100"
                                   class="input-field w-48">
                            <p class="text-dark-500 text-xs mt-1">Bonus points for first activity in a track</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Activity Settings</button>
                </div>
            </form>
        </div>

        <!-- Email Settings -->
        <div x-show="activeTab === 'email'" x-cloak>
            <form action="{{ route('admin.settings.update', 'email') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-6">Email Configuration</h3>

                <div class="mb-8 p-4 rounded-xl border border-dark-700 bg-dark-900/40">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="email_verification_required" value="0">
                        <input type="checkbox" name="email_verification_required" value="1"
                               {{ \App\Models\AdminSetting::get('email_verification_required', true) ? 'checked' : '' }}
                               class="mt-1 rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                        <div>
                            <div class="text-white font-medium">Require email verification</div>
                            <p class="text-dark-400 text-sm mt-1">When enabled, new users must click a verification link before accessing their dashboard. Uncheck to auto-verify all sign-ups and bypass the verification screen.</p>
                        </div>
                    </label>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Sender Name</label>
                        <input type="text" name="email_sender_name" 
                               value="{{ $settings['email_sender_name'] ?? 'IKS Platform' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Sender Email</label>
                        <input type="email" name="email_sender_address" 
                               value="{{ $settings['email_sender_address'] ?? 'noreply@iks.innova.cm' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Reply-To Email</label>
                        <input type="email" name="email_reply_to" 
                               value="{{ $settings['email_reply_to'] ?? '' }}"
                               class="input-field w-full"
                               placeholder="support@iks.innova.cm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Weekly Digest Day</label>
                        <select name="email_weekly_digest_day" class="input-field w-full">
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <option value="{{ $day }}" {{ ($settings['email_weekly_digest_day'] ?? 'monday') === $day ? 'selected' : '' }}>
                                    {{ ucfirst($day) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-dark-700">
                    <h4 class="text-md font-semibold text-white mb-4">Email Notifications</h4>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="email_activity_approved" value="1"
                                   {{ ($settings['email_activity_approved'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Send email when activity is approved</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="email_interview_reminder" value="1"
                                   {{ ($settings['email_interview_reminder'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Send interview reminders</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="email_weekly_progress" value="1"
                                   {{ ($settings['email_weekly_progress'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Send weekly progress reports</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="email_tier_change" value="1"
                                   {{ ($settings['email_tier_change'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Send notification on tier changes</span>
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Email Settings</button>
                </div>
            </form>
        </div>

        <!-- Branding Settings -->
        <div x-show="activeTab === 'branding'" x-cloak>
            <form action="{{ route('admin.settings.update', 'branding') }}" method="POST" class="card p-6">
                @csrf
                @method('PATCH')
                
                <h3 class="text-lg font-semibold text-white mb-6">Branding & Appearance</h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Platform Name</label>
                        <input type="text" name="platform_name" 
                               value="{{ $settings['platform_name'] ?? 'IKS Career Capital' }}"
                               class="input-field w-full">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Company Name</label>
                        <input type="text" name="company_name" 
                               value="{{ $settings['company_name'] ?? 'I-NNOVA CMR' }}"
                               class="input-field w-full">
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-dark-700">
                    <h4 class="text-md font-semibold text-white mb-4">Brand Colors</h4>
                    <p class="text-dark-400 mb-4">These colors will be used throughout the platform.</p>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Primary Color</label>
                            <div class="flex items-center space-x-3">
                                <input type="color" name="primary_color" 
                                       value="{{ $settings['primary_color'] ?? '#7C3AED' }}"
                                       class="h-10 w-16 rounded cursor-pointer">
                                <input type="text" 
                                       value="{{ $settings['primary_color'] ?? '#7C3AED' }}"
                                       class="input-field flex-1" readonly>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Secondary Color</label>
                            <div class="flex items-center space-x-3">
                                <input type="color" name="secondary_color" 
                                       value="{{ $settings['secondary_color'] ?? '#1E40AF' }}"
                                       class="h-10 w-16 rounded cursor-pointer">
                                <input type="text" 
                                       value="{{ $settings['secondary_color'] ?? '#1E40AF' }}"
                                       class="input-field flex-1" readonly>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Accent Color</label>
                            <div class="flex items-center space-x-3">
                                <input type="color" name="accent_color" 
                                       value="{{ $settings['accent_color'] ?? '#14B8A6' }}"
                                       class="h-10 w-16 rounded cursor-pointer">
                                <input type="text" 
                                       value="{{ $settings['accent_color'] ?? '#14B8A6' }}"
                                       class="input-field flex-1" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-dark-700">
                    <h4 class="text-md font-semibold text-white mb-4">Feature Toggles</h4>
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_registration" value="1"
                                   {{ ($settings['enable_registration'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Enable new user registration</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_recruiter_registration" value="1"
                                   {{ ($settings['enable_recruiter_registration'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Enable recruiter registration</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="maintenance_mode" value="1"
                                   {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-dark-600 text-primary-600 focus:ring-primary-500">
                            <span class="ml-3 text-dark-300">Enable maintenance mode (only admins can access)</span>
                        </label>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Save Branding Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
