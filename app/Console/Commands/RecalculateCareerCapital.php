<?php

namespace App\Console\Commands;

use App\Enums\Tier;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Services\CareerCapitalCalculator;
use Illuminate\Console\Command;

/**
 * Recalculate Career Capital scores for every enrolled fellow.
 *
 * Run after changing the scoring formula or the scoring settings.
 * Use --dry-run first to see the new scores without saving them.
 */
class RecalculateCareerCapital extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'career-capital:recalculate
                            {--track= : Only this track (slug or id)}
                            {--dry-run : Show the new scores without saving}
                            {--no-notify : Do not send tier promotion notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate Career Capital scores and tiers for all enrolled fellows';

    public function handle(CareerCapitalCalculator $calculator): int
    {
        $query = FellowTrack::query()
            ->whereHas('fellow')
            ->whereHas('track')
            ->with(['fellow', 'track']);

        if ($trackOption = $this->option('track')) {
            $track = Track::where('slug', $trackOption)->orWhere('id', $trackOption)->first();
            if (!$track) {
                $this->error("Track '{$trackOption}' not found.");
                return self::FAILURE;
            }
            $query->where('track_id', $track->id);
        }

        $dryRun = (bool) $this->option('dry-run');
        $rows = [];
        $before = [];
        $after = [];
        $failed = 0;

        foreach ($query->cursor() as $fellowTrack) {
            $oldScore = (float) $fellowTrack->score;
            $oldTier = Tier::tryFrom((string) $fellowTrack->tier) ?? Tier::ROOKIE;

            try {
                if ($dryRun) {
                    $newScore = $calculator->calculateScore($fellowTrack->fellow, $fellowTrack->track);
                } else {
                    $newScore = (float) $calculator->updateScore(
                        $fellowTrack->fellow,
                        $fellowTrack->track,
                        notify: !$this->option('no-notify'),
                        markActivity: false,
                    )->score;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("{$fellowTrack->fellow->name} / {$fellowTrack->track->name}: {$e->getMessage()}");
                continue;
            }

            $newTier = Tier::fromScore($newScore);
            $before[$oldTier->label()] = ($before[$oldTier->label()] ?? 0) + 1;
            $after[$newTier->label()] = ($after[$newTier->label()] ?? 0) + 1;

            $rows[] = [
                $fellowTrack->fellow->name,
                $fellowTrack->track->name,
                number_format($oldScore, 2) . ' ' . $oldTier->label(),
                number_format($newScore, 2) . ' ' . $newTier->label(),
            ];
        }

        usort($rows, fn ($a, $b) => (float) $b[3] <=> (float) $a[3]);

        $this->table(['Fellow', 'Track', 'Before', $dryRun ? 'New (not saved)' : 'After'], $rows);

        $this->newLine();
        $this->line('Tiers before: ' . $this->summarise($before));
        $this->line('Tiers after:  ' . $this->summarise($after));

        if ($failed) {
            $this->warn("{$failed} record(s) could not be recalculated.");
        }

        $this->info($dryRun
            ? count($rows) . ' score(s) previewed. Nothing was saved; run without --dry-run to apply.'
            : count($rows) . ' score(s) recalculated.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    protected function summarise(array $counts): string
    {
        $parts = [];
        foreach (Tier::cases() as $tier) {
            $parts[] = $tier->label() . ' ' . ($counts[$tier->label()] ?? 0);
        }

        return implode(' · ', $parts);
    }
}
