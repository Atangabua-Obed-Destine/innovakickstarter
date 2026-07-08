<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Site Content Model
 * 
 * Manages all dynamic content sections for the landing page and other public pages.
 * Supports different content types: text, html, json, image.
 * All landing page content is admin-editable through this model.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class SiteContent extends Model
{
    use HasFactory;

    /**
     * Content sections for organization
     */
    public const SECTIONS = [
        'hero' => 'Hero Section',
        'stats' => 'Statistics',
        'pillars' => 'Four Pillars',
        'tracks' => 'Career Tracks',
        'how_it_works' => 'How It Works',
        'interviews' => 'Mock Interviews',
        'testimonials' => 'Testimonials',
        'cta' => 'Call to Action',
        'recruiter_cta' => 'Recruiter CTA',
        'footer' => 'Footer',
        'meta' => 'Meta/SEO',
    ];

    /**
     * Content types supported
     */
    public const TYPES = [
        'text' => 'Plain Text',
        'html' => 'Rich HTML',
        'json' => 'JSON Data',
        'image' => 'Image URL',
    ];

    protected $fillable = [
        'key',
        'section',
        'label',
        'value',
        'type',
        'description',
        'is_required',
        'sort_order',
        'updated_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Cache key prefix
     */
    protected const CACHE_PREFIX = 'site_content_';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clear cache on save
        static::saved(function (SiteContent $content) {
            Cache::forget(self::CACHE_PREFIX . $content->key);
            Cache::forget(self::CACHE_PREFIX . 'section_' . $content->section);
            Cache::forget(self::CACHE_PREFIX . 'all');
        });

        // Clear cache on delete
        static::deleted(function (SiteContent $content) {
            Cache::forget(self::CACHE_PREFIX . $content->key);
            Cache::forget(self::CACHE_PREFIX . 'section_' . $content->section);
            Cache::forget(self::CACHE_PREFIX . 'all');
        });
    }

    /**
     * Get content by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_TTL,
            fn() => static::where('key', $key)->first()?->getValue() ?? $default
        );
    }

    /**
     * Get all content for a section.
     */
    public static function getSection(string $section): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'section_' . $section,
            self::CACHE_TTL,
            function () use ($section) {
                $items = static::where('section', $section)
                    ->orderBy('sort_order')
                    ->get();
                
                $result = [];
                foreach ($items as $item) {
                    $result[$item->key] = $item->getValue();
                }
                return $result;
            }
        );
    }

    /**
     * Get all content grouped by section.
     */
    public static function getAllGrouped(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'all',
            self::CACHE_TTL,
            function () {
                $items = static::orderBy('section')
                    ->orderBy('sort_order')
                    ->get();
                
                $grouped = [];
                foreach ($items as $item) {
                    $grouped[$item->section][$item->key] = $item->getValue();
                }
                return $grouped;
            }
        );
    }

    /**
     * Get the typed value.
     */
    public function getValue(): mixed
    {
        return match($this->type) {
            'json' => json_decode($this->value, true) ?? [],
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            default => $this->value,
        };
    }

    /**
     * Set value with automatic type handling.
     */
    public function setValue(mixed $value): void
    {
        $this->value = match($this->type) {
            'json' => is_array($value) ? json_encode($value) : $value,
            'boolean' => $value ? 'true' : 'false',
            default => (string) $value,
        };
    }

    /**
     * Relationship: Who last updated
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: By section
     */
    public function scopeInSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    /**
     * Get section label.
     */
    public function getSectionLabelAttribute(): string
    {
        return self::SECTIONS[$this->section] ?? ucfirst($this->section);
    }
}
