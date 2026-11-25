<?php

namespace App\Console\Commands;

use App\Models\AiReview;
use App\Services\AiReview\AiGrader;
use App\Services\AiReview\AiReviewService;
use App\Services\AiReview\EvidenceCollector;
use App\Services\AiReview\ReviewPolicy;
use Illuminate\Console\Command;

/**
 * Runs the calibration cases through the real model and decision rules,
 * and reports where the AI's recommendation differs from what a good
 * reviewer would accept. Nothing is saved.
 */
class CalibrateAiReview extends Command
{
    protected $signature = 'ai-review:calibrate
                            {--case= : Run a single case by id}
                            {--show : Print the AI feedback and reasoning for each case}';

    protected $description = 'Check the AI reviewer against calibration cases with known correct outcomes';

    public function handle(AiGrader $grader, AiReviewService $service, EvidenceCollector $collector, ReviewPolicy $policy): int
    {
        if (!$grader->isConfigured()) {
            $this->error('OPENAI_API_KEY is not set.');
            return self::FAILURE;
        }

        $cases = require app_path('Services/AiReview/Calibration/cases.php');
        if ($only = $this->option('case')) {
            $cases = array_values(array_filter($cases, fn ($c) => $c['id'] === $only));
        }

        $this->info('Model: ' . $grader->model() . ' · prompt ' . AiGrader::PROMPT_VERSION . ' · ' . count($cases) . ' case(s)');

        $rows = [];
        $passed = 0;
        $wrongApprovals = 0;
        $tokens = 0;

        foreach ($cases as $case) {
            $package = $this->package($case, $collector);
            $checks = $service->hardChecks($package);
            $integrity = ['similarity' => ['matches' => [], 'compared' => true], 'provenance' => [], 'ai_assistance' => $case['ai'] ?? null];

            try {
                $graded = $grader->grade($package, $integrity);
                $result = $graded['result'];
                $tokens += (int) $graded['input_tokens'] + (int) $graded['output_tokens'];
            } catch (\Throwable $e) {
                $result = null;
                $this->warn("{$case['id']}: " . $e->getMessage());
            }

            $score = $result ? $policy->weightedScore($package['activity']['rubric'], $result['criteria']) : 0.0;
            $decision = $policy->decide($package, $checks, $integrity, $result, $score);
            $ok = in_array($decision['decision'], $case['expect'], true);
            $passed += $ok ? 1 : 0;
            if ($decision['decision'] === AiReview::DECISION_APPROVE && !in_array(AiReview::DECISION_APPROVE, $case['expect'], true)) {
                $wrongApprovals++;
            }

            $rows[] = [
                $ok ? '<info>PASS</info>' : '<error>FAIL</error>',
                $case['id'],
                implode('/', $case['expect']),
                $decision['decision'],
                $result ? number_format($score, 0) : '—',
                $result['confidence'] ?? '—',
                implode(', ', $result['flags'] ?? []) ?: '—',
            ];

            if ($this->option('show') || !$ok) {
                $this->newLine();
                $this->line("<comment>{$case['id']}</comment> ({$case['note']})");
                foreach ($decision['reasons'] as $reason) {
                    $this->line("  · {$reason}");
                }
                foreach ($result['criteria'] ?? [] as $c) {
                    $this->line("  {$c['criterion']}: {$c['score']} · {$c['reasoning']}");
                }
                if ($result) {
                    $this->line('  Feedback: ' . $result['feedback_for_fellow']);
                }
            }
        }

        $this->newLine();
        $this->table(['', 'Case', 'Expected', 'AI', 'Score', 'Confidence', 'Flags'], $rows);
        $this->line("Passed {$passed} of " . count($cases) . " · wrong approvals: {$wrongApprovals} · tokens: " . number_format($tokens));

        return $wrongApprovals === 0 && $passed === count($cases) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Build the same evidence package the collector produces, from a case.
     */
    protected function package(array $case, EvidenceCollector $collector): array
    {
        $activity = $case['activity'];

        return [
            'activity' => [
                'title' => $activity['title'],
                'track' => $activity['track'],
                'milestone' => $activity['milestone'],
                'type' => $activity['type'],
                'difficulty' => $activity['difficulty'],
                'points' => $activity['points'],
                'description' => $activity['description'],
                'instructions' => '',
                'evidence_requirements' => $activity['evidence_requirements'],
                'rubric' => $collector->rubric($activity['rubric']),
            ],
            'submission' => [
                'attempt' => 1,
                'started_at' => now()->subDays(2)->toIso8601String(),
                'submitted_at' => now()->toIso8601String(),
                'minutes_from_start_to_submit' => 2880,
                'late' => false,
                'url' => $case['url'] ?? null,
                'text' => $case['text'] ?? null,
                'reflection' => $case['reflection'] ?? null,
                'file_names' => [],
                'ai_assistance' => $case['ai'] ?? null,
            ],
            'github' => $case['github'] ?? [],
            'links' => [],
            'files' => [],
            'unread' => [],
        ];
    }
}
