<?php

namespace App\Repositories\Contracts;

use App\Enums\TrackCategory;
use App\Models\Track;
use Illuminate\Database\Eloquent\Collection;

/**
 * Track Repository Interface
 * 
 * Defines specialized methods for track operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface TrackRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all active tracks.
     */
    public function getActiveTracks(): Collection;

    /**
     * Get tracks by category.
     */
    public function getByCategory(TrackCategory $category): Collection;

    /**
     * Get track with all relationships for display.
     */
    public function getWithDetails(string $trackId): ?Track;

    /**
     * Get tracks with fellow counts.
     */
    public function getWithFellowCounts(): Collection;

    /**
     * Get top fellows for a track.
     */
    public function getTopFellows(string $trackId, int $limit = 10): Collection;

    /**
     * Get track leaderboard.
     */
    public function getLeaderboard(string $trackId, int $limit = 100): Collection;

    /**
     * Get track statistics.
     */
    public function getStatistics(string $trackId): array;

    /**
     * Get tracks sorted by popularity.
     */
    public function getPopularTracks(int $limit = 10): Collection;

    /**
     * Search tracks by name or description.
     */
    public function search(string $term): Collection;

    /**
     * Get tracks available for enrollment.
     */
    public function getAvailableForEnrollment(): Collection;

    /**
     * Get track tier distribution.
     */
    public function getTierDistribution(string $trackId): array;

    /**
     * Get average score for a track.
     */
    public function getAverageScore(string $trackId): float;
}
