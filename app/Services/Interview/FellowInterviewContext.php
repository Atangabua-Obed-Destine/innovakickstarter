<?php

namespace App\Services\Interview;

use App\Enums\CurriculumStatus;
use App\Enums\InterviewStatus;
use App\Models\AiReview;
use App\Models\FellowCurriculumProgress;
use App\Models\FlashAnswer;
use App\Models\FlashCard;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\AiReview\GitHubInspector;
use App\Services\PathwayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * What an interview is based on: the track and chapter, the activity's interview settings,
 * the blueprint's chapter loop, where the fellow is (earlier interview weaknesses, missed
 * quick checks, reviewer feedback) and the fellow's own project in this chapter.
 * A compact snapshot is stored on the session (`context`). Never fails on missing data.
 */
class FellowInterviewContext
{
    public function __construct(
        protected BlueprintService $blueprints,
        protected PathwayService $pathways,
        protected GitHubInspector $github,
    ) {}

    public function build(User $fellow, Track $track, ?TrackMilestone $milestone, ?TrackCurriculumActivity $activity, bool $practice): array
    {
        $bp = $this->blueprints->forTrack($track);
        $pathway = $this->safe(fn () => $this->pathways->major($fellow, $track));
        $chapterNumber = $milestone ? (int) $this->safe(fn () => $this->pathways->hasPathways($track)
            ? $this->pathways->chapterNumber($milestone, null, $pathway)
            : max(1, TrackMilestone::where('track_id', $track->id)->where('is_active', true)->where('sequence_order', '<=', $milestone->sequence_order)->count()), 1) : null;
        $chapter = $this->blueprints->chapter($bp, $milestone, $activity);
        $config = (array) ($activity?->interview_config ?? []);

        $passMark = isset($config['min_score']) && is_numeric($config['min_score'])
            ? (int) $config['min_score']
            : (int) ($chapter['pass_mark'] ?? 70);

        return [
            'track' => ['id' => $track->id, 'name' => $track->name, 'slug' => $track->slug],
            'blueprint' => ['version' => (int) ($bp['version'] ?? 0), 'generic' => $this->blueprints->isGeneric($bp)],
            'role' => $bp['role'] ?? [],
            'interviewer' => $bp['interviewer'] ?? [],
            'milestone' => $milestone ? ['id' => $milestone->id, 'title' => $milestone->title, 'chapter' => $chapterNumber] : null,
            'pathway' => $pathway,
            'activity' => $activity ? ['id' => $activity->id, 'title' => $activity->title] : null,
            'practice' => $practice,
            'config' => array_intersect_key($config, array_flip(['type', 'mode', 'difficulty', 'min_score', 'count', 'topics'])),
            'chapter' => $chapter,
            'pass_mark' => $passMark,
            'level' => $this->level($fellow, $track, $milestone, $chapterNumber),
            'project' => $milestone ? $this->project($fellow, $milestone) : null,
            'seen_questions' => $this->seenQuestions($fellow, $track),
        ];
    }

    /**
     * Where the fellow stands: weaknesses from earlier interviews in this track,
     * quick checks they missed in this chapter, and reviewer feedback in this chapter.
     */
    protected function level(User $fellow, Track $track, ?TrackMilestone $milestone, ?int $chapterNumber): array
    {
        $previous = InterviewSession::where('fellow_id', $fellow->id)->where('track_id', $track->id)
            ->where('status', InterviewStatus::COMPLETED->value)->whereNotNull('score')
            ->orderByDesc('completed_at')->limit(5)->get(['id', 'score', 'scorecard', 'rubric_scores', 'title', 'completed_at']);

        $weak = [];
        foreach ($previous as $s) {
            foreach ((array) ($s->scorecard['dimensions'] ?? []) as $d) {
                if ((int) ($d['level'] ?? 4) <= 2 && !empty($d['label'])) {
                    $weak[$d['label']] = ($weak[$d['label']] ?? 0) + 1;
                }
            }
            foreach ((array) ($s->scorecard['improvements'] ?? []) as $imp) {
                if (is_string($imp) && count($weak) < 8) {
                    $weak[mb_substr($imp, 0, 160)] = ($weak[mb_substr($imp, 0, 160)] ?? 0) + 1;
                }
            }
        }
        arsort($weak);

        $missed = [];
        if ($chapterNumber) {
            $missed = $this->safe(fn () => FlashAnswer::where('flash_answers.fellow_id', $fellow->id)->where('flash_answers.correct', false)
                ->join('flash_cards', 'flash_cards.id', '=', 'flash_answers.card_id')
                ->where('flash_cards.track_id', $track->id)->where('flash_cards.chapter', $chapterNumber)
                ->orderByDesc('flash_answers.id')->limit(20)->get(['flash_cards.question', 'flash_cards.skill'])
                ->unique('question')->take(5)->map(fn ($r) => ['question' => mb_substr((string) $r->question, 0, 200), 'skill' => $r->skill])->values()->all(), []);
        }

        $feedback = [];
        if ($milestone) {
            $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                ->whereIn('curriculum_activity_id', TrackCurriculumActivity::where('milestone_id', $milestone->id)->pluck('id'))
                ->whereNotNull('review_notes')->orderByDesc('reviewed_at')->limit(3)->with('curriculumActivity:id,title')->get();
            foreach ($progress as $p) {
                $feedback[] = ['from' => 'reviewer', 'activity' => $p->curriculumActivity?->title, 'text' => mb_substr((string) $p->review_notes, 0, 400)];
            }
            $ai = $this->safe(fn () => AiReview::where('fellow_id', $fellow->id)
                ->whereIn('curriculum_activity_id', TrackCurriculumActivity::where('milestone_id', $milestone->id)->pluck('id'))
                ->whereNotNull('feedback_for_fellow')->latest()->limit(3)->get(['feedback_for_fellow']), collect());
            foreach ($ai as $r) {
                $feedback[] = ['from' => 'ai_review', 'text' => mb_substr((string) $r->feedback_for_fellow, 0, 400)];
            }
        }

        $scores = $previous->pluck('score')->map(fn ($s) => (float) $s);

        return [
            'previous_interviews' => $previous->count(),
            'average_score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
            'weak_spots' => array_slice(array_keys($weak), 0, 5),
            'missed_quick_checks' => $missed,
            'feedback' => $feedback,
        ];
    }

