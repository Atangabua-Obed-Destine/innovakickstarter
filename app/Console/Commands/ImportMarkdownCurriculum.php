<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Models\TrackMilestone;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\MarkdownCurriculumParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportMarkdownCurriculum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curriculum:import-markdown {folder} {--track=software-engineering}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports structured curriculum from a folder of markdown files into the database.';

    /**
     * Execute the console command.
     */
    public function handle(CurriculumService $curriculumService, MarkdownCurriculumParser $parser)
    {
        $folderPath = $this->argument('folder');
        $trackSlug = $this->option('track');

        if (!File::isDirectory($folderPath)) {
            $this->error("Directory does not exist: {$folderPath}");
            return 1;
        }

        $track = Track::where('slug', $trackSlug)->first();
        if (!$track) {
            $this->error("Track with slug '{$trackSlug}' not found.");
            return 1;
        }

        // Get admin user to run the creations
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        if (!$admin) {
            $admin = User::first(); // fallback
        }

        $files = collect(File::files($folderPath))
            ->filter(fn($f) => $f->getExtension() === 'md')
            ->sortBy(function ($f) {
                preg_match('/Milestone_(\d+)/i', $f->getFilename(), $m);
                return isset($m[1]) ? (int)$m[1] : 999;
            });

        if ($files->isEmpty()) {
            $this->warn("No markdown files found in {$folderPath}");
            return 0;
        }

        $this->info("Importing curriculum for track: {$track->title}");

        DB::beginTransaction();

        try {
            // Wipe existing milestones and activities for this track to start fresh
            DB::table('track_curriculum_activities')->where('track_id', $track->id)->delete();
            DB::table('track_milestones')->where('track_id', $track->id)->delete();

            $activityMap = []; // Maps '1.1' -> UUID
            $milestoneOrder = 1;
            $activitySequence = 1;

            foreach ($files as $file) {
                $this->info("Parsing: " . $file->getFilename());
                $content = File::get($file->getPathname());
                $parsed = $parser->parse($content);

                if (empty($parsed['title'])) {
                    $this->warn("Skipping file, no milestone title found.");
                    continue;
                }

                // Create Milestone
                $milestone = $curriculumService->createMilestone($track, [
                    'title' => $parsed['title'],
                    'description' => $parsed['theme'],
                    'sequence_order' => $milestoneOrder++,
                ], $admin);

                $this->line(" Created Milestone: {$milestone->title}");

                // Create Activities
                foreach ($parsed['activities'] as $actData) {
                    $this->line("   -> Activity {$actData['identifier']}: {$actData['title']}");

                    // Resolve Chain Parent
                    $chainParentId = null;
                    if (!empty($actData['chain_parent']) && isset($activityMap[$actData['chain_parent']])) {
                        $chainParentId = $activityMap[$actData['chain_parent']];
                    }

                    // Resolve Prerequisites
                    $prerequisiteIds = [];
                    foreach ($actData['prerequisites'] as $prereq) {
                        if (isset($activityMap[$prereq])) {
                            $prerequisiteIds[] = $activityMap[$prereq];
                        }
                    }

                    $activityPayload = [
                        'track_id' => $track->id,
                        'milestone_id' => $milestone->id,
                        'title' => $actData['title'],
                        'description' => $actData['description'],
                        'type' => $actData['type'],
                        'difficulty_level' => $actData['difficulty'],
                        'points' => $actData['points'],
                        'sequence_order' => $activitySequence++,
                        'deadline_days' => $actData['deadline_days'],
                        'grace_period_days' => $actData['grace_period_days'],
                        'late_penalty_percent' => $actData['late_penalty_percent'],
                        'is_required' => $actData['is_required'],
                        'chain_parent_id' => $chainParentId,
                        'prerequisites' => $prerequisiteIds,
                        'evidence_requirements' => $actData['evidence_requirements'],
                        'evaluation_rubric' => $actData['evaluation_rubric'],
                        'interview_config' => $actData['interview_config'],
                        'resources' => $actData['resources'] ?? [],
                        'is_active' => true,
                    ];

                    $activity = $curriculumService->createCurriculumActivity($activityPayload, $admin);

                    // Store mapping
                    $activityMap[$actData['identifier']] = $activity->id;
                }
            }

            DB::commit();
            $this->info("Successfully imported curriculum!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
