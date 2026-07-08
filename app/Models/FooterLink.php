<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Footer Link Model
 * 
 * Manages footer navigation links organized by column/group.
 * Supports external links, internal routes, and new tab behavior.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FooterLink extends Model
{
    use HasFactory;

    /**
     * Footer columns/groups
     */
    public const COLUMNS = [
        'product' => 'Product',
        'company' => 'Company',
        'resources' => 'Resources',
        'legal' => 'Legal',
        'social' => 'Social Media',
    ];

    protected $fillable = [
        'column',
        'label',
        'url',
        'route_name',
        'icon',
        'is_external',
        'open_new_tab',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'open_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected const CACHE_KEY = 'footer_links';
    protected const CACHE_TTL = 3600;

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn() => Cache::forget(self::CACHE_KEY));
        static::deleted(fn() => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Get all footer links grouped by column.
     */
    public static function getGrouped(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                $links = static::where('is_active', true)
                    ->orderBy('column')
                    ->orderBy('sort_order')
                    ->get();

                $grouped = [];
                foreach (self::COLUMNS as $key => $label) {
                    $grouped[$key] = [
                        'label' => $label,
                        'links' => $links->where('column', $key)->values(),
                    ];
                }
                return $grouped;
            }
        );
    }

    /**
     * Get social media links.
     */
    public static function getSocialLinks(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('column', 'social')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get the resolved URL.
     */
    public function getResolvedUrlAttribute(): string
    {
        if ($this->route_name && !$this->is_external) {
            try {
                return route($this->route_name);
            } catch (\Exception $e) {
                return $this->url ?? '#';
            }
        }
        return $this->url ?? '#';
    }

    /**
     * Get target attribute.
     */
    public function getTargetAttribute(): string
    {
        return $this->open_new_tab ? '_blank' : '_self';
    }

    /**
     * Get rel attribute for external links.
     */
    public function getRelAttribute(): ?string
    {
        return $this->is_external ? 'noopener noreferrer' : null;
    }

    /**
     * Scope: Active only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By column
     */
    public function scopeInColumn($query, string $column)
    {
        return $query->where('column', $column);
    }
}