    /**
     * The fellow's own submitted project (or case study) in this chapter: what they wrote,
     * links, and a short GitHub summary. Best effort: never fails the interview.
     */
    protected function project(User $fellow, TrackMilestone $milestone): ?array
    {
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('status', [CurriculumStatus::SUBMITTED->value, CurriculumStatus::PEER_REVIEW->value, CurriculumStatus::UNDER_REVIEW->value, CurriculumStatus::COMPLETED->value, CurriculumStatus::REJECTED->value])
            ->whereHas('curriculumActivity', fn ($q) => $q->where('milestone_id', $milestone->id)->whereIn('type', ['project', 'case_study', 'portfolio']))
            ->with('curriculumActivity:id,title,type,requires_video,points')
            ->get()
            // The chapter's main project (the Show & Tell one, then the biggest) says most about the fellow.
            ->sortByDesc(fn ($p) => [(int) (bool) $p->curriculumActivity?->requires_video, $p->curriculumActivity?->type?->value === 'project' ? 1 : 0, (int) $p->curriculumActivity?->points, (string) $p->submitted_at])
            ->first();
        if (!$progress) {
            return null;
        }
        $evidence = (array) ($progress->evidence ?? []);
        $text = trim((string) ($evidence['evidence_text'] ?? ''));
        $url = (string) ($evidence['evidence_url'] ?? '');

        $project = [
            'activity' => $progress->curriculumActivity?->title,
            'type' => $progress->curriculumActivity?->type?->value,
            'status' => $progress->status?->value,
            'text' => mb_substr($text, 0, 1500),
            'reflection' => mb_substr((string) $progress->submission_notes, 0, 600),
            'url' => $url ?: null,
            'github' => null,
        ];

        $links = array_values(array_unique(array_merge($url ? GitHubInspector::findLinks($url) : [], GitHubInspector::findLinks($text))));
        if ($links) {
            $project['github'] = $this->githubSummary($progress, $links[0], $fellow);
        }

        return $project;
    }

    protected function githubSummary(FellowCurriculumProgress $progress, string $link, User $fellow): ?array
    {
        // The AI review already read the repository for this submission: reuse it.
        $stored = AiReview::where('progress_id', $progress->id)->whereNotNull('evidence')->latest()->value('evidence');
        $repo = is_array($stored) ? collect($stored['github'] ?? [])->firstWhere('ok', true) : null;

        if (!$repo) {
            $repo = $this->safe(fn () => Cache::remember('interview-github:' . md5($link), now()->addHours(6), function () use ($link, $progress, $fellow) {
                $r = $this->github->inspect($link, $progress->started_at, GitHubInspector::usernameFromProfile($fellow->github_url));

                return ($r['ok'] ?? false) ? $r : null;
            }));
        }
        if (!is_array($repo)) {
            return null;
        }

        $readme = collect($repo['files'] ?? [])->first(fn ($f) => preg_match('/(^|\/)readme(\.md)?$/i', (string) ($f['path'] ?? '')));

        return [
            'repo' => $repo['repo'] ?? $repo['url'] ?? $link,
            'description' => $repo['description'] ?? null,
            'language' => $repo['language'] ?? null,
            'files' => array_slice((array) ($repo['file_list'] ?? array_column((array) ($repo['files'] ?? []), 'path')), 0, 40),
            'readme' => $readme ? mb_substr((string) ($readme['content'] ?? $readme['excerpt'] ?? ''), 0, 800) : null,
            'commits' => $repo['history']['commits_total'] ?? null,
        ];
    }

    /** Bank ids and question texts this fellow has already been asked in this track. */
    public function seenQuestions(User $fellow, Track $track): array
    {
        $ids = [];
        $texts = [];
        InterviewSession::where('fellow_id', $fellow->id)->where('track_id', $track->id)->whereNotNull('plan')
            ->orderByDesc('created_at')->limit(30)->get(['id', 'plan'])
            ->each(function ($s) use (&$ids, &$texts) {
                foreach ((array) ($s->plan['items'] ?? []) as $item) {
                    if (!empty($item['bank_id'])) {
                        $ids[] = $item['bank_id'];
                    }
                    if (!empty($item['question'])) {
                        $texts[] = mb_substr((string) $item['question'], 0, 160);
                    }
                }
            });

        return ['bank_ids' => array_values(array_unique($ids)), 'questions' => array_slice(array_values(array_unique($texts)), 0, 40)];
    }

    protected function safe(callable $fn, mixed $default = null): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::info('Interview context: part skipped', ['error' => mb_substr($e->getMessage(), 0, 200)]);

            return $default;
        }
    }
}
