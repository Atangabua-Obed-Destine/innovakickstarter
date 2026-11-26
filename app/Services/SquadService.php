<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\Notification;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Squads for squad chapters (milestones with a squad_size): fellows who have reached the
 * chapter are grouped into teams that mix pathways (backend, frontend, mobile, full-stack),
 * so each squad can build a whole product together. Admins form, adjust and dissolve squads;
 * fellows see their squad on the chapter's activities.
 */
class SquadService
{
    public const NAMES = ['Mami Wata', 'Mount Cameroon', 'Waza', 'Lake Nyos', 'Bafut', 'Menchum', 'Kribi', 'Ekom', 'Mandara', 'Korup', 'Sanaga', 'Dja', 'Benoue', 'Lobe', 'Rhumsiki', 'Ngaoundaba'];

    public function isSquadChapter(?TrackMilestone $milestone): bool
    {
        return (int) ($milestone?->squad_size ?? 0) > 0;
    }

    public function squadFor(User $fellow, TrackMilestone $milestone): ?Squad
    {
        return Squad::where('milestone_id', $milestone->id)
            ->whereHas('members', fn ($q) => $q->where('fellow_id', $fellow->id))
            ->with('members.fellow:id,name,github_url,avatar_url')->first();
    }

    /** Fellows who have reached the chapter (any of its activities is open) and have no squad yet. */
    public function waiting(TrackMilestone $milestone): Collection
    {
        $inSquad = SquadMember::whereHas('squad', fn ($q) => $q->where('milestone_id', $milestone->id))->pluck('fellow_id');
        $ids = FellowCurriculumProgress::whereIn('curriculum_activity_id', $milestone->curriculumActivities()->pluck('id'))
            ->where('status', '!=', CurriculumStatus::LOCKED->value)
            ->whereNotIn('fellow_id', $inSquad)->distinct()->pluck('fellow_id');
        $pathways = app(PathwayService::class);
        $track = $milestone->track;

        return User::whereIn('id', $ids)->orderBy('name')->get(['id', 'name', 'email', 'github_url'])
            ->each(fn ($u) => $u->setAttribute('pathway', $pathways->major($u, $track)));
    }

    /**
     * Form squads from the waiting fellows, mixing pathways: fellows are dealt round-robin from
     * each pathway's queue, so every squad gets as many different pathways as possible.
     *
     * @return Collection<int, Squad> the new squads
     */
    public function form(TrackMilestone $milestone, ?int $size = null, ?User $by = null): Collection
    {
        $size = max(2, min(6, $size ?? (int) ($milestone->squad_size ?: 4)));
        $waiting = $this->waiting($milestone);
        if ($waiting->count() < 2) {
            return collect();
        }
        $count = max(1, (int) round($waiting->count() / $size));
        // Interleave pathways: backend, frontend, mobile, full-stack, none... then deal into squads in turn.
        $queues = $waiting->groupBy(fn ($u) => $u->pathway ?? 'none')->sortByDesc(fn ($g) => $g->count())->map->values();
        $order = collect();
        while ($queues->contains(fn ($q) => $q->isNotEmpty())) {
            foreach ($queues as $k => $q) {
                if ($q->isNotEmpty()) {
                    $order->push($q->shift());
                }
            }
        }
        // Each fellow joins the smallest squad that does not have their pathway yet (if any has room),
        // otherwise the smallest squad: squads stay even in size and as mixed as possible.
        $groups = array_fill(0, $count, []);
        $cap = (int) ceil($waiting->count() / $count);
        foreach ($order as $u) {
            $open = array_keys(array_filter($groups, fn ($g) => count($g) < $cap));
            $lacking = array_values(array_filter($open, fn ($i) => !in_array($u->pathway, array_map(fn ($m) => $m->pathway, $groups[$i]), true)));
            $pool = $lacking ?: $open;
            usort($pool, fn ($a, $b) => count($groups[$a]) <=> count($groups[$b]) ?: $a <=> $b);
            $groups[$pool[0]][] = $u;
        }

        return DB::transaction(function () use ($groups, $milestone, $by) {
            $used = Squad::where('milestone_id', $milestone->id)->pluck('name')->all();
            $free = array_values(array_diff(self::NAMES, $used));
            $made = collect();
            foreach ($groups as $i => $members) {
                if (!$members) {
                    continue;
                }
                $squad = Squad::create(['track_id' => $milestone->track_id, 'milestone_id' => $milestone->id,
                    'name' => 'Squad ' . ($free[$i] ?? ('#' . (count($used) + $i + 1))), 'created_by' => $by?->id]);
                foreach ($members as $u) {
                    $squad->members()->create(['fellow_id' => $u->id, 'pathway' => $u->pathway]);
                }
                $made->push($squad->load('members.fellow'));
            }
            $made->each(fn ($s) => $this->notifyMembers($s));

            return $made;
        });
    }

    /** Put a fellow in a squad (moving them out of any other squad of the same chapter). */
    public function assign(User $fellow, Squad $squad): void
    {
        DB::transaction(function () use ($fellow, $squad) {
            SquadMember::where('fellow_id', $fellow->id)->whereHas('squad', fn ($q) => $q->where('milestone_id', $squad->milestone_id))->delete();
            $squad->members()->create(['fellow_id' => $fellow->id, 'pathway' => app(PathwayService::class)->major($fellow, $squad->track)]);
        });
        $this->notifyMembers($squad->fresh('members.fellow'), $fellow);
    }

    public function remove(User $fellow, Squad $squad): void
    {
        $squad->members()->where('fellow_id', $fellow->id)->delete();
    }

    public function dissolve(Squad $squad): void
    {
        $squad->delete();
    }

    /** Tell squad members who is in the team (only $only when given, e.g. a newcomer). */
    public function notifyMembers(Squad $squad, ?User $only = null): void
    {
        $milestone = $squad->milestone;
        $list = $squad->members->map(fn ($m) => $m->fellow?->name . ($m->pathway ? " ({$m->pathway})" : ''))->filter()->implode(', ');
        $activity = $milestone?->curriculumActivities()->where('is_active', true)->orderBy('sequence_order')->first();
        foreach ($squad->members as $m) {
            if (!$m->fellow || ($only && $m->fellow_id !== $only->id)) {
                continue;
            }
            Notification::send($m->fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, "You are in {$squad->name}",
                "For \"{$milestone?->title}\" you work as a team: {$list}. Meet this week, agree your sprint plan, and share one repository.",
                ['action_url' => $activity ? route('curriculum.activity.show', $activity) : route('curriculum.index'), 'action_label' => 'Meet your squad']);
        }
    }
}
