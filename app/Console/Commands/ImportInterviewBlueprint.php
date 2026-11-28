<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Services\Interview\BlueprintService;
use Illuminate\Console\Command;

/**
 * Load a track's mock-interview blueprint (rounds, rubric, chapter loops, question bank)
 * from a private JSON file. The file stays private; only the server reads the bank.
 */
class ImportInterviewBlueprint extends Command
{
    protected $signature = 'interviews:import {file} {--dry-run}';

    protected $description = 'Validate and import a track mock-interview blueprint from a JSON file';

    public function handle(BlueprintService $blueprints): int
    {
        $path = (string) $this->argument('file');
        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            $this->error('The file is not valid JSON: ' . json_last_error_msg());

            return self::FAILURE;
        }

        $errors = $blueprints->validate($data);
        $track = is_string($data['track'] ?? null) ? Track::where('slug', $data['track'])->first() : null;
        if (!$track) {
            $errors[] = 'No track with slug "' . ($data['track'] ?? '') . '".';
        }
        if ($errors) {
            foreach (array_slice($errors, 0, 60) as $error) {
                $this->error($error);
            }
            if (count($errors) > 60) {
                $this->error('... and ' . (count($errors) - 60) . ' more.');
            }

            return self::FAILURE;
        }

        foreach ($blueprints->unknownMilestones($track, $data) as $title) {
            $this->warn("Chapter \"{$title}\" is not in the {$track->name} curriculum yet (it will apply once it is).");
        }

        $this->info(sprintf('%s v%d: %d rounds, %d chapters, %d bank questions.',
            $track->name, $data['version'], count($data['rounds']), count($data['chapters']), count($data['bank'])));

        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing saved.');

            return self::SUCCESS;
        }

        $blueprints->store($track, $data);
        $this->info('Imported.');

        return self::SUCCESS;
    }
}
