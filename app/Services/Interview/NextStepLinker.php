<?php

namespace App\Services\Interview;

use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;

/**
 * Links a next step to the exact lessons and Practice Lab challenges that train a skill.
 */
class NextStepLinker
{
    /** @var array<string, \Illuminate\Support\Collection> */
    private array $lessons = [];

    /**
     * @return array<int, array{title: string, url: string, kind: string}>
     */
    public function links(Track $track, ?string $skill, ?string $milestoneId = null): array
    {
        if (!$skill) {
            return [];
        }
        $skill = strtolower(trim($skill));
        $lessons = $this->lessons[$track->id] ??= TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)
            ->where('type', '!=', 'mock_interview')
            ->with('milestone:id,sequence_order')
            ->get(['id', 'title', 'skills', 'milestone_id', 'sequence_order', 'type']);

        $matches = $lessons->filter(fn ($a) => in_array($skill, array_map('strtolower', (array) ($a->skills ?? [])), true))
            ->sortBy(fn ($a) => [$a->milestone_id === $milestoneId ? 0 : 1, -1 * (int) ($a->milestone?->sequence_order ?? 0), (int) $a->sequence_order])
            ->take(2);

        $links = $matches->map(fn ($a) => ['title' => $a->title, 'url' => route('curriculum.activity.show', $a->id), 'kind' => 'lesson'])->values()->all();

        $challenge = PracticeChallenge::where('track_id', $track->id)->where('is_active', true)->where('skill', $skill)->orderBy('difficulty')->orderBy('sort')->first();
        if ($challenge) {
            $links[] = ['title' => 'Practice Lab: ' . $challenge->title, 'url' => route('practice.show', $challenge), 'kind' => 'practice'];
        }

        return $links;
    }
}
