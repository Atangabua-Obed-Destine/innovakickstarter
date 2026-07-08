<?php

namespace App\Repositories;

use App\Enums\Tier;
use App\Enums\UserRole;
use App\Models\Track;
use App\Models\User;
use App\Repositories\Contracts\FellowRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Fellow Repository Implementation
 * 
 * Handles all fellow-related database operations with complex queries.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FellowRepository extends BaseRepository implements FellowRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return User::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllFellowsWithPrimaryTrack(): Collection
    {
        return $this->model
            ->fellows()
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFellowsPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->fellows()
            ->with(['primaryTrack', 'primaryFellowTrack']);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['track_id'])) {
            $query->whereHas('fellowTracks', function ($q) use ($filters) {
                $q->where('track_id', $filters['track_id']);
            });
        }

        if (!empty($filters['tier'])) {
            $query->whereHas('primaryFellowTrack', function ($q) use ($filters) {
                $q->where('tier', $filters['tier']);
            });
        }

        if (!empty($filters['min_score'])) {
            $query->whereHas('primaryFellowTrack', function ($q) use ($filters) {
                $q->where('score', '>=', $filters['min_score']);
            });
        }

        if (!empty($filters['max_score'])) {
            $query->whereHas('primaryFellowTrack', function ($q) use ($filters) {
                $q->where('score', '<=', $filters['max_score']);
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        if ($sortBy === 'score') {
            $query->leftJoin('fellow_tracks', function ($join) {
                $join->on('users.id', '=', 'fellow_tracks.fellow_id')
                    ->where('fellow_tracks.is_primary', true);
            })
            ->orderBy('fellow_tracks.score', $sortDir)
            ->select('users.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getByTier(Tier $tier): Collection
    {
        return $this->model
            ->fellows()
            ->whereHas('primaryFellowTrack', function ($query) use ($tier) {
                $query->where('tier', $tier);
            })
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByTrack(Track $track): Collection
    {
        return $this->model
            ->fellows()
            ->whereHas('fellowTracks', function ($query) use ($track) {
                $query->where('track_id', $track->id);
            })
            ->with(['fellowTracks' => function ($query) use ($track) {
                $query->where('track_id', $track->id);
            }])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByRole(UserRole $role): Collection
    {
        return $this->model
            ->where('role', $role)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTopFellows(int $limit = 10, ?Track $track = null): Collection
    {
        $query = $this->model->fellows();

        if ($track) {
            $query->whereHas('fellowTracks', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            })
            ->with(['fellowTracks' => function ($q) use ($track) {
                $q->where('track_id', $track->id);
            }])
            ->leftJoin('fellow_tracks', function ($join) use ($track) {
                $join->on('users.id', '=', 'fellow_tracks.fellow_id')
                    ->where('fellow_tracks.track_id', $track->id);
            });
        } else {
            $query->with(['primaryFellowTrack', 'primaryTrack'])
                ->leftJoin('fellow_tracks', function ($join) {
                    $join->on('users.id', '=', 'fellow_tracks.fellow_id')
                        ->where('fellow_tracks.is_primary', true);
                });
        }

        return $query
            ->orderByDesc('fellow_tracks.score')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAboveScore(float $minScore): Collection
    {
        return $this->model
            ->fellows()
            ->whereHas('primaryFellowTrack', function ($query) use ($minScore) {
                $query->where('score', '>=', $minScore);
            })
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWithIncompleteWeeklyProgress(): Collection
    {
        $currentWeek = now()->isoWeek();
        $currentYear = now()->isoWeekYear();

        return $this->model
            ->fellows()
            ->whereHas('weeklyProgress', function ($query) use ($currentWeek, $currentYear) {
                $query->where('week_number', $currentWeek)
                    ->where('year', $currentYear)
                    ->where('is_complete', false);
            })
            ->with(['primaryTrack', 'weeklyProgress' => function ($query) use ($currentWeek, $currentYear) {
                $query->where('week_number', $currentWeek)
                    ->where('year', $currentYear);
            }])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentlyActive(int $days = 7): Collection
    {
        return $this->model
            ->fellows()
            ->where('last_active_at', '>=', now()->subDays($days))
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->orderByDesc('last_active_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getInactiveFellows(int $days = 30): Collection
    {
        return $this->model
            ->fellows()
            ->where(function ($query) use ($days) {
                $query->whereNull('last_active_at')
                    ->orWhere('last_active_at', '<', now()->subDays($days));
            })
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term): Collection
    {
        return $this->model
            ->fellows()
            ->where(function ($query) use ($term) {
                $query->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('headline', 'like', "%{$term}%");
            })
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->limit(20)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFullProfile(string $fellowId): ?User
    {
        return $this->model
            ->with([
                'tracks',
                'fellowTracks.track',
                'activities' => function ($query) {
                    $query->approved()->latest()->limit(10);
                },
                'interviewSessions' => function ($query) {
                    $query->completed()->latest()->limit(5);
                },
                'weeklyProgress' => function ($query) {
                    $query->latest()->limit(12);
                },
            ])
            ->find($fellowId);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarketplaceFellows(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->fellows()
            ->where('is_profile_public', true)
            ->where('profile_completion', '>=', 70)
            ->with(['primaryTrack', 'primaryFellowTrack']);

        // Apply marketplace-specific filters
        if (!empty($filters['track_id'])) {
            $query->whereHas('fellowTracks', function ($q) use ($filters) {
                $q->where('track_id', $filters['track_id']);
            });
        }

        if (!empty($filters['tiers'])) {
            $query->whereHas('primaryFellowTrack', function ($q) use ($filters) {
                $q->whereIn('tier', $filters['tiers']);
            });
        }

        if (!empty($filters['min_score'])) {
            $query->whereHas('primaryFellowTrack', function ($q) use ($filters) {
                $q->where('score', '>=', $filters['min_score']);
            });
        }

        if (!empty($filters['skills'])) {
            foreach ($filters['skills'] as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        if (!empty($filters['available_for_hire'])) {
            $query->where('is_available_for_hire', true);
        }

        // Default sorting by score
        $query->leftJoin('fellow_tracks', function ($join) {
            $join->on('users.id', '=', 'fellow_tracks.fellow_id')
                ->where('fellow_tracks.is_primary', true);
        })
        ->orderByDesc('fellow_tracks.score')
        ->select('users.*');

        return $query->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatistics(string $fellowId): array
    {
        $fellow = $this->getFullProfile($fellowId);

        if (!$fellow) {
            return [];
        }

        $primaryTrack = $fellow->primaryFellowTrack;

        return [
            'career_capital_score' => $primaryTrack?->score ?? 0,
            'current_tier' => $primaryTrack?->tier ? ucfirst($primaryTrack->tier) : 'None',
            'total_activities' => $fellow->activities()->approved()->count(),
            'pending_activities' => $fellow->activities()->pending()->count(),
            'total_interviews' => $fellow->interviewSessions()->completed()->count(),
            'weekly_streak' => $fellow->getWeeklyStreak(),
            'weeks_active' => $fellow->weeklyProgress()->complete()->count(),
            'tracks_enrolled' => $fellow->fellowTracks()->count(),
            'profile_views' => $fellow->recruiterActions()->views()->count(),
            'shortlist_count' => $fellow->recruiterActions()->shortlists()->count(),
            'score_breakdown' => [
                'technical' => $primaryTrack?->technical_score ?? 0,
                'interview' => $primaryTrack?->interview_score ?? 0,
                'portfolio' => $primaryTrack?->portfolio_score ?? 0,
                'collaboration' => $primaryTrack?->collaboration_score ?? 0,
                'learning' => $primaryTrack?->learning_score ?? 0,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRankInTrack(User $fellow, Track $track): int
    {
        $fellowTrack = $fellow->fellowTracks()
            ->where('track_id', $track->id)
            ->first();

        if (!$fellowTrack) {
            return 0;
        }

        return $track->fellowTracks()
            ->where('score', '>', $fellowTrack->score)
            ->count() + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getEligibleForPromotion(): Collection
    {
        // Get fellows whose current tier doesn't match their score-based tier
        return $this->model
            ->fellows()
            ->with(['primaryFellowTrack', 'primaryTrack'])
            ->whereHas('primaryFellowTrack')
            ->get()
            ->filter(function ($fellow) {
                $fellowTrack = $fellow->primaryFellowTrack;
                $scoreBasedTier = Tier::fromScore($fellowTrack->score);
                return $fellowTrack->tier !== $scoreBasedTier->value;
            });
    }

    /**
     * {@inheritDoc}
     */
    public function updateProfileCompletion(string $fellowId): float
    {
        $fellow = $this->find($fellowId);

        if (!$fellow) {
            return 0;
        }

        $completion = $fellow->calculateProfileCompletion();
        $fellow->update(['profile_completion' => $completion]);

        return $completion;
    }

    /**
     * {@inheritDoc}
     */
    public function getVisibleToRecruiter(User $recruiter, array $filters = []): LengthAwarePaginator
    {
        $subscription = $recruiter->currentSubscription;
        $filters['min_score'] = $filters['min_score'] ?? 0;

        // Premium recruiters see all fellows
        // Partner recruiters see 25%+ (Intern and above)
        // Free recruiters see 50%+ (Professional and above)
        if (!$subscription || $subscription->tier->value === 'free') {
            $filters['min_score'] = max($filters['min_score'] ?? 0, 50);
        } elseif ($subscription->tier->value === 'partner') {
            $filters['min_score'] = max($filters['min_score'] ?? 0, 25);
        }

        return $this->getMarketplaceFellows($filters);
    }

    /**
     * {@inheritDoc}
     */
    public function countByTier(): array
    {
        $counts = [];

        foreach (Tier::cases() as $tier) {
            $counts[$tier->value] = $this->model
                ->fellows()
                ->whereHas('primaryFellowTrack', function ($query) use ($tier) {
                    $query->where('tier', $tier);
                })
                ->count();
        }

        return $counts;
    }

    /**
     * {@inheritDoc}
     */
    public function countByTrack(): array
    {
        return Track::active()
            ->withCount('fellows')
            ->get()
            ->pluck('fellows_count', 'name')
            ->toArray();
    }

    /**
     * Get fellows needing weekly reminder.
     */
    public function getNeedingWeeklyReminder(): Collection
    {
        return $this->getWithIncompleteWeeklyProgress()
            ->filter(function ($fellow) {
                // Only send reminder if week is ending soon (last 2 days)
                return now()->endOfWeek()->diffInDays(now()) <= 2;
            });
    }

    /**
     * Get fellows with frozen scores.
     */
    public function getWithFrozenScores(): Collection
    {
        return $this->model
            ->fellows()
            ->whereHas('weeklyProgress', function ($query) {
                $query->where('score_frozen', true)
                    ->where('year', now()->isoWeekYear());
            })
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->get();
    }

    /**
     * Get new fellows from the last N days.
     */
    public function getNewFellows(int $days = 30): Collection
    {
        return $this->model
            ->fellows()
            ->where('created_at', '>=', now()->subDays($days))
            ->with(['primaryTrack', 'primaryFellowTrack'])
            ->orderByDesc('created_at')
            ->get();
    }
}
