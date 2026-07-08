<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Testimonial Model
 * 
 * Manages testimonials displayed on the landing page and marketing pages.
 * Admin can CRUD testimonials with images, quotes, and featured status.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'company',
        'quote',
        'image_url',
        'track_id',
        'rating',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected const CACHE_KEY = 'testimonials_active';
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
     * Get active testimonials for display.
     */
    public static function getActive(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn() => static::where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit($limit)
                ->get()
        );
    }

    /**
     * Get featured testimonials.
     */
    public static function getFeatured(int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Relationship: Track
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
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
     * Get avatar URL with fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->image_url) {
            return $this->image_url;
        }
        
        // Generate UI Avatars fallback
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&size=64&background=8B5CF6&color=fff";
    }

    /**
     * Get star rating HTML.
     */
    public function getStarsHtmlAttribute(): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $filled = $i <= $this->rating ? 'text-yellow-400' : 'text-gray-300';
            $stars .= "<span class=\"{$filled}\">★</span>";
        }
        return $stars;
    }
}
