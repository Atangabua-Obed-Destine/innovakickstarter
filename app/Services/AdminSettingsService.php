<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Admin Settings Service
 * 
 * Handles system configuration and settings management.
 * Provides a centralized way to manage all platform settings.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AdminSettingsService
{
    /**
     * Get all settings grouped by category.
     */
    public function getAllGrouped(): array
    {
        $settings = AdminSetting::orderBy('group')
            ->orderBy('key')
            ->get();

        return $settings->groupBy('group')->toArray();
    }

    /**
     * Get settings by group.
     */
    public function getByGroup(string $group): Collection
    {
        return AdminSetting::where('group', $group)
            ->orderBy('key')
            ->get();
    }

    /**
     * Get a single setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return AdminSetting::get($key, $default);
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value, User $admin): AdminSetting
    {
        if (is_null($value)) {
            $value = '';
        }

        $setting = AdminSetting::where('key', $key)->first();
        $oldValue = $setting?->value;

        AdminSetting::set($key, $value, $admin->id);
        $setting = AdminSetting::where('key', $key)->first();

        // Log the change
        AuditLog::create([
            'fellow_id' => $admin->id,
            'admin_id' => $admin->id,
            'action' => 'setting_updated',
            'auditable_type' => AdminSetting::class,
            'auditable_id' => $setting->id,
            'justification' => "Setting '{$key}' updated",
            'old_values' => ['value' => $oldValue],
            'new_values' => ['value' => $value],
        ]);

        return $setting;
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $settings, User $admin): array
    {
        $updated = [];

        foreach ($settings as $key => $value) {
            $updated[$key] = $this->set($key, $value, $admin);
        }

        return $updated;
    }

    /**
     * Get tier threshold settings.
     */
    public function getTierThresholds(): array
    {
        return [
            'elite' => (float) $this->get('tier_elite_min', 75),
            'professional' => (float) $this->get('tier_professional_min', 50),
            'intern' => (float) $this->get('tier_intern_min', 25),
            'rookie' => (float) $this->get('tier_rookie_min', 0),
        ];
    }

    /**
     * Update tier thresholds.
     */
    public function updateTierThresholds(array $thresholds, User $admin): void
    {
        foreach ($thresholds as $tier => $value) {
            $this->set("tier_{$tier}_min", (float) $value, $admin);
        }
    }

    /**
     * Get career capital category weights.
     */
    public function getCategoryWeights(): array
    {
        return [
            'technical' => (float) $this->get('weight_technical', 30),
            'interview' => (float) $this->get('weight_interview', 25),
            'portfolio' => (float) $this->get('weight_portfolio', 20),
            'collaboration' => (float) $this->get('weight_collaboration', 15),
            'learning' => (float) $this->get('weight_learning', 10),
        ];
    }

    /**
     * Update category weights.
     */
    public function updateCategoryWeights(array $weights, User $admin): void
    {
        // Validate weights sum to 100
        $sum = array_sum($weights);
        if (abs($sum - 100) > 0.01) {
            throw new \InvalidArgumentException("Category weights must sum to 100%, got {$sum}%");
        }

        foreach ($weights as $category => $value) {
            $this->set("weight_{$category}", (float) $value, $admin);
        }
    }

    /**
     * Get interview limits.
     */
    public function getInterviewLimits(): array
    {
        return [
            'ai_weekly_limit' => (int) $this->get('ai_interview_weekly_limit', 0), // 0 = unlimited
            'human_weekly_limit' => (int) $this->get('human_interview_weekly_limit', 2),
            'daily_limit' => (int) $this->get('daily_interview_limit', 3),
        ];
    }

    /**
     * Get subscription pricing.
     */
    public function getSubscriptionPricing(): array
    {
        return [
            'free' => [
                'price' => (int) $this->get('free_price', 0),
                'profile_views' => (int) $this->get('free_profile_views', 20),
                'downloads' => (int) $this->get('free_downloads', 5),
                'intros' => 0,
            ],
            'partner' => [
                'price' => (int) $this->get('partner_price', 300000),
                'profile_views' => (int) $this->get('partner_profile_views', -1),
                'downloads' => (int) $this->get('partner_downloads', -1),
                'intros' => (int) $this->get('partner_intros', 5),
            ],
            'premium' => [
                'price' => (int) $this->get('premium_price', 1200000),
                'profile_views' => (int) $this->get('premium_profile_views', -1),
                'downloads' => (int) $this->get('premium_downloads', -1),
                'intros' => (int) $this->get('premium_intros', -1),
            ],
            'trial_days' => (int) $this->get('recruiter_trial_days', 14),
        ];
    }

    /**
     * Get platform settings.
     */
    public function getPlatformSettings(): array
    {
        return [
            'site_name' => $this->get('site_name', 'IKS Career Capital Platform'),
            'site_tagline' => $this->get('site_tagline', 'Transform Learning into Career Capital'),
            'contact_email' => $this->get('contact_email', 'support@iks.cm'),
            'max_tracks_per_fellow' => (int) $this->get('max_tracks_per_fellow', 3),
            'activity_approval_sla_hours' => (int) $this->get('activity_approval_sla_hours', 48),
            'weekly_reminder_day' => $this->get('weekly_reminder_day', 'friday'),
            'timezone' => $this->get('platform_timezone', 'Africa/Douala'),
        ];
    }

    /**
     * Get activity point values.
     */
    public function getActivityPoints(): array
    {
        return [
            'learning' => (int) $this->get('points_learning', 5),
            'project' => (int) $this->get('points_project', 15),
            'certification' => (int) $this->get('points_certification', 20),
            'networking' => (int) $this->get('points_networking', 8),
            'content_creation' => (int) $this->get('points_content_creation', 12),
            'mentorship' => (int) $this->get('points_mentorship', 10),
            'competition' => (int) $this->get('points_competition', 25),
            'speaking' => (int) $this->get('points_speaking', 18),
            'publication' => (int) $this->get('points_publication', 22),
            'workshop' => (int) $this->get('points_workshop', 15),
        ];
    }

    /**
     * Get penalty settings.
     */
    public function getPenaltySettings(): array
    {
        return [
            'weekly_incomplete_freeze' => (bool) $this->get('penalty_weekly_freeze', true),
            'inactivity_decay_days' => (int) $this->get('penalty_inactivity_days', 30),
            'inactivity_decay_percent' => (float) $this->get('penalty_inactivity_percent', 5),
        ];
    }

    /**
     * Initialize default settings.
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            // Tier thresholds
            ['key' => 'tier_elite_min', 'value' => 75, 'type' => 'number', 'group' => 'tiers', 'label' => 'Elite Threshold', 'description' => 'Minimum score for Elite tier'],
            ['key' => 'tier_professional_min', 'value' => 50, 'type' => 'number', 'group' => 'tiers', 'label' => 'Professional Threshold', 'description' => 'Minimum score for Professional tier'],
            ['key' => 'tier_intern_min', 'value' => 25, 'type' => 'number', 'group' => 'tiers', 'label' => 'Intern Threshold', 'description' => 'Minimum score for Intern tier'],
            ['key' => 'tier_rookie_min', 'value' => 0, 'type' => 'number', 'group' => 'tiers', 'label' => 'Rookie Threshold', 'description' => 'Minimum score for Rookie tier'],

            // Category weights
            ['key' => 'weight_technical', 'value' => 30, 'type' => 'number', 'group' => 'weights', 'label' => 'Technical Weight', 'description' => 'Weight for Technical Execution category'],
            ['key' => 'weight_interview', 'value' => 25, 'type' => 'number', 'group' => 'weights', 'label' => 'Interview Weight', 'description' => 'Weight for Interview Readiness category'],
            ['key' => 'weight_portfolio', 'value' => 20, 'type' => 'number', 'group' => 'weights', 'label' => 'Portfolio Weight', 'description' => 'Weight for Portfolio Quality category'],
            ['key' => 'weight_collaboration', 'value' => 15, 'type' => 'number', 'group' => 'weights', 'label' => 'Collaboration Weight', 'description' => 'Weight for Collaboration category'],
            ['key' => 'weight_learning', 'value' => 10, 'type' => 'number', 'group' => 'weights', 'label' => 'Learning Weight', 'description' => 'Weight for Continuous Learning category'],

            // Interview limits
            ['key' => 'ai_interview_weekly_limit', 'value' => 0, 'type' => 'number', 'group' => 'interviews', 'label' => 'AI Interview Weekly Limit', 'description' => 'Weekly AI interview limit (0 = unlimited)'],
            ['key' => 'human_interview_weekly_limit', 'value' => 2, 'type' => 'number', 'group' => 'interviews', 'label' => 'Human Interview Weekly Limit', 'description' => 'Weekly human interview limit'],
            ['key' => 'daily_interview_limit', 'value' => 3, 'type' => 'number', 'group' => 'interviews', 'label' => 'Daily Interview Limit', 'description' => 'Daily interview limit'],

            // Subscription pricing
            ['key' => 'free_price', 'value' => 0, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Free Plan Price (XAF)', 'description' => 'Free plan price (always 0)'],
            ['key' => 'free_profile_views', 'value' => 20, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Free Profile Views', 'description' => 'Free plan monthly profile views'],
            ['key' => 'free_downloads', 'value' => 5, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Free Downloads', 'description' => 'Free plan monthly downloads'],
            ['key' => 'partner_price', 'value' => 300000, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Partner Annual Price (XAF)', 'description' => 'Partner plan annual price'],
            ['key' => 'partner_profile_views', 'value' => -1, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Partner Profile Views', 'description' => 'Partner plan monthly profile views (-1 = unlimited)'],
            ['key' => 'partner_intros', 'value' => 5, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Partner Intros', 'description' => 'Partner plan monthly intro requests'],
            ['key' => 'premium_price', 'value' => 1200000, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Premium Annual Price (XAF)', 'description' => 'Premium plan annual price'],
            ['key' => 'premium_profile_views', 'value' => -1, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Premium Profile Views', 'description' => 'Premium plan monthly profile views (-1 = unlimited)'],
            ['key' => 'premium_intros', 'value' => -1, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Premium Intros', 'description' => 'Premium plan monthly intro requests (-1 = unlimited)'],
            ['key' => 'recruiter_trial_days', 'value' => 14, 'type' => 'number', 'group' => 'subscriptions', 'label' => 'Recruiter Trial Days', 'description' => 'Free trial duration in days'],

            // Platform settings
            ['key' => 'site_name', 'value' => 'IKS Career Capital Platform', 'type' => 'string', 'group' => 'platform', 'label' => 'Site Name', 'description' => 'Platform name'],
            ['key' => 'site_tagline', 'value' => 'Transform Learning into Career Capital', 'type' => 'string', 'group' => 'platform', 'label' => 'Site Tagline', 'description' => 'Platform tagline'],
            ['key' => 'contact_email', 'value' => 'support@iks.cm', 'type' => 'string', 'group' => 'platform', 'label' => 'Contact Email', 'description' => 'Support email'],
            ['key' => 'max_tracks_per_fellow', 'value' => 3, 'type' => 'number', 'group' => 'platform', 'label' => 'Max Tracks Per Fellow', 'description' => 'Maximum tracks per fellow'],
            ['key' => 'activity_approval_sla_hours', 'value' => 48, 'type' => 'number', 'group' => 'platform', 'label' => 'Activity Approval SLA', 'description' => 'Activity approval SLA in hours'],
            ['key' => 'weekly_reminder_day', 'value' => 'friday', 'type' => 'string', 'group' => 'platform', 'label' => 'Weekly Reminder Day', 'description' => 'Day to send weekly reminders'],
            ['key' => 'platform_timezone', 'value' => 'Africa/Douala', 'type' => 'string', 'group' => 'platform', 'label' => 'Platform Timezone', 'description' => 'Platform default timezone'],

            // Activity points
            ['key' => 'points_learning', 'value' => 5, 'type' => 'number', 'group' => 'points', 'label' => 'Learning Points', 'description' => 'Points for learning activities'],
            ['key' => 'points_project', 'value' => 15, 'type' => 'number', 'group' => 'points', 'label' => 'Project Points', 'description' => 'Points for project completion'],
            ['key' => 'points_certification', 'value' => 20, 'type' => 'number', 'group' => 'points', 'label' => 'Certification Points', 'description' => 'Points for certifications'],
            ['key' => 'points_networking', 'value' => 8, 'type' => 'number', 'group' => 'points', 'label' => 'Networking Points', 'description' => 'Points for networking activities'],
            ['key' => 'points_content_creation', 'value' => 12, 'type' => 'number', 'group' => 'points', 'label' => 'Content Creation Points', 'description' => 'Points for content creation'],
            ['key' => 'points_mentorship', 'value' => 10, 'type' => 'number', 'group' => 'points', 'label' => 'Mentorship Points', 'description' => 'Points for mentorship activities'],
            ['key' => 'points_competition', 'value' => 25, 'type' => 'number', 'group' => 'points', 'label' => 'Competition Points', 'description' => 'Points for competitions/hackathons'],
            ['key' => 'points_speaking', 'value' => 18, 'type' => 'number', 'group' => 'points', 'label' => 'Speaking Points', 'description' => 'Points for speaking engagements'],
            ['key' => 'points_publication', 'value' => 22, 'type' => 'number', 'group' => 'points', 'label' => 'Publication Points', 'description' => 'Points for publications'],
            ['key' => 'points_workshop', 'value' => 15, 'type' => 'number', 'group' => 'points', 'label' => 'Workshop Points', 'description' => 'Points for workshops'],

            // Penalty settings
            ['key' => 'penalty_weekly_freeze', 'value' => true, 'type' => 'boolean', 'group' => 'penalties', 'label' => 'Weekly Freeze Penalty', 'description' => 'Freeze score on incomplete weekly progress'],
            ['key' => 'penalty_inactivity_days', 'value' => 30, 'type' => 'number', 'group' => 'penalties', 'label' => 'Inactivity Days', 'description' => 'Days of inactivity before decay'],
            ['key' => 'penalty_inactivity_percent', 'value' => 5, 'type' => 'number', 'group' => 'penalties', 'label' => 'Inactivity Decay Percent', 'description' => 'Score decay percentage for inactivity'],
        ];

        foreach ($defaults as $setting) {
            AdminSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'label' => $setting['label'],
                    'description' => $setting['description'],
                    'is_public' => false,
                ]
            );
        }
    }

    /**
     * Reset a setting to its default value.
     */
    public function reset(string $key, User $admin): AdminSetting
    {
        // Get default value by calling initializeDefaults and checking
        $this->initializeDefaults();
        $setting = AdminSetting::where('key', $key)->first();

        if ($setting) {
            AuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'setting_reset',
                'auditable_type' => AdminSetting::class,
                'auditable_id' => $setting->id,
                'justification' => "Setting '{$key}' reset to default",
            ]);
        }

        return $setting;
    }

    /**
     * Export all settings.
     */
    public function export(): array
    {
        return AdminSetting::all()
            ->mapWithKeys(fn($s) => [$s->key => $s->value])
            ->toArray();
    }

    /**
     * Import settings.
     */
    public function import(array $settings, User $admin): int
    {
        $count = 0;

        foreach ($settings as $key => $value) {
            $this->set($key, $value, $admin);
            $count++;
        }

        return $count;
    }
}
