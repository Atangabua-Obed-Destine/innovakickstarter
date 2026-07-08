<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * FAQ Model
 * 
 * Manages frequently asked questions for different audiences.
 * Supports categorization and featured status.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FAQ extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    /**
     * FAQ categories
     */
    public const CATEGORIES = [
        'general' => 'General',
        'fellows' => 'For Fellows',
        'recruiters' => 'For Recruiters',
        'mentors' => 'For Mentors',
        'pricing' => 'Pricing & Billing',
        'technical' => 'Technical',
    ];

    protected $fillable = [
        'question',
        'answer',
        'category',
        'is_featured',
        'is_active',
        'sort_order',
        'view_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
    ];

    protected const CACHE_KEY = 'faqs_active';
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
     * Get all active FAQs grouped by category.
     */
    public static function getGroupedByCategory(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                $faqs = static::where('is_active', true)
                    ->orderBy('category')
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->get();

                $grouped = [];
                foreach (self::CATEGORIES as $key => $label) {
                    $categoryFaqs = $faqs->where('category', $key)->values();
                    if ($categoryFaqs->isNotEmpty()) {
                        $grouped[$key] = [
                            'label' => $label,
                            'faqs' => $categoryFaqs,
                        ];
                    }
                }
                return $grouped;
            }
        );
    }

    /**
     * Get featured FAQs for landing page.
     */
    public static function getFeatured(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Increment view count.
     */
    public function recordView(): void
    {
        $this->increment('view_count');
    }

    /**
     * Scope: Active only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Featured
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By category
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
