<?php

namespace App\Console\Commands;

use App\Models\PracticeChallenge;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Load Practice Lab challenges from a private JSON file. Expected answers are
 * stored only as SHA-256 hashes of their canonical form; solutions stay in the
 * file. Challenges missing from the file are deactivated (attempts are kept).
 */
class ImportPracticeChallenges extends Command
{
    protected $signature = 'practice:import {file} {--dry-run}';

    protected $description = 'Import Practice Lab challenges for a track from a JSON file';

    public function handle(): int
    {
        $data = json_decode((string) file_get_contents($this->argument('file')), true);
        $track = Track::where('slug', $data['track'] ?? '')->first();
        if (!$track || !is_array($data['challenges'] ?? null)) {
            $this->error('The file needs a valid "track" slug and a "challenges" array.');

            return self::FAILURE;
        }

        $errors = [];
        $slugs = [];
        foreach ($data['challenges'] as $i => $c) {
            $label = $c['slug'] ?? "#{$i}";
            if (empty($c['slug']) || empty($c['title']) || empty($c['prompt'])) {
                $errors[] = "{$label}: slug, title and prompt are required.";
            }
            if (in_array($c['slug'] ?? null, $slugs, true)) {
                $errors[] = "{$label}: duplicate slug.";
            }
            $slugs[] = $c['slug'] ?? null;
            if (!in_array($c['kind'] ?? '', ['sql', 'python', 'quiz'], true)) {
                $errors[] = "{$label}: kind must be sql, python or quiz.";
            } elseif ($c['kind'] === 'quiz') {
                if (count($c['options'] ?? []) < 2 || !isset($c['options'][$c['answer_index'] ?? -1])) {
                    $errors[] = "{$label}: a quiz needs options and a valid answer_index.";
                }
            } elseif (!is_string($c['expected_canonical'] ?? null) || $c['expected_canonical'] === '') {
                $errors[] = "{$label}: expected_canonical is missing.";
            }
        }
        if ($errors) {
            array_map(fn ($e) => $this->error($e), $errors);

            return self::FAILURE;
        }

        $this->info(count($data['challenges']) . " challenges for {$track->name}.");
        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing saved.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($data, $track, $slugs) {
            foreach (array_values($data['challenges']) as $i => $c) {
                PracticeChallenge::updateOrCreate(
                    ['track_id' => $track->id, 'slug' => $c['slug']],
                    [
                        'title' => $c['title'],
                        'skill' => $c['skill'] ?? null,
                        'kind' => $c['kind'],
                        'difficulty' => max(1, min(3, (int) ($c['difficulty'] ?? 1))),
                        'xp' => (int) ($c['xp'] ?? 10),
                        'prompt' => $c['prompt'],
                        'starter_code' => $c['starter_code'] ?? null,
                        'setup' => $c['setup'] ?? null,
                        'hint' => $c['hint'] ?? null,
                        'explanation' => $c['explanation'] ?? null,
                        'ordered' => (bool) ($c['ordered'] ?? false),
                        'needs_pandas' => (bool) ($c['needs_pandas'] ?? false),
                        'options' => $c['kind'] === 'quiz' ? array_values($c['options']) : null,
                        'answer_index' => $c['kind'] === 'quiz' ? (int) $c['answer_index'] : null,
                        'expected_hash' => $c['kind'] === 'quiz' ? null : hash('sha256', $c['expected_canonical']),
                        'sort' => $i,
                        'is_active' => true,
                    ]
                );
            }
            PracticeChallenge::where('track_id', $track->id)->whereNotIn('slug', $slugs)->update(['is_active' => false]);
        });

        $this->info('Imported.');

        return self::SUCCESS;
    }
}
