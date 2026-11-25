<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Support\TrackExperience;

/**
 * How far a fellow has built each skill of their track: points earned on
 * approved activities tagged with the skill, out of the points available
 * on required activities tagged with it.
 */
class SkillMapService
{
    /**
     * @return array<int, array{key: string, label: string, description: string, earned: int, available: int, percent: int, completed: int, total: int}>
     */
    public function forFellow(User $fellow, Track $track): array
    {
        $skills = TrackExperience::for($track)?->skills() ?? [];
        if ($skills === []) {
            return [];
        }

        $activities = TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true)
            ->whereNotNull('skills')
            ->get(['id', 'points', 'skills', 'is_required']);

        $done = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('curriculum_activity_id', $activities->pluck('id'))
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->pluck('points_awarded', 'curriculum_activity_id');

        return array_map(function (array $skill) use ($activities, $done) {
            $tagged = $activities->filter(fn ($a) => in_array($skill['key'], (array) $a->skills, true));
            $required = $tagged->where('is_required', true);
            $available = (int) $required->sum('points');
            $earned = (int) $tagged->sum(fn ($a) => (int) ($done[$a->id] ?? 0));

            return [
                'key' => $skill['key'],
                'label' => $skill['label'],
                'description' => $skill['description'] ?? '',
                'earned' => $earned,
                'available' => $available,
                'percent' => $available > 0 ? (int) min(100, round(100 * $earned / $available)) : 0,
                'completed' => $required->filter(fn ($a) => isset($done[$a->id]))->count(),
                'total' => $required->count(),
            ];
        }, $skills);
    }
}
