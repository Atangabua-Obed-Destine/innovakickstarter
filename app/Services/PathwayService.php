<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Pathways inside a track: a shared core, then one chosen pathway (the "major"),
 * optional chapters from other pathways ("minors"), then shared chapters again.
 *
 * Definitions live in tracks.experience.pathways:
 *   [{"key": "backend", "title": "Backend", "icon": "🛠️", "stack": "PHP + Laravel + MySQL",
 *     "alternatives": ["Node/Express", ...], "summary": "...", "demand": "..."}]
 * Chapters (milestones) carry `pathways` (null = everyone). Activities may also carry
 * `pathways` for role-specific work inside a shared chapter.
 *
 * A track without definitions behaves exactly as before (every method answers "applies").
 *
 * Gating for pathway tracks: a chapter unlocks when every EARLIER chapter that counts for
 * the fellow (core + major) is complete. Minors unlock once the core is complete and never
 * block anything. Lessons marked catch_up for a fellow never block a chapter either.
 */
class PathwayService
{
    /** @var array<string, ?FellowTrack> */
    private array $enrolments = [];

    public function definitions(?Track $track): array
    {
        return array_values(array_filter((array) ($track?->experience['pathways'] ?? []), fn ($p) => is_array($p) && !empty($p['key'])));
    }

    public function hasPathways(?Track $track): bool
    {
        return $this->definitions($track) !== [];
    }

    public function definition(Track $track, ?string $key): ?array
    {
        return collect($this->definitions($track))->firstWhere('key', $key);
    }

    public function enrolment(User $fellow, Track $track): ?FellowTrack
    {
        $k = $fellow->id . ':' . $track->id;
        if (!array_key_exists($k, $this->enrolments)) {
            $this->enrolments[$k] = FellowTrack::where('fellow_id', $fellow->id)->where('track_id', $track->id)->first();
        }

        return $this->enrolments[$k];
    }

    public function forget(User $fellow, Track $track): void
    {
        unset($this->enrolments[$fellow->id . ':' . $track->id]);
    }

    public function major(User $fellow, Track $track): ?string
    {
        return $this->enrolment($fellow, $track)?->pathway;
    }

    /** @return string[] milestone ids taken as minors */
    public function minors(User $fellow, Track $track): array
    {
        return array_values(array_map('strval', (array) ($this->enrolment($fellow, $track)?->minors ?? [])));
    }

    /**
     * How a chapter relates to a fellow: 'core' (everyone), 'major', 'minor', or null (not theirs).
     */
    public function role(User $fellow, TrackMilestone $milestone): ?string
    {
        $tags = (array) ($milestone->pathways ?? []);
        if ($tags === []) {
            return 'core';
        }
        $track = $milestone->relationLoaded('track') ? $milestone->track : Track::find($milestone->track_id);
        $major = $track ? $this->major($fellow, $track) : null;
        if ($major && in_array($major, $tags, true)) {
            return 'major';
        }
        if ($track && in_array((string) $milestone->id, $this->minors($fellow, $track), true)) {
            return 'minor';
        }

        return null;
    }

    public function appliesTo(User $fellow, TrackMilestone $milestone): bool
    {
        return $this->role($fellow, $milestone) !== null;
    }

    /** Does a (possibly role-specific) activity apply to this fellow? */
    public function activityApplies(User $fellow, TrackCurriculumActivity $activity): bool
    {
        $tags = (array) ($activity->pathways ?? []);
        if ($tags === []) {
            return true;
        }
        $track = $activity->relationLoaded('track') ? $activity->track : Track::find($activity->track_id);
        $major = $track ? $this->major($fellow, $track) : null;

        return $major === null || in_array($major, $tags, true);
    }

    /** Required, active activity ids of a chapter that count for this fellow (catch-up lessons excluded). */
    public function requiredIdsIn(User $fellow, TrackMilestone $milestone): Collection
    {
        $acts = $milestone->curriculumActivities()->where('is_required', true)->where('is_active', true)->get(['id', 'pathways', 'track_id']);
        $acts = $acts->filter(fn ($a) => $this->activityApplies($fellow, $a));
        $catchUp = FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $acts->pluck('id'))
            ->where('catch_up', true)->where('status', '!=', CurriculumStatus::COMPLETED->value)->pluck('curriculum_activity_id')->flip();

