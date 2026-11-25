<?php

namespace App\Console\Commands;

use App\Models\DetectiveCase;
use App\Models\Track;
use App\Services\DetectiveService;
use Illuminate\Console\Command;

/**
 * Load Data Detective cases from a private JSON file. Number answers are stored
 * as hashes of their canonical form; choice answers as indexes in a hidden column.
 */
class ImportDetectiveCases extends Command
{
    protected $signature = 'detective:import {file}';

    protected $description = 'Import Data Detective cases for a track';

    public function handle(): int
    {
        $data = json_decode((string) file_get_contents($this->argument('file')), true);
        $track = Track::where('slug', $data['track'] ?? '')->first();
        if (!$track || !is_array($data['cases'] ?? null)) {
            $this->error('The file needs a valid "track" slug and a "cases" array.');

            return self::FAILURE;
        }

        foreach ($data['cases'] as $i => $c) {
            $public = [];
            $key = [];
            foreach ($c['questions'] as $q) {
                if (($q['kind'] ?? '') === 'number') {
                    $canonical = (string) $q['expected_canonical'];
                    // The server re-canonicalises typed numbers: make sure the stored answer survives that.
                    if (!is_numeric($canonical) || DetectiveService::canonicalNumber((float) $canonical) !== $canonical) {
                        $this->error("{$c['slug']} {$q['key']}: expected answer {$canonical} is not in canonical form.");

                        return self::FAILURE;
                    }
                    $key[$q['key']] = hash('sha256', $canonical);
                } elseif (($q['kind'] ?? '') === 'choice' && isset($q['options'][$q['answer_index'] ?? -1])) {
                    $key[$q['key']] = (int) $q['answer_index'];
                } else {
                    $this->error("{$c['slug']} {$q['key']}: invalid question.");

                    return self::FAILURE;
                }
                $public[] = array_intersect_key($q, array_flip(['key', 'prompt', 'kind', 'unit', 'options']));
            }

            DetectiveCase::updateOrCreate(['track_id' => $track->id, 'slug' => $c['slug']], [
                'title' => $c['title'], 'difficulty' => (int) ($c['difficulty'] ?? 1), 'xp' => (int) ($c['xp'] ?? 50), 'story' => $c['story'],
                'data_note' => $c['data_note'] ?? null, 'hints' => array_values($c['hints'] ?? []), 'questions' => $public, 'answer_key' => $key,
                'solution' => $c['solution'] ?? null, 'sort' => $i, 'is_active' => true,
            ]);
        }
        DetectiveCase::where('track_id', $track->id)->whereNotIn('slug', array_column($data['cases'], 'slug'))->update(['is_active' => false]);
        $this->info(count($data['cases']) . " cases imported for {$track->name}.");

        return self::SUCCESS;
    }
}
