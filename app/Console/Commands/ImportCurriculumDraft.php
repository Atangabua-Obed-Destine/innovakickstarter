<?php

namespace App\Console\Commands;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CareerCapitalCalculator;
use App\Services\CurriculumService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Publish an approved curriculum draft (JSON) to a track: rewrites existing
 * milestones and activities, adds new ones, renumbers them, then opens any
 * activity fellows can now reach and recalculates their scores.
 *
 * Draft format: {"milestones": [{"key", "sequence_order", "title", "description",
 * "estimated_duration_days", "activities": [{"key", "order", "title", "type", ...}]}]}
 * Existing records use their id as "key"; new ones use "new-<slug>". Every
 * existing milestone and activity of the track must be in the draft, so
 * nothing with fellow progress is dropped by accident.
 */
class ImportCurriculumDraft extends Command
{
    protected $signature = 'curriculum:import-draft {file : Path to the approved draft JSON}
        {--track=data-science : Track slug}
        {--dry-run : Validate and show what would change, without saving}
        {--no-catch-up : Make new lessons in chapters a fellow already finished required (default: recommended catch-up that never blocks)}';

    protected $description = 'Publish an approved curriculum draft to a track';

    protected const TYPES = ['project', 'research', 'blog_post', 'mock_interview', 'presentation', 'case_study', 'debug_challenge', 'reflection', 'other'];
    protected const LEVELS = ['beginner', 'intermediate', 'advanced', 'expert'];
    protected const CATEGORIES = ['technical', 'interview', 'portfolio', 'collaboration', 'learning'];
    protected const EVIDENCE = ['url', 'github_repo', 'github_commit', 'file_upload', 'text', 'screenshot', 'video', 'interview_session', 'social_post', 'presentation'];
    protected const RESOURCE_TYPES = ['link', 'file', 'youtube'];

