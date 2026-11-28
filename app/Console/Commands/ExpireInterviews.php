<?php

namespace App\Console\Commands;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Models\InterviewSession;
use App\Services\Interview\InterviewEngine;
use Illuminate\Console\Command;

/**
 * Close AI interviews that were left open: 30 minutes without activity, or 2 hours after
 * the start. They are cancelled as "abandoned" and never scored or counted.
 *
 * --stuck also closes sessions from the old interview room that never finished
 * (in progress for more than 2 hours). Use --dry-run first.
 */
class ExpireInterviews extends Command
{
    protected $signature = 'interviews:expire {--stuck : Also close old-room AI sessions left in progress for over 2 hours} {--dry-run : List what would be closed, change nothing}';

    protected $description = 'Cancel abandoned AI mock interviews (never scored, never counted)';

    public function handle(InterviewEngine $engine): int
    {
        $dry = (bool) $this->option('dry-run');
        $closed = 0;

        InterviewSession::where('status', InterviewStatus::IN_PROGRESS->value)->whereNotNull('engine_version')
            ->where(fn ($q) => $q->where('expires_at', '<', now())->orWhere('last_activity_at', '<', now()->subMinutes(InterviewEngine::IDLE_MINUTES)))
            ->orderBy('created_at')
            ->each(function (InterviewSession $s) use ($engine, $dry, &$closed) {
                if (!$engine->isExpired($s)) {
                    return;
                }
                $this->line(($dry ? '[dry run] ' : '') . "Abandoned: {$s->id} (fellow {$s->fellow_id}, started " . $s->started_at?->toDateTimeString() . ')');
                if (!$dry) {
                    $engine->expire($s);
                }
                $closed++;
            });

        if ($this->option('stuck')) {
            InterviewSession::where('status', InterviewStatus::IN_PROGRESS->value)->whereNull('engine_version')
                ->where('mode', InterviewMode::AI->value)
                ->where(fn ($q) => $q->where('started_at', '<', now()->subMinutes(InterviewEngine::MAX_MINUTES))
                    ->orWhere(fn ($q) => $q->whereNull('started_at')->where('created_at', '<', now()->subMinutes(InterviewEngine::MAX_MINUTES))))
                ->orderBy('created_at')
                ->each(function (InterviewSession $s) use ($dry, &$closed) {
                    $this->line(($dry ? '[dry run] ' : '') . "Stuck (old room): {$s->id} (fellow {$s->fellow_id}, created " . $s->created_at?->toDateTimeString() . ')');
                    if (!$dry) {
                        $s->forceFill(['status' => InterviewStatus::CANCELLED, 'cancellation_reason' => 'abandoned', 'score' => null, 'overall_score' => null])->save();
                    }
                    $closed++;
                });
        }

        $this->info(($dry ? 'Would close ' : 'Closed ') . $closed . ' interview(s).');

        return self::SUCCESS;
    }
}
