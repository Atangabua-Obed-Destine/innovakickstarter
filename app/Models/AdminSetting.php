<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * AdminSetting Model
 * 
 * Dynamic key-value configuration store for admin-controlled settings.
 * 
 * Categories of settings:
 * - scoring: Tier thresholds, activity point values, decay rates
 * - platform: Feature flags, maintenance mode, limits
 * - email: Templates, sender info, notification preferences
 * - marketplace: Recruiter settings, subscription pricing
 * 
 * All settings are cached for performance.
 * 
 * @property string $id UUID
 * @property string $key Unique setting key
 * @property mixed $value Setting value
 * @property string $type Data type for casting
 * @property string $group Setting group
 * @property string|null $description Human-readable description
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AdminSetting extends Model
{
    use HasFactory, HasUuids;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'input_type',
        'is_public',
        'is_readonly',
        'validation_rules',
        'options',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_readonly' => 'boolean',
            'options' => 'array',
        ];
    }

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'type' => 'string',
        'group' => 'general',
        'is_public' => false,
        'is_readonly' => false,
    ];

    /**
     * Cache prefix for settings.
     */
    protected static string $cachePrefix = 'admin_setting_';

    /**
     * Cache duration in seconds (1 hour).
     */
    protected static int $cacheDuration = 3600;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clear cache when setting is saved
        static::saved(function (AdminSetting $setting) {
            static::clearCache($setting->key);
            static::clearGroupCache($setting->group);
        });

        // Clear cache when setting is deleted
        static::deleted(function (AdminSetting $setting) {
            static::clearCache($setting->key);
            static::clearGroupCache($setting->group);
        });
    }

    // ==========================================
    // CONSTANTS - DEFAULT SETTINGS
    // ==========================================

    /**
     * Default settings configuration.
     */
    public const DEFAULTS = [
        // Tier Thresholds
        'tier_rookie_min' => ['value' => 0, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Rookie Tier Minimum Score'],
        'tier_intern_min' => ['value' => 25, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Intern Tier Minimum Score'],
        'tier_professional_min' => ['value' => 50, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Professional Tier Minimum Score'],
        'tier_elite_min' => ['value' => 75, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Elite Tier Minimum Score'],

        // Activity Point Values
        'activity_points_project' => ['value' => 20, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Project'],
        'activity_points_blog_post' => ['value' => 10, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Blog Post'],
        'activity_points_mentoring' => ['value' => 15, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Mentoring Session'],
        'activity_points_open_source' => ['value' => 15, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Open Source Contribution'],
        'activity_points_certification' => ['value' => 25, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Certification'],
        'activity_points_workshop' => ['value' => 10, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Workshop'],
        'activity_points_mock_interview' => ['value' => 10, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Mock Interview'],
        'activity_points_peer_review' => ['value' => 5, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Points per Peer Review'],

        // Interview Limits
        'weekly_ai_interviews_limit' => ['value' => 5, 'type' => 'integer', 'group' => 'platform', 'label' => 'Weekly AI Interview Limit'],
        'weekly_human_interviews_limit' => ['value' => 2, 'type' => 'integer', 'group' => 'platform', 'label' => 'Weekly Human Interview Limit'],

        // Score Decay
        'score_decay_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'scoring', 'label' => 'Enable Score Decay'],
        'score_decay_weekly_percentage' => ['value' => 2, 'type' => 'float', 'group' => 'scoring', 'label' => 'Weekly Score Decay Percentage'],
        'score_decay_max_weeks_inactive' => ['value' => 4, 'type' => 'integer', 'group' => 'scoring', 'label' => 'Max Weeks Inactive Before Decay'],

        // Platform Settings
        'maintenance_mode' => ['value' => false, 'type' => 'boolean', 'group' => 'platform', 'label' => 'Maintenance Mode'],
        'registration_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'platform', 'label' => 'Registration Enabled'],
        'recruiter_registration_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'platform', 'label' => 'Recruiter Registration Enabled'],
        'max_tracks_per_fellow' => ['value' => 3, 'type' => 'integer', 'group' => 'platform', 'label' => 'Max Tracks per Fellow'],
        'min_profile_completion' => ['value' => 70, 'type' => 'integer', 'group' => 'platform', 'label' => 'Min Profile Completion (%)'],

        // Marketplace Settings
        'free_tier_monthly_views' => ['value' => 10, 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Free Tier Monthly Profile Views'],
        'partner_tier_monthly_views' => ['value' => 100, 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Partner Tier Monthly Profile Views'],
        'partner_tier_price_xaf' => ['value' => 300000, 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Partner Tier Price (XAF)'],
        'premium_tier_price_xaf' => ['value' => 1200000, 'type' => 'integer', 'group' => 'marketplace', 'label' => 'Premium Tier Price (XAF)'],

        // Email Settings
        'email_verification_required' => ['value' => true, 'type' => 'boolean', 'group' => 'email', 'label' => 'Require Email Verification'],
        'email_sender_name' => ['value' => 'IKS Platform', 'type' => 'string', 'group' => 'email', 'label' => 'Email Sender Name'],
        'email_sender_address' => ['value' => 'noreply@iks.innova.cm', 'type' => 'string', 'group' => 'email', 'label' => 'Email Sender Address'],
        'email_weekly_digest_day' => ['value' => 'monday', 'type' => 'string', 'group' => 'email', 'label' => 'Weekly Digest Day'],

        // Brand Settings (public)
        'platform_name' => ['value' => 'IKS Career Capital', 'type' => 'string', 'group' => 'brand', 'is_public' => true, 'label' => 'Platform Name'],
        'company_name' => ['value' => 'I-NNOVA CMR', 'type' => 'string', 'group' => 'brand', 'is_public' => true, 'label' => 'Company Name'],
        'primary_color' => ['value' => '#7C3AED', 'type' => 'string', 'group' => 'brand', 'is_public' => true, 'label' => 'Primary Color'],
        'secondary_color' => ['value' => '#1E40AF', 'type' => 'string', 'group' => 'brand', 'is_public' => true, 'label' => 'Secondary Color'],
        'accent_color' => ['value' => '#14B8A6', 'type' => 'string', 'group' => 'brand', 'is_public' => true, 'label' => 'Accent Color'],
    ];

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the cast value.
     */
    public function getCastValueAttribute()
    {
        return $this->castValue($this->value, $this->type);
    }

    /**
     * Get display type.
     */
    public function getDisplayTypeAttribute(): string
    {
        return match($this->type) {
            'integer', 'float' => 'number',
            'boolean' => 'toggle',
            'json', 'array' => 'json',
            'text' => 'textarea',
            default => 'text',
        };
    }

    /**
     * Get group label.
     */
    public function getGroupLabelAttribute(): string
    {
        return match($this->group) {
            'scoring' => 'Scoring & Tiers',
            'platform' => 'Platform',
            'marketplace' => 'Marketplace',
            'email' => 'Email',
            'brand' => 'Branding',
            default => ucfirst($this->group),
        };
    }

    /**
     * Get group icon.
     */
    public function getGroupIconAttribute(): string
    {
        return match($this->group) {
            'scoring' => 'chart-bar',
            'platform' => 'cog',
            'marketplace' => 'shopping-bag',
            'email' => 'mail',
            'brand' => 'palette',
            default => 'adjustments',
        };
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = static::$cachePrefix . $key;

        return Cache::remember($cacheKey, static::$cacheDuration, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                // Check if there's a default
                if (isset(static::DEFAULTS[$key])) {
                    return static::castValue(
                        static::DEFAULTS[$key]['value'],
                        static::DEFAULTS[$key]['type']
                    );
                }
                return $default;
            }

            return $setting->cast_value;
        });
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, ?int $updatedBy = null): void
    {
        $setting = static::firstOrNew(['key' => $key]);
        
        // If new, set defaults from DEFAULTS array or generate sensible defaults
        if (!$setting->exists) {
            if (isset(static::DEFAULTS[$key])) {
                $setting->type = static::DEFAULTS[$key]['type'];
                $setting->group = static::DEFAULTS[$key]['group'];
                $setting->label = static::DEFAULTS[$key]['label'] ?? static::generateLabel($key);
                $setting->is_public = static::DEFAULTS[$key]['is_public'] ?? false;
            } else {
                // Auto-detect type and generate label for unknown keys
                $setting->type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_float($value) ? 'float' : (is_array($value) ? 'json' : 'string')));
                $setting->group = 'general';
                $setting->label = static::generateLabel($key);
            }
        }

        // Serialize arrays/objects
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $setting->value = $value;
        $setting->updated_by = $updatedBy;
        $setting->save();
    }

    /**
     * Generate a human-readable label from a setting key.
     */
    protected static function generateLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Get all settings in a group.
     */
    public static function getByGroup(string $group): array
    {
        $cacheKey = static::$cachePrefix . 'group_' . $group;

        return Cache::remember($cacheKey, static::$cacheDuration, function () use ($group) {
            $settings = static::where('group', $group)->get();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->cast_value;
            }

            return $result;
        });
    }

    /**
     * Get all public settings (for frontend).
     */
    public static function getPublic(): array
    {
        return Cache::remember(static::$cachePrefix . 'public', static::$cacheDuration, function () {
            $settings = static::where('is_public', true)->get();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->cast_value;
            }

            // Add defaults that are public
            foreach (static::DEFAULTS as $key => $config) {
                if (($config['is_public'] ?? false) && !isset($result[$key])) {
                    $result[$key] = static::castValue($config['value'], $config['type']);
                }
            }

            return $result;
        });
    }

    /**
     * Clear cache for a setting.
     */
    public static function clearCache(string $key): void
    {
        Cache::forget(static::$cachePrefix . $key);
    }

    /**
     * Clear cache for a group.
     */
    public static function clearGroupCache(string $group): void
    {
        Cache::forget(static::$cachePrefix . 'group_' . $group);
    }

    /**
     * Clear all settings cache.
     */
    public static function clearAllCache(): void
    {
        // Clear group caches
        foreach (['scoring', 'platform', 'marketplace', 'email', 'brand'] as $group) {
            Cache::forget(static::$cachePrefix . 'group_' . $group);
        }

        // Clear public cache
        Cache::forget(static::$cachePrefix . 'public');

        // Clear individual setting caches
        foreach (array_keys(static::DEFAULTS) as $key) {
            Cache::forget(static::$cachePrefix . $key);
        }
    }

    /**
     * Seed default settings.
     */
    public static function seedDefaults(): void
    {
        foreach (static::DEFAULTS as $key => $config) {
            static::firstOrCreate(
                ['key' => $key],
                [
                    'value' => is_array($config['value']) ? json_encode($config['value']) : (string) $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'label' => $config['label'] ?? static::generateLabel($key),
                    'is_public' => $config['is_public'] ?? false,
                ]
            );
        }
    }

    /**
     * Cast a value to its appropriate type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match($type) {
            'integer', 'int' => (int) $value,
            'float', 'double', 'decimal' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    // ==========================================
    // CONVENIENCE GETTERS
    // ==========================================

    /**
     * Get tier thresholds.
     */
    public static function getTierThresholds(): array
    {
        return [
            'rookie' => static::get('tier_rookie_min', 0),
            'intern' => static::get('tier_intern_min', 25),
            'professional' => static::get('tier_professional_min', 50),
            'elite' => static::get('tier_elite_min', 75),
        ];
    }

    /**
     * Get activity point values.
     */
    public static function getActivityPoints(): array
    {
        return [
            'project' => static::get('activity_points_project', 20),
            'blog_post' => static::get('activity_points_blog_post', 10),
            'mentoring' => static::get('activity_points_mentoring', 15),
            'open_source' => static::get('activity_points_open_source', 15),
            'certification' => static::get('activity_points_certification', 25),
            'workshop' => static::get('activity_points_workshop', 10),
            'mock_interview' => static::get('activity_points_mock_interview', 10),
            'peer_review' => static::get('activity_points_peer_review', 5),
        ];
    }

    /**
     * Check if maintenance mode is enabled.
     */
    public static function isMaintenanceMode(): bool
    {
        return static::get('maintenance_mode', false);
    }

    /**
     * Check if registration is enabled.
     */
    public static function isRegistrationEnabled(): bool
    {
        return static::get('registration_enabled', true) && !static::isMaintenanceMode();
    }

    /**
     * Get brand colors.
     */
    public static function getBrandColors(): array
    {
        return [
            'primary' => static::get('primary_color', '#7C3AED'),
            'secondary' => static::get('secondary_color', '#1E40AF'),
            'accent' => static::get('accent_color', '#14B8A6'),
        ];
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope by group.
     */
    public function scopeOfGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to editable settings.
     */
    public function scopeEditable($query)
    {
        return $query->where('is_readonly', false);
    }
}
