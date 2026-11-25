<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;

/**
 * Load a track's orientation story (welcome, day in the life, myths, career
 * ladder, what seniors master) into tracks.experience.orientation.
 * Default file: database/data/orientation/{slug}.json
 */
class ImportOrientation extends Command
{
    protected $signature = 'orientation:import {slug} {file?}';

    protected $description = "Import a track's orientation content";

    public function handle(): int
    {
        $track = Track::where('slug', $this->argument('slug'))->first();
        if (!$track) {
            $this->error('No track with that slug.');

            return self::FAILURE;
        }
        $file = $this->argument('file') ?? database_path("data/orientation/{$track->slug}.json");
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
        if (!is_array($data)) {
            $this->error("Could not read {$file} as JSON.");

            return self::FAILURE;
        }

        $problems = [];
        foreach ($data['day'] ?? [] as $i => $d) {
            if (empty($d['time']) || empty($d['title']) || empty($d['story'])) {
                $problems[] = "day[{$i}] needs time, title and story";
            }
        }
        foreach ($data['myths'] ?? [] as $i => $m) {
            if (empty($m['claim']) || !is_bool($m['truth'] ?? null) || empty($m['explain'])) {
                $problems[] = "myths[{$i}] needs claim, truth (true/false) and explain";
            }
        }
        foreach ($data['ladder'] ?? [] as $i => $l) {
            if (empty($l['title']) || empty($l['does'])) {
                $problems[] = "ladder[{$i}] needs title and does";
            }
        }
        $skillKeys = array_column($track->experience['skills'] ?? [], 'key');
        foreach (array_keys($data['senior'] ?? []) as $key) {
            if ($skillKeys && !in_array($key, $skillKeys, true)) {
                $problems[] = "senior.{$key} is not one of the track's skills";
            }
        }
        if ($problems) {
            $this->error(implode("\n", $problems));

            return self::FAILURE;
        }

        $experience = is_array($track->experience) ? $track->experience : [];
        $experience['orientation'] = $data;
        $track->update(['experience' => $experience]);
        $this->info("Orientation loaded for {$track->name}: " . count($data['day'] ?? []) . ' moments, ' . count($data['myths'] ?? []) . ' myths, ' . count($data['ladder'] ?? []) . ' ladder levels.');

        return self::SUCCESS;
    }
}
