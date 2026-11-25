<?php

namespace App\Console\Commands;

use App\Models\FlashCard;
use App\Models\Track;
use Illuminate\Console\Command;

/**
 * Load flash review cards for a track from a private JSON file. A file holds
 * either daily-review cards or lesson quick checks (cards with an "activity" id
 * and optional "after_step"); cards of the same kind missing from the file are
 * deactivated.
 */
class ImportFlashCards extends Command
{
    protected $signature = 'flashcards:import {file}';

    protected $description = 'Import flash review cards for a track';

    public function handle(): int
    {
        $data = json_decode((string) file_get_contents($this->argument('file')), true);
        $track = Track::where('slug', $data['track'] ?? '')->first();
        if (!$track || !is_array($data['cards'] ?? null)) {
            $this->error('The file needs a valid "track" slug and a "cards" array.');

            return self::FAILURE;
        }
        $lessonCards = count(array_filter($data['cards'], fn ($c) => !empty($c['activity'])));
        if ($lessonCards && $lessonCards !== count($data['cards'])) {
            $this->error('A file holds either daily cards or lesson quick checks (all with "activity"), not both.');

            return self::FAILURE;
        }
        $activityIds = \App\Models\TrackCurriculumActivity::where('track_id', $track->id)->pluck('id')->flip();
        foreach ($data['cards'] as $c) {
            if (!empty($c['activity']) && !$activityIds->has($c['activity'])) {
                $this->error("Card {$c['key']}: activity {$c['activity']} is not in this track.");

                return self::FAILURE;
            }
            if (empty($c['key']) || empty($c['question']) || count($c['options'] ?? []) < 2 || !isset($c['options'][$c['answer_index'] ?? -1])) {
                $this->error('Invalid card: ' . ($c['key'] ?? json_encode($c)));

                return self::FAILURE;
            }
        }
        foreach ($data['cards'] as $c) {
            FlashCard::updateOrCreate(['track_id' => $track->id, 'key' => $c['key']], [
                'chapter' => (int) $c['chapter'], 'skill' => $c['skill'] ?? null, 'question' => $c['question'], 'options' => array_values($c['options']),
                'answer_index' => (int) $c['answer_index'], 'explanation' => $c['explanation'] ?? null, 'is_active' => true,
                'curriculum_activity_id' => $c['activity'] ?? null, 'after_step' => isset($c['after_step']) ? (int) $c['after_step'] : null,
            ]);
        }
        FlashCard::where('track_id', $track->id)->{$lessonCards ? 'whereNotNull' : 'whereNull'}('curriculum_activity_id')
            ->whereNotIn('key', array_column($data['cards'], 'key'))->update(['is_active' => false]);
        $this->info(count($data['cards']) . ' ' . ($lessonCards ? 'quick checks' : 'cards') . " imported for {$track->name}.");

        return self::SUCCESS;
    }
}
