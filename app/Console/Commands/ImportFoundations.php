<?php

namespace App\Console\Commands;

use App\Models\FoundationArea;
use Illuminate\Console\Command;

/** Load Base Camp areas (check, lessons, tests) from the private JSON file. */
class ImportFoundations extends Command
{
    protected $signature = 'foundations:import {file}';

    protected $description = 'Import Base Camp (Foundations) content';

    public function handle(): int
    {
        $data = json_decode((string) file_get_contents($this->argument('file')), true);
        $areas = $data['areas'] ?? null;
        if (!is_array($areas) || !$areas) {
            $this->error('The file needs an "areas" array.');

            return self::FAILURE;
        }
        $valid = fn ($q) => !empty($q['key']) && !empty($q['question']) && count($q['options'] ?? []) === 4 && isset($q['options'][$q['answer'] ?? -1]);
        foreach ($areas as $a) {
            $questions = array_merge($a['check']['questions'] ?? [], $a['test']['questions'] ?? [], ...array_map(fn ($l) => $l['practice'] ?? [], $a['lessons'] ?? []));
            if (empty($a['key']) || empty($a['title']) || !$questions || !collect($questions)->every($valid) || empty($a['lessons'])) {
                $this->error('Invalid area: ' . ($a['key'] ?? '?'));

                return self::FAILURE;
            }
        }
        foreach (array_values($areas) as $i => $a) {
            FoundationArea::updateOrCreate(['key' => $a['key']], [
                'title' => $a['title'], 'icon' => $a['icon'] ?? null, 'summary' => $a['summary'] ?? null, 'why' => $a['why'] ?? null,
                'content' => ['check' => $a['check'], 'lessons' => $a['lessons'], 'test' => $a['test']], 'sort' => $i + 1, 'is_active' => true,
            ]);
        }
        FoundationArea::whereNotIn('key', array_column($areas, 'key'))->update(['is_active' => false]);
        $this->info(count($areas) . ' Base Camp areas imported.');

        return self::SUCCESS;
    }
}