        return $acts->pluck('id')->reject(fn ($id) => isset($catchUp[$id]))->values();
    }

    /** The chapters that count for this fellow, in order, each with its role. */
    public function milestones(User $fellow, Track $track): Collection
    {
        return TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')->get()
            ->map(fn ($m) => $m->setAttribute('pathway_role', $this->role($fellow, $m)))
            ->filter(fn ($m) => $m->pathway_role !== null)->values();
    }

    /** Required activity ids across the track for this fellow (core + major; minors and catch-up excluded). */
    public function requiredActivityIds(User $fellow, Track $track): Collection
    {
        if (!$this->hasPathways($track)) {
            return TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->where('is_required', true)->pluck('id');
        }

        return $this->milestones($fellow, $track)->whereIn('pathway_role', ['core', 'major'])
            ->flatMap(fn ($m) => $this->requiredIdsIn($fellow, $m))->values();
    }

    /** Is this pathway-track chapter unlocked for the fellow? */
    public function isUnlocked(User $fellow, TrackMilestone $milestone): bool
    {
        $role = $this->role($fellow, $milestone);
        if ($role === null) {
            return false;
        }
        $earlier = TrackMilestone::where('track_id', $milestone->track_id)->where('is_active', true)
            ->where('sequence_order', '<', $milestone->sequence_order)->orderBy('sequence_order')->get();
        foreach ($earlier as $m) {
            $r = $this->role($fellow, $m);
            $needed = $role === 'minor' ? $r === 'core' : in_array($r, ['core', 'major'], true);
            if ($needed && !$m->isCompletedBy($fellow)) {
                return false;
            }
        }

        return true;
    }

    /** Is the shared core (all untagged chapters before the first pathway chapter) complete? */
    public function coreComplete(User $fellow, Track $track): bool
    {
        foreach (TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')->get() as $m) {
            if (!empty($m->pathways)) {
                return true;
            }
            if (!$m->isCompletedBy($fellow)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Choose a major pathway (and optional minor chapters). Keeps all completed work.
     *
     * @return array{ok: bool, error?: string}
     */
    public function choose(User $fellow, Track $track, string $pathway, array $minorMilestoneIds = []): array
    {
        if (!$this->definition($track, $pathway)) {
            return ['ok' => false, 'error' => 'That pathway does not exist in this track.'];
        }
        $enrolment = $this->enrolment($fellow, $track);
        if (!$enrolment) {
            return ['ok' => false, 'error' => 'You are not enrolled in this track.'];
        }
        $allowed = TrackMilestone::where('track_id', $track->id)->where('is_active', true)->whereNotNull('pathways')->get()
            ->reject(fn ($m) => in_array($pathway, (array) $m->pathways, true))->pluck('id')->map(fn ($id) => (string) $id)->all();
        $minors = array_values(array_unique(array_intersect(array_map('strval', $minorMilestoneIds), $allowed)));

        // The Job Market target role follows the pathway, unless the fellow picked another role themselves.
        $roleFor = fn (?string $p) => $p ? (collect(app(JobMarketService::class)->data($track)['roles'] ?? [])->first(fn ($r) => str_contains($r['key'], $p))['key'] ?? null) : null;
        $role = $enrolment->career_role;
        if (!$role || $role === $roleFor($enrolment->pathway)) {
            $role = $roleFor($pathway) ?? $role;
        }
        $enrolment->forceFill(['pathway' => $pathway, 'minors' => $minors, 'career_role' => $role, 'pathway_chosen_at' => $enrolment->pathway_chosen_at ?? now()])->save();
        $this->forget($fellow, $track);
        app(CurriculumService::class)->initializeFellowCurriculum($fellow, $track);

        return ['ok' => true];
    }
}
