<?php

namespace App\Console\Commands;

use App\Models\FlashCard;
use App\Models\Track;
use Illuminate\Console\Command;

/** Load flash review cards for a track from a private JSON file. Cards missing from the file are deactivated. */
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
        foreach ($data['cards'] as $c) {
            if (empty($c['key']) || empty($c['question']) || count($c['options'] ?? []) < 2 || !isset($c['options'][$c['answer_index'] ?? -1])) {
                $this->error('Invalid card: ' . ($c['key'] ?? json_encode($c)));

                return self::FAILURE;
            }
        }
        foreach ($data['cards'] as $c) {
            FlashCard::updateOrCreate(['track_id' => $track->id, 'key' => $c['key']], [
                'chapter' => (int) $c['chapter'], 'skill' => $c['skill'] ?? null, 'question' => $c['question'], 'options' => array_values($c['options']),
                'answer_index' => (int) $c['answer_index'], 'explanation' => $c['explanation'] ?? null, 'is_active' => true,
            ]);
        }
        FlashCard::where('track_id', $track->id)->whereNotIn('key', array_column($data['cards'], 'key'))->update(['is_active' => false]);
        $this->info(count($data['cards']) . " cards imported for {$track->name}.");

        return self::SUCCESS;
    }
}
