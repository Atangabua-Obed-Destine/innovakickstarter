<?php

namespace App\Console\Commands;

use App\Enums\CurriculumStatus;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\Presentation;
use App\Models\TrackFeedEvent;
use App\Models\TrackMilestone;
use App\Services\TrackFeedService;
use Illuminate\Console\Command;

/** Fill the track community feed with recent achievements, keeping their original dates. */
class BackfillTrackFeed extends Command
{
    protected $signature = 'feed:backfill {--days=60 : How far back to look}';

    protected $description = 'Add recent achievements to the track community feeds';

    public function handle(TrackFeedService $feed): int
    {
        $since = now()->subDays((int) $this->option('days'));
        $before = TrackFeedEvent::count();
        $stamp = function (?TrackFeedEvent $event, $at) {
            // Only events this run just created (a re-run keeps earlier dates).
            if ($event && $at && $event->created_at->gt(now()->subMinutes(10))) {
                $event->timestamps = false;
                $event->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
            }
        };

        FellowBadge::with('fellow')->where('badge_type', 'milestone')->where('earned_at', '>=', $since)->whereNotNull('milestone_id')
            ->get()->each(function ($badge) use ($feed, $stamp) {
                $milestone = TrackMilestone::find($badge->milestone_id);
                if ($milestone && $badge->fellow) {
                    $feed->chapterCompleted($badge->fellow, $milestone);
                    $stamp(TrackFeedEvent::where('fellow_id', $badge->fellow_id)->where('subject_key', "milestone:{$milestone->id}")->first(), $badge->earned_at);
                }
            });

        FellowCurriculumProgress::with(['curriculumActivity', 'fellow'])->where('status', CurriculumStatus::COMPLETED->value)
            ->where('completed_at', '>=', $since)
            ->whereHas('curriculumActivity', fn ($q) => $q->whereIn('type', ['project', 'case_study', 'blog_post']))
            ->get()->each(function ($progress) use ($feed, $stamp) {
                $feed->workApproved($progress);
                $stamp(TrackFeedEvent::where('fellow_id', $progress->fellow_id)->where('subject_key', "activity:{$progress->curriculum_activity_id}")->first(), $progress->completed_at);
            });

        Presentation::with('fellow')->where('status', Presentation::STATUS_APPROVED)->where('reviewed_at', '>=', $since)
            ->get()->each(function ($presentation) use ($feed, $stamp) {
                $feed->presentationApproved($presentation);
                $stamp(TrackFeedEvent::where('fellow_id', $presentation->fellow_id)->where('subject_key', "presentation:{$presentation->id}")->first(), $presentation->reviewed_at);
            });

        $this->info('Added ' . (TrackFeedEvent::count() - $before) . ' feed events.');

        return self::SUCCESS;
    }
}
