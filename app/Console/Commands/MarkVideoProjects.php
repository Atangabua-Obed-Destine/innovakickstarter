<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use Illuminate\Console\Command;

/**
 * Show & Tell: mark the closing project of every chapter (the last active project
 * in each milestone) as needing a public YouTube video that explains the work.
 * Other activities keep their current setting unless --only is used.
 */
class MarkVideoProjects extends Command
{
    protected $signature = 'curriculum:video-projects {slug : Track slug}
        {--only : Also clear the flag on every other activity of the track}
        {--dry-run : Show what would change without saving}';

    protected $description = "Require a Show & Tell YouTube video on each chapter's closing project";

    public function handle(): int
    {
        $track = Track::where('slug', $this->argument('slug'))->first();
        if (!$track) {
            $this->error('No track with that slug.');

            return self::FAILURE;
        }

        $rows = [];
        $ids = [];
        foreach (TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')->get() as $i => $milestone) {
            $project = TrackCurriculumActivity::where('milestone_id', $milestone->id)
                ->where('is_active', true)
                ->where('type', 'project')
                ->orderByDesc('sequence_order')
                ->first();
            if (!$project) {
                $rows[] = ['Chapter ' . ($i + 1), $milestone->title, '(no project in this chapter)', '-'];
                continue;
            }
            $ids[] = $project->id;
            $rows[] = ['Chapter ' . ($i + 1), $milestone->title, $project->title, $project->requires_video ? 'already' : 'will require video'];
        }
        $this->table(['Chapter', 'Milestone', 'Closing project', 'Change'], $rows);

        $cleared = $this->option('only')
            ? TrackCurriculumActivity::where('track_id', $track->id)->whereNotIn('id', $ids)->where('requires_video', true)->pluck('title')
            : collect();
        foreach ($cleared as $title) {
            $this->line("  will no longer require a video: {$title}");
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing saved.');

            return self::SUCCESS;
        }

        $set = TrackCurriculumActivity::whereIn('id', $ids)->where('requires_video', false)->update(['requires_video' => true]);
        if ($this->option('only')) {
            TrackCurriculumActivity::where('track_id', $track->id)->whereNotIn('id', $ids)->update(['requires_video' => false]);
        }
        $this->info("{$set} project(s) now require a Show & Tell video" . ($cleared->isNotEmpty() ? "; {$cleared->count()} cleared" : '') . '.');

        return self::SUCCESS;
    }
}
