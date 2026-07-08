<?php

namespace App\Repositories\Contracts;

use App\Enums\UserRole;
use App\Enums\Tier;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Fellow Repository Interface
 * 
 * Defines specialized methods for fellow (user) operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface FellowRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all fellows with their primary track.
     */
    public function getAllFellowsWithPrimaryTrack(): Collection;

    /**
     * Get fellows paginated with filters.
     */
    public function getFellowsPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get fellows by tier.
     */
    public function getByTier(Tier $tier): Collection;

    /**
     * Get fellows by track.
     */
    public function getByTrack(Track $track): Collection;

    /**
     * Get fellows by role.
     */
    public function getByRole(UserRole $role): Collection;

    /**
     * Get top fellows by Career Capital score.
     */
    public function getTopFellows(int $limit = 10, ?Track $track = null): Collection;

    /**
     * Get fellows with score above threshold.
     */
    public function getAboveScore(float $minScore): Collection;

    /**
     * Get fellows with incomplete weekly progress.
     */
    public function getWithIncompleteWeeklyProgress(): Collection;

    /**
     * Get recently active fellows.
     */
    public function getRecentlyActive(int $days = 7): Collection;

    /**
     * Get fellows who haven't been active.
     */
    public function getInactiveFellows(int $days = 30): Collection;

    /**
     * Search fellows by name or email.
     */
    public function search(string $term): Collection;

    /**
     * Get fellow with all relationships for profile.
     */
    public function getFullProfile(string $fellowId): ?User;

    /**
     * Get fellows for marketplace (visible to recruiters).
     */
    public function getMarketplaceFellows(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get fellow statistics.
     */
    public function getStatistics(string $fellowId): array;

    /**
     * Get fellow's rank within their track.
     */
    public function getRankInTrack(User $fellow, Track $track): int;

    /**
     * Get fellows eligible for tier promotion.
     */
    public function getEligibleForPromotion(): Collection;

    /**
     * Update fellow profile completion percentage.
     */
    public function updateProfileCompletion(string $fellowId): float;

    /**
     * Get fellows by subscription tier (for recruiter filtering).
     */
    public function getVisibleToRecruiter(User $recruiter, array $filters = []): LengthAwarePaginator;

    /**
     * Count fellows by tier.
     */
    public function countByTier(): array;

    /**
     * Count fellows by track.
     */
    public function countByTrack(): array;
}