    public function handle(CurriculumService $curriculum, CareerCapitalCalculator $calculator): int
    {
        $track = Track::where('slug', $this->option('track'))->firstOrFail();
        $draft = json_decode((string) file_get_contents($this->argument('file')), true);
        if (!is_array($draft['milestones'] ?? null)) {
            $this->error('The draft has no "milestones" array.');

            return self::FAILURE;
        }

        $errors = $this->validateDraft($track, $draft);
        if ($errors) {
            foreach ($errors as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $summary = $this->summarise($track, $draft);
        $this->table(['Milestone', 'Activities', 'New', 'Required', 'Optional', 'Longest deadline path (days)'], $summary);

        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing saved.');

            return self::SUCCESS;
        }

        $admin = User::role('admin')->orderBy('id')->value('id');

        // Which chapters each fellow had finished BEFORE the import: lessons added to them become catch-up.
        $finishedBefore = [];
        foreach (FellowTrack::where('track_id', $track->id)->pluck('fellow_id') as $fid) {
            $u = User::find($fid);
            if ($u) {
                $finishedBefore[$fid] = TrackMilestone::where('track_id', $track->id)->get()->filter(fn ($m) => $m->isCompletedBy($u))->pluck('id')->all();
            }
        }

        DB::transaction(function () use ($track, $draft, $admin) {
            if (is_array($draft['track_experience'] ?? null)) {
                $track->update(['experience' => $draft['track_experience']]);
            }

            // Free the sequence numbers first (milestones have a unique track + order).
            TrackMilestone::withTrashed()->where('track_id', $track->id)->update(['sequence_order' => DB::raw('sequence_order + 10000')]);

            $milestoneIds = [];
            $activityIds = [];
            foreach ($draft['milestones'] as $m) {
                $milestone = str_starts_with($m['key'], 'new-')
                    ? new TrackMilestone(['track_id' => $track->id, 'created_by' => $admin])
                    : TrackMilestone::findOrFail($m['key']);
                $milestone->fill([
                    'title' => $m['title'],
                    'description' => $m['description'],
                    'sequence_order' => (int) $m['sequence_order'],
                    'estimated_duration_days' => (int) ($m['estimated_duration_days'] ?? 14),
                    'story' => $m['story'] ?? $milestone->story,
                    'pathways' => array_key_exists('pathways', $m) ? ($m['pathways'] ?: null) : $milestone->pathways,
                    'is_active' => true,
                    'is_required' => true,
                ])->save();
                $milestoneIds[$m['key']] = $milestone->id;
                foreach ($m['activities'] as $a) {
                    $activityIds[$a['key']] = str_starts_with($a['key'], 'new-') ? (string) Str::uuid7() : $a['key'];
                }
            }

            $order = 0;
            foreach (collect($draft['milestones'])->sortBy('sequence_order') as $m) {
                foreach (collect($m['activities'])->sortBy('order') as $a) {
                    $order++;
                    $activity = str_starts_with($a['key'], 'new-')
                        ? (new TrackCurriculumActivity())->forceFill(['id' => $activityIds[$a['key']], 'track_id' => $track->id, 'created_by' => $admin])
                        : TrackCurriculumActivity::findOrFail($a['key']);

                    $activity->forceFill([
                        'milestone_id' => $milestoneIds[$m['key']],
                        'title' => $a['title'],
                        'description' => $a['description'],
                        'instructions' => $a['instructions'] ?? null,
                        'reviewer_guide' => $a['reviewer_guide'] ?? null,
                        'type' => $a['type'],
                        'difficulty_level' => $a['difficulty_level'],
                        'points' => (int) $a['points'],
                        'career_capital_category' => $a['career_capital_category'],
                        'is_required' => (bool) $a['is_required'],
                        'is_collaborative' => (bool) ($a['is_collaborative'] ?? false),
                        'requires_video' => array_key_exists('requires_video', $a) ? (bool) $a['requires_video'] : (bool) $activity->requires_video,
                        'skills' => array_key_exists('skills', $a) ? array_values((array) $a['skills']) : $activity->skills,
                        'is_active' => true,
                        'is_sequential' => false,
                        'sequence_order' => $order,
                        'deadline_days' => $a['deadline_days'] ?? null,
                        'grace_period_days' => (int) ($a['grace_period_days'] ?? 1),
                        'late_penalty_percent' => (int) ($a['late_penalty_percent'] ?? 10),
                        'evidence_requirements' => array_values($a['evidence_requirements'] ?? []),
                        'evaluation_rubric' => array_map(fn ($c) => [
                            'criterion' => $c['criterion'], 'weight' => (int) $c['weight'], 'description' => $c['description'] ?? '',
                        ], $a['evaluation_rubric'] ?? []),
                        'resources' => array_map(fn ($r) => ['type' => $r['type'], 'title' => $r['title'], 'content' => $r['content']], $a['resources'] ?? []),
                        'interview_config' => $a['interview_config'] ?? null,
                        'pathways' => array_key_exists('pathways', $a) ? ($a['pathways'] ?: null) : $activity->pathways,
                        'api_contract' => array_key_exists('api_contract', $a) ? ($a['api_contract'] ?: null) : $activity->api_contract,
                        'prerequisites' => array_values(array_map(fn ($k) => $activityIds[$k], $a['prerequisites'] ?? [])),
                        'chain_parent_id' => null,
                    ])->save();
                }
            }
        });

        // Catch-up: required lessons now in a chapter the fellow had already finished never block it.
        $catchUp = 0;
        if (!$this->option('no-catch-up')) {
            foreach ($finishedBefore as $fid => $milestoneIds) {
                if (!$milestoneIds) {
                    continue;
                }
                $done = FellowCurriculumProgress::where('fellow_id', $fid)->where('status', CurriculumStatus::COMPLETED->value)->pluck('curriculum_activity_id')->flip();
                foreach (TrackCurriculumActivity::whereIn('milestone_id', $milestoneIds)->where('is_active', true)->where('is_required', true)->get() as $act) {
                    if (isset($done[$act->id])) {
                        continue;
                    }
                    $row = FellowCurriculumProgress::firstOrNew(['fellow_id' => $fid, 'curriculum_activity_id' => $act->id]);
                    if (!$row->exists) {
                        $row->status = CurriculumStatus::AVAILABLE;
                    }
                    $row->catch_up = true;
                    $row->save();
                    $catchUp++;
                }
            }
        }

        // Fellows: create progress for new activities and open what is now reachable.
        $opened = 0;
        $fellows = User::whereIn('id', FellowTrack::where('track_id', $track->id)->pluck('fellow_id'))->get();
        foreach ($fellows as $fellow) {
            $curriculum->initializeFellowCurriculum($fellow, $track);
            $locked = FellowCurriculumProgress::with('curriculumActivity.milestone')
                ->where('fellow_id', $fellow->id)
                ->where('status', CurriculumStatus::LOCKED->value)
                ->whereHas('curriculumActivity', fn ($q) => $q->where('track_id', $track->id))
                ->get();
            foreach ($locked as $progress) {
                $activity = $progress->curriculumActivity;
                if ($activity && $activity->isAvailableFor($fellow)) {
                    $progress->update([
                        'status' => CurriculumStatus::AVAILABLE,
                        'deadline_at' => $activity->calculateDeadlineFor($fellow),
                        'grace_deadline_at' => $activity->calculateGraceDeadlineFor($fellow),
                    ]);
                    $opened++;
                }
            }
            $calculator->updateScore($fellow, $track, notify: false, markActivity: false);
        }

        $this->info("Imported. {$fellows->count()} fellows resynced, {$opened} activities opened, {$catchUp} catch-up lessons marked, scores recalculated.");

        return self::SUCCESS;
    }

    protected function validateDraft(Track $track, array $draft): array
    {
        $errors = [];
        $existingMilestones = TrackMilestone::where('track_id', $track->id)->pluck('id')->all();
        $existingActivities = TrackCurriculumActivity::where('track_id', $track->id)->pluck('id')->all();
        $milestoneKeys = array_column($draft['milestones'], 'key');
        $orders = array_column($draft['milestones'], 'sequence_order');
        if (count($orders) !== count(array_unique($orders))) {
            $errors[] = 'Two milestones have the same sequence_order.';
        }

        $activityKeys = [];
        $requiredKeys = [];
        foreach ($draft['milestones'] as $m) {
            if (!str_starts_with($m['key'], 'new-') && !in_array($m['key'], $existingMilestones, true)) {
                $errors[] = "Milestone {$m['key']} is not in this track.";
            }
            foreach ($m['activities'] ?? [] as $a) {
                if (in_array($a['key'], $activityKeys, true)) {
                    $errors[] = "Activity key {$a['key']} appears twice.";
                }
                $activityKeys[] = $a['key'];
                if (!empty($a['is_required'])) {
                    $requiredKeys[] = $a['key'];
                }
            }
        }
        foreach (array_diff($existingMilestones, $milestoneKeys) as $missing) {
            $errors[] = "Existing milestone {$missing} is missing from the draft.";
        }
        foreach (array_diff($existingActivities, $activityKeys) as $missing) {
            $errors[] = "Existing activity {$missing} is missing from the draft (fellows have progress on it).";
        }

        $skillKeys = array_column(($draft['track_experience']['skills'] ?? $track->experience['skills'] ?? []), 'key');
        foreach ($draft['milestones'] as $m) {
            foreach ($m['activities'] ?? [] as $a) {
                $label = "'" . ($a['title'] ?? $a['key']) . "'";
                foreach ((array) ($a['skills'] ?? []) as $skill) {
                    if (!in_array($skill, $skillKeys, true)) {
                        $errors[] = "{$label}: unknown skill {$skill}";
                    }
                }
                if (!str_starts_with($a['key'], 'new-') && !in_array($a['key'], $existingActivities, true)) {
                    $errors[] = "{$label}: activity {$a['key']} is not in this track.";
                }
                foreach (['title', 'description', 'type', 'difficulty_level', 'career_capital_category'] as $field) {
                    if (blank($a[$field] ?? null)) {
                        $errors[] = "{$label}: {$field} is missing.";
                    }
                }
                if (!in_array($a['type'] ?? '', self::TYPES, true)) {
                    $errors[] = "{$label}: unknown type " . ($a['type'] ?? '');
                }
                if (!in_array($a['difficulty_level'] ?? '', self::LEVELS, true)) {
                    $errors[] = "{$label}: unknown difficulty " . ($a['difficulty_level'] ?? '');
                }
                if (!in_array($a['career_capital_category'] ?? '', self::CATEGORIES, true)) {
                    $errors[] = "{$label}: unknown category " . ($a['career_capital_category'] ?? '');
                }
                foreach ($a['evidence_requirements'] ?? [] as $e) {
                    if (!in_array($e, self::EVIDENCE, true)) {
                        $errors[] = "{$label}: unknown evidence type {$e}";
                    }
                }
                foreach ($a['resources'] ?? [] as $r) {
                    if (!in_array($r['type'] ?? '', self::RESOURCE_TYPES, true) || !preg_match('#^https?://#', (string) ($r['content'] ?? ''))) {
                        $errors[] = "{$label}: bad resource " . json_encode($r);
                    }
                }
                $isInterview = ($a['type'] ?? '') === 'mock_interview';
                $weights = array_sum(array_map(fn ($c) => (int) ($c['weight'] ?? 0), $a['evaluation_rubric'] ?? []));
                if (!$isInterview && $weights !== 100) {
                    $errors[] = "{$label}: rubric weights add up to {$weights}, not 100.";
                }
                if ($isInterview && empty($a['interview_config']['type'])) {
                    $errors[] = "{$label}: mock interview without interview_config.type.";
                }
                if (!$isInterview && empty($a['evidence_requirements'])) {
                    $errors[] = "{$label}: no evidence requirements.";
                }
                foreach ($a['prerequisites'] ?? [] as $p) {
                    if (!in_array($p, $activityKeys, true) && !in_array($p, $existingActivities, true)) {
                        $errors[] = "{$label}: prerequisite {$p} is not in the draft.";
                    }
                    if (!empty($a['is_required']) && !in_array($p, $requiredKeys, true)) {
                        $errors[] = "{$label}: required activity depends on optional activity {$p}.";
                    }
                }
            }
        }

        return $errors;
    }

    /** Per milestone: counts and the longest chain of deadlines (activities wait for their prerequisites). */
    protected function summarise(Track $track, array $draft): array
    {
        $byKey = [];
        foreach ($draft['milestones'] as $m) {
            foreach ($m['activities'] as $a) {
                $byKey[$a['key']] = $a + ['_milestone' => $m['key']];
            }
        }
        $memo = [];
        $path = function (string $key) use (&$path, &$memo, $byKey) {
            if (isset($memo[$key])) {
                return $memo[$key];
            }
            $a = $byKey[$key] ?? null;
            if (!$a) {
                return 0;
            }
            $before = 0;
            foreach ($a['prerequisites'] ?? [] as $p) {
                // Only the part of the chain inside the same milestone counts toward its 14 days.
                if (($byKey[$p]['_milestone'] ?? null) === $a['_milestone']) {
                    $before = max($before, $path($p));
                }
            }

            return $memo[$key] = $before + (int) ($a['deadline_days'] ?? 0);
        };

        return collect($draft['milestones'])->sortBy('sequence_order')->map(fn ($m) => [
            $m['sequence_order'] . '. ' . $m['title'],
            count($m['activities']),
            collect($m['activities'])->filter(fn ($a) => str_starts_with($a['key'], 'new-'))->count(),
            collect($m['activities'])->where('is_required', true)->count(),
            collect($m['activities'])->where('is_required', false)->count(),
            collect($m['activities'])->where('is_required', true)->map(fn ($a) => $path($a['key']))->max() ?? 0,
        ])->values()->all();
    }
}
