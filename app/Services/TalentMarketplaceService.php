<?php

namespace App\Services;

use App\Enums\Tier;
use App\Enums\TrackCategory;
use App\Models\AdminSetting;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Talent Marketplace Service
 * 
 * Handles talent discovery, search, and filtering for recruiters.
 * Powers the main marketplace where recruiters find and shortlist talent.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class TalentMarketplaceService
{
    /**
     * Search and filter talent.
     */
    public function search(array $filters = []): LengthAwarePaginator
    {
        $query = User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->with([
                'primaryTrack.track',
                'fellowTracks' => fn($q) => $q->with('track')->orderByDesc('score'),
            ]);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        return $query->paginate($filters['per_page'] ?? 12);
    }

    /**
     * Apply search filters.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        // Text search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('headline', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereJsonContains('skills', $search);
            });
        }

        // Track filter
        if (!empty($filters['track_id'])) {
            $query->whereHas('fellowTracks', function ($q) use ($filters) {
                $q->where('track_id', $filters['track_id']);
            });
        }

        // Track category filter
        if (!empty($filters['track_category'])) {
            $query->whereHas('fellowTracks.track', function ($q) use ($filters) {
                $q->where('category', $filters['track_category']);
            });
        }

        // Tier filter
        if (!empty($filters['tier'])) {
            $tiers = is_array($filters['tier']) ? $filters['tier'] : [$filters['tier']];
            $query->whereHas('primaryTrack', function ($q) use ($tiers) {
                $q->whereIn('tier', $tiers);
            });
        }

        // Minimum tier filter (includes all tiers at or above specified)
        if (!empty($filters['min_tier'])) {
            $minTier = Tier::from($filters['min_tier']);
            $validTiers = $this->getTiersAtOrAbove($minTier);
            $query->whereHas('primaryTrack', function ($q) use ($validTiers) {
                $q->whereIn('tier', $validTiers);
            });
        }

        // Score range filter
        if (!empty($filters['min_score'])) {
            $query->whereHas('primaryTrack', function ($q) use ($filters) {
                $q->where('score', '>=', $filters['min_score']);
            });
        }
        if (!empty($filters['max_score'])) {
            $query->whereHas('primaryTrack', function ($q) use ($filters) {
                $q->where('score', '<=', $filters['max_score']);
            });
        }

        // Skills filter
        if (!empty($filters['skills'])) {
            $skills = is_array($filters['skills']) ? $filters['skills'] : [$filters['skills']];
            foreach ($skills as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        // Location filter
        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        // Availability filter
        if (!empty($filters['availability'])) {
            $query->where('availability', $filters['availability']);
        }

        // Open to opportunities filter
        if (!empty($filters['open_to_opportunities'])) {
            $query->where('open_to_opportunities', true);
        }

        // Featured only filter (top scorers with complete profiles)
        if (!empty($filters['featured']) && $filters['featured']) {
            $query->whereHas('primaryTrack', fn($q) => $q->where('score', '>=', 75));
        }
    }

    /**
     * Apply sorting.
     */
    protected function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'score';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'score':
                $query->orderByRaw('(
                    SELECT score 
                    FROM fellow_tracks 
                    WHERE fellow_tracks.fellow_id = users.id 
                    AND fellow_tracks.is_primary = true 
                    LIMIT 1
                ) ' . $sortDirection);
                break;

            case 'name':
                $query->orderBy('name', $sortDirection);
                break;

            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;

            case 'tier':
                $query->orderByRaw('(
                    SELECT FIELD(tier, "elite", "professional", "intern", "rookie") 
                    FROM fellow_tracks 
                    WHERE fellow_tracks.fellow_id = users.id 
                    AND fellow_tracks.is_primary = true 
                    LIMIT 1
                ) ' . ($sortDirection === 'desc' ? 'asc' : 'desc'));
                break;

            case 'relevance':
            default:
                // Relevance combines score and activity
                $query->orderByRaw('(
                    SELECT score 
                    FROM fellow_tracks 
                    WHERE fellow_tracks.fellow_id = users.id 
                    AND fellow_tracks.is_primary = true 
                    LIMIT 1
                ) DESC')
                    ->orderBy('updated_at', 'desc');
                break;
        }
    }

    /**
     * Get tiers at or above a minimum.
     */
    protected function getTiersAtOrAbove(Tier $minTier): array
    {
        $tierOrder = [
            Tier::ROOKIE,
            Tier::INTERN,
            Tier::PROFESSIONAL,
            Tier::ELITE,
        ];

        $minIndex = array_search($minTier, $tierOrder);
        
        return array_slice($tierOrder, $minIndex);
    }

    /**
     * Get featured talent.
     */
    public function getFeatured(int $limit = 8): Collection
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereHas('primaryTrack', fn($q) => $q->where('score', '>=', 75))
            ->with(['primaryTrack.track'])
            ->orderByRaw('(
                SELECT score 
                FROM fellow_tracks 
                WHERE fellow_tracks.fellow_id = users.id 
                AND fellow_tracks.is_primary = true 
                LIMIT 1
            ) DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top talent by tier.
     */
    public function getTopByTier(Tier $tier, int $limit = 12): Collection
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereHas('primaryTrack', fn($q) => $q->where('tier', $tier))
            ->with(['primaryTrack.track'])
            ->orderByRaw('(
                SELECT score 
                FROM fellow_tracks 
                WHERE fellow_tracks.fellow_id = users.id 
                AND fellow_tracks.is_primary = true 
                LIMIT 1
            ) DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top talent by track.
     */
    public function getTopByTrack(Track $track, int $limit = 12): Collection
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereHas('fellowTracks', fn($q) => $q->where('track_id', $track->id))
            ->with(['primaryTrack.track', 'fellowTracks' => fn($q) => $q->where('track_id', $track->id)])
            ->orderByRaw('(
                SELECT score 
                FROM fellow_tracks 
                WHERE fellow_tracks.fellow_id = users.id 
                AND fellow_tracks.track_id = ? 
                LIMIT 1
            ) DESC', [$track->id])
            ->limit($limit)
            ->get();
    }

    /**
     * Get rising stars (highest recent growth).
     */
    public function getRisingStars(int $limit = 6): Collection
    {
        // Rising stars = fellows with biggest score increase in last 30 days
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereHas('activities', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30))
                    ->where('status', 'approved');
            })
            ->withCount(['activities as recent_activities' => function ($q) {
                $q->where('created_at', '>=', now()->subDays(30))
                    ->where('status', 'approved');
            }])
            ->with(['primaryTrack.track'])
            ->orderByDesc('recent_activities')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently active talent.
     */
    public function getRecentlyActive(int $limit = 12): Collection
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->orderByDesc('last_login_at')
            ->with(['primaryTrack.track'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get talent by skill.
     */
    public function getBySkill(string $skill, int $limit = 12): Collection
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereJsonContains('skills', $skill)
            ->with(['primaryTrack.track'])
            ->orderByRaw('(
                SELECT score 
                FROM fellow_tracks 
                WHERE fellow_tracks.fellow_id = users.id 
                AND fellow_tracks.is_primary = true 
                LIMIT 1
            ) DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get available tracks for filtering.
     */
    public function getAvailableTracks(): Collection
    {
        return Track::where('is_active', true)
            ->withCount(['fellowTracks' => function ($q) {
                $q->whereHas('fellow', fn($f) => 
                    $f->where('is_active', true)
                        ->where('is_public', true)
                );
            }])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available skills for filtering.
     */
    public function getAvailableSkills(): array
    {
        $fellows = User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereNotNull('skills')
            ->get();

        $skills = [];
        foreach ($fellows as $fellow) {
            if (is_array($fellow->skills)) {
                foreach ($fellow->skills as $skill) {
                    $key = strtolower(trim($skill));
                    if (!isset($skills[$key])) {
                        $skills[$key] = [
                            'name' => $skill,
                            'count' => 0,
                        ];
                    }
                    $skills[$key]['count']++;
                }
            }
        }

        // Sort by count descending
        uasort($skills, fn($a, $b) => $b['count'] <=> $a['count']);

        return array_values($skills);
    }

    /**
     * Get marketplace statistics.
     */
    public function getStatistics(): array
    {
        $baseQuery = User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true);

        return [
            'total_talent' => (clone $baseQuery)->count(),
            'by_tier' => [
                'elite' => $this->countByTier(Tier::ELITE),
                'professional' => $this->countByTier(Tier::PROFESSIONAL),
                'intern' => $this->countByTier(Tier::INTERN),
                'rookie' => $this->countByTier(Tier::ROOKIE),
            ],
            'by_track' => $this->countByTrack(),
            'average_score' => $this->calculateAverageScore(),
            'top_skills' => array_slice($this->getAvailableSkills(), 0, 10),
            'recently_joined' => (clone $baseQuery)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    /**
     * Count talent by tier.
     */
    protected function countByTier(Tier $tier): int
    {
        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->whereHas('primaryTrack', fn($q) => $q->where('tier', $tier))
            ->count();
    }

    /**
     * Count talent by track.
     */
    protected function countByTrack(): array
    {
        return Track::where('is_active', true)
            ->withCount(['fellowTracks' => function ($q) {
                $q->whereHas('fellow', fn($f) => 
                    $f->where('is_active', true)
                        ->where('is_public', true)
                );
            }])
            ->orderByDesc('fellow_tracks_count')
            ->get()
            ->mapWithKeys(fn($track) => [$track->name => $track->fellow_tracks_count])
            ->toArray();
    }

    /**
     * Calculate average Career Capital score.
     */
    protected function calculateAverageScore(): float
    {
        $result = FellowTrack::whereHas('fellow', fn($q) => 
            $q->where('is_active', true)
                ->where('is_public', true)
        )
            ->where('is_primary', true)
            ->avg('score');

        return round($result ?? 0, 2);
    }

    /**
     * Get similar talent to a specific fellow.
     */
    public function getSimilarTalent(User $fellow, int $limit = 6): Collection
    {
        $primaryTrack = $fellow->primaryTrack;
        
        if (!$primaryTrack) {
            return collect();
        }

        return User::role('fellow')
            ->where('is_active', true)
            ->where('is_public', true)
            ->where('id', '!=', $fellow->id)
            ->whereHas('fellowTracks', fn($q) => $q->where('track_id', $primaryTrack->track_id))
            ->whereHas('primaryTrack', function ($q) use ($primaryTrack) {
                // Similar tier (+/- 1 tier level)
                $q->whereIn('tier', $this->getAdjacentTiers(Tier::from($primaryTrack->tier)));
            })
            ->with(['primaryTrack.track'])
            ->orderByRaw('ABS((
                SELECT score 
                FROM fellow_tracks ft
                WHERE ft.fellow_id = users.id 
                AND ft.is_primary = true 
                LIMIT 1
            ) - ?) ASC', [$primaryTrack->score])
            ->limit($limit)
            ->get();
    }

    /**
     * Get adjacent tiers (current, one above, one below).
     */
    protected function getAdjacentTiers(Tier $tier): array
    {
        $tierOrder = [
            Tier::ROOKIE,
            Tier::INTERN,
            Tier::PROFESSIONAL,
            Tier::ELITE,
        ];

        $index = array_search($tier, $tierOrder);
        $adjacent = [$tier];

        if ($index > 0) {
            $adjacent[] = $tierOrder[$index - 1];
        }
        if ($index < count($tierOrder) - 1) {
            $adjacent[] = $tierOrder[$index + 1];
        }

        return $adjacent;
    }
}
