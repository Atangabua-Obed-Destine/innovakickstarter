<?php

namespace App\Repositories;

use App\Enums\Tier;
use App\Enums\TrackCategory;
use App\Models\Track;
use App\Repositories\Contracts\TrackRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Track Repository Implementation
 * 
 * Handles all track-related database operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class TrackRepository extends BaseRepository implements TrackRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Track::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveTracks(): Collection
    {
        return $this->model
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByCategory(TrackCategory $category): Collection
    {
        return $this->model
            ->active()
            ->where('category', $category)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWithDetails(string $trackId): ?Track
    {
        return $this->model
            ->with([
                'fellowTracks' => function ($query) {
                    $query->orderByDesc('score')->limit(10);
                },
                'fellowTracks.fellow',
            ])
            ->find($trackId);
    }

    /**
     * {@inheritDoc}
     */
    public function getWithFellowCounts(): Collection
    {
        return $this->model
            ->active()
            ->withCount('fellows')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTopFellows(string $trackId, int $limit = 10): Collection
    {
        $track = $this->find($trackId);

        if (!$track) {
            return new Collection();
        }

        return $track->fellowTracks()
            ->with('fellow')
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->pluck('fellow');
    }

    /**
     * {@inheritDoc}
     */
    public function getLeaderboard(string $trackId, int $limit = 100): Collection
    {
        $track = $this->find($trackId);

        if (!$track) {
            return new Collection();
        }

        return $track->fellowTracks()
            ->with('fellow')
            ->orderByDesc('score')
            ->limit($limit)
            ->get()
            ->map(function ($fellowTrack, $index) {
                $tier = Tier::from($fellowTrack->tier);
                return [
                    'rank' => $index + 1,
                    'fellow' => $fellowTrack->fellow,
                    'score' => $fellowTrack->score,
                    'tier' => $fellowTrack->tier,
                    'tier_label' => $tier->label(),
                    'tier_color' => $tier->color(),
                ];
            });
    }

    /**
     * {@inheritDoc}
     */
    public function getStatistics(string $trackId): array
    {
        $track = $this->find($trackId);

        if (!$track) {
            return [];
        }

        $fellowTracks = $track->fellowTracks;

        return [
            'total_fellows' => $fellowTracks->count(),
            'average_score' => round($fellowTracks->avg('score') ?? 0, 2),
            'highest_score' => $fellowTracks->max('score') ?? 0,
            'lowest_score' => $fellowTracks->min('score') ?? 0,
            'tier_distribution' => $this->getTierDistribution($trackId),
            'category' => $track->category->label(),
            'category_icon' => $track->category->icon(),
            'total_activities' => $track->activities()->approved()->count(),
            'total_interviews' => $track->interviewSessions()->completed()->count(),
            'recently_joined' => $track->fellows()
                ->where('fellow_tracks.created_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPopularTracks(int $limit = 10): Collection
    {
        return $this->model
            ->active()
            ->withCount('fellows')
            ->orderByDesc('fellows_count')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term): Collection
    {
        return $this->model
            ->active()
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableForEnrollment(): Collection
    {
        return $this->model
            ->active()
            ->where('is_enrollable', true)
            ->withCount('fellows')
            ->having('fellows_count', '<', \DB::raw('max_fellows'))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTierDistribution(string $trackId): array
    {
        $track = $this->find($trackId);

        if (!$track) {
            return [];
        }

        $distribution = [];

        foreach (Tier::cases() as $tier) {
            $distribution[$tier->value] = [
                'label' => $tier->label(),
                'color' => $tier->color(),
                'count' => $track->fellowTracks()
                    ->where('tier', $tier->value)
                    ->count(),
            ];
        }

        $total = array_sum(array_column($distribution, 'count'));

        foreach ($distribution as $tier => &$data) {
            $data['percentage'] = $total > 0 
                ? round(($data['count'] / $total) * 100, 1) 
                : 0;
        }

        return $distribution;
    }

    /**
     * {@inheritDoc}
     */
    public function getAverageScore(string $trackId): float
    {
        $track = $this->find($trackId);

        if (!$track) {
            return 0;
        }

        return round($track->fellowTracks()->avg('score') ?? 0, 2);
    }

    /**
     * Get track by slug.
     */
    public function findBySlug(string $slug): ?Track
    {
        return $this->model
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get all tracks grouped by category.
     */
    public function getAllGroupedByCategory(): array
    {
        return $this->model
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($track) => $track->category->value)
            ->map(fn ($tracks, $category) => [
                'category' => TrackCategory::from($category),
                'label' => TrackCategory::from($category)->label(),
                'icon' => TrackCategory::from($category)->icon(),
                'tracks' => $tracks,
            ])
            ->toArray();
    }

    /**
     * Get tracks for dropdown selection.
     */
    public function getForDropdown(): array
    {
        return $this->model
            ->active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Update track statistics (cached counts, etc.)
     */
    public function refreshStatistics(string $trackId): void
    {
        $track = $this->find($trackId);

        if (!$track) {
            return;
        }

        $track->update([
            'cached_fellow_count' => $track->fellows()->count(),
            'cached_avg_score' => $track->fellowTracks()->avg('score') ?? 0,
        ]);
    }

    /**
     * Get suggested tracks for a fellow based on their skills.
     */
    public function getSuggestedTracks(array $skills, int $limit = 3): Collection
    {
        return $this->model
            ->active()
            ->where('is_enrollable', true)
            ->get()
            ->filter(function ($track) use ($skills) {
                // Check if track skills overlap with fellow skills
                $trackSkills = $track->required_skills ?? [];
                return !empty(array_intersect($skills, $trackSkills));
            })
            ->take($limit);
    }

    /**
     * Get track growth data for analytics.
     */
    public function getGrowthData(string $trackId, int $months = 6): array
    {
        $track = $this->find($trackId);

        if (!$track) {
            return [];
        }

        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $data[] = [
                'month' => $date->format('M Y'),
                'new_fellows' => $track->fellows()
                    ->whereBetween('fellow_tracks.created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'avg_score' => round($track->fellowTracks()
                    ->where('created_at', '<=', $endOfMonth)
                    ->avg('score') ?? 0, 2),
            ];
        }

        return $data;
    }
}
