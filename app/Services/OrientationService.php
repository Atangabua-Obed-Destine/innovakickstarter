<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Enums\Tier;
use App\Models\DetectiveCase;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FlashCard;
use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Support\TrackExperience;
use Illuminate\Support\Collection;

/**
 * Track orientation ("Chapter 0"): what the job is, the career ladder, what
 * seniors master and where the curriculum trains it, how the program works,
 * a study plan, a rules quiz and the fellow's commitment.
 *
 * Track-specific content lives in tracks.experience.orientation (welcome, day,
 * myths, ladder, senior); everything else is computed from the track's real
 * curriculum and settings, so every track gets a working orientation.
 *
 * Required before Chapter 1 for fellows who haven't started any work in the
 * track; recommended (not blocking) for fellows already under way.
 */
class OrientationService
{
    public const SECTIONS = ['welcome', 'day', 'myths', 'ladder', 'skills', 'toolkit', 'plan', 'quiz', 'commit'];

    /** Rough hours per activity type, for the study planner. */
    public const HOURS_PER_TYPE = [
        'research' => 2, 'project' => 6, 'case_study' => 4, 'blog_post' => 3, 'mock_interview' => 1.5,
        'debug_challenge' => 2, 'reflection' => 1, 'presentation' => 3,
    ];

    public function record(User $fellow, Track $track): TrackOrientation
    {
        return TrackOrientation::firstOrNew(['fellow_id' => $fellow->id, 'track_id' => $track->id]);
    }

    public function isComplete(User $fellow, Track $track): bool
    {
        return TrackOrientation::where('fellow_id', $fellow->id)->where('track_id', $track->id)->whereNotNull('completed_at')->exists();
    }

    /** True when the fellow must finish the orientation before starting work: new to the track and not oriented yet. */
    public function isRequired(User $fellow, Track|string $track): bool
    {
        $trackId = $track instanceof Track ? $track->id : $track;
        if (TrackOrientation::where('fellow_id', $fellow->id)->where('track_id', $trackId)->whereNotNull('completed_at')->exists()) {
            return false;
        }

        $begun = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('curriculum_activity_id', TrackCurriculumActivity::where('track_id', $trackId)->select('id'))
            ->whereNotIn('status', [CurriculumStatus::LOCKED->value, CurriculumStatus::AVAILABLE->value])
            ->exists();

        return !$begun;
    }

    /** The sections this track's orientation has (story sections need track content). */
    public function sections(Track $track): array
    {
        $o = $this->content($track);

        return array_values(array_filter(self::SECTIONS, fn ($s) => match ($s) {
            'day' => !empty($o['day']),
            'myths' => !empty($o['myths']),
            'ladder' => !empty($o['ladder']),
            'skills' => !empty(TrackExperience::for($track)?->skills()),
            default => true,
        }));
    }

    public function content(Track $track): array
    {
        return is_array($track->experience['orientation'] ?? null) ? $track->experience['orientation'] : [];
    }

    public function markSection(TrackOrientation $record, string $section): void
    {
        $done = $record->sections_done ?? [];
        if (in_array($section, self::SECTIONS, true) && !in_array($section, $done, true)) {
            $done[] = $section;
            $record->sections_done = $done;
            $record->save();
        }
    }

    // ------------------------------------------------------------------ skills

    /**
     * Each track skill with what a senior can do and where the curriculum trains it.
     *
     * @return array<int, array{key: string, label: string, description: string, senior: ?string, activities: array, chapters: array}>
     */
    public function skillPaths(Track $track): array
    {
        $experience = TrackExperience::for($track);
        $senior = $this->content($track)['senior'] ?? [];
        $milestones = TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')->get(['id', 'sequence_order', 'title', 'story']);
        $chapterNo = $milestones->values()->mapWithKeys(fn ($m, $i) => [$m->id => $i + 1]);
        $activities = TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')
            ->get(['id', 'title', 'skills', 'milestone_id', 'type', 'is_required']);

        return collect($experience?->skills() ?? [])->map(function ($skill) use ($activities, $chapterNo, $senior) {
            $tagged = $activities->filter(fn ($a) => in_array($skill['key'], (array) $a->skills, true) && $chapterNo->has($a->milestone_id))
                ->sortBy(fn ($a) => $chapterNo[$a->milestone_id]);

            return [
                'key' => $skill['key'], 'label' => $skill['label'], 'description' => $skill['description'] ?? '',
                'senior' => $senior[$skill['key']] ?? null,
                'activities' => $tagged->map(fn ($a) => ['title' => $a->title, 'chapter' => $chapterNo[$a->milestone_id], 'type' => $a->type?->label() ?? ''])->values()->all(),
                'chapters' => $tagged->map(fn ($a) => $chapterNo[$a->milestone_id])->unique()->sort()->values()->all(),
            ];
        })->all();
    }

    // ------------------------------------------------------------------ plan

    /** Estimated hours and pace from the real curriculum: chapters, their planned days, and required activities. */
    public function plan(Track $track): array
    {
        $milestones = TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')
            ->with(['curriculumActivities' => fn ($q) => $q->where('is_active', true)->where('is_required', true)])->get();

        $chapters = $milestones->values()->map(function ($m, $i) {
            $hours = $m->curriculumActivities->sum(fn ($a) => self::HOURS_PER_TYPE[$a->type?->value ?? ''] ?? 2);

            return ['number' => $i + 1, 'title' => $m->title, 'days' => (int) ($m->estimated_duration_days ?: 14), 'hours' => round($hours), 'activities' => $m->curriculumActivities->count()];
        });

        $totalHours = (int) round($chapters->sum('hours'));
        $weeks = max(1, (int) round($chapters->sum('days') / 7));

        return [
            'chapters' => $chapters->all(),
            'total_hours' => $totalHours,
            'weeks' => $weeks,
            'pace' => (int) max(1, ceil($totalHours / $weeks)),
        ];
    }

    // ------------------------------------------------------------------ toolkit

    /** How the program works, limited to what this track actually has. */
    public function toolkit(Track $track): array
    {
        $activities = TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->get(['type', 'deadline_days', 'grace_period_days', 'late_penalty_percent']);
        $deadline = (int) ($activities->pluck('deadline_days')->filter()->mode()[0] ?? 3);
        $grace = (int) ($activities->pluck('grace_period_days')->filter()->mode()[0] ?? 1);
        $penalties = $activities->pluck('late_penalty_percent')->filter();
        $penalty = $penalties->isEmpty() ? 'a part' : ($penalties->min() === $penalties->max() ? $penalties->min() . '%' : $penalties->min() . '-' . $penalties->max() . '%');
        $weights = app(CareerCapitalCalculator::class)->categoryWeights($track);
        $experience = TrackExperience::for($track);
        $mentor = app(MentorService::class)->persona($track);
        $types = $activities->map(fn ($a) => $a->type?->value)->unique();

        $cards = [
            ['icon' => '🗺️', 'name' => 'Chapters & activities', 'what' => 'Chapters open one after another. Every activity shows its steps, what to submit and how it is graded.', 'gives' => 'A clear path: you always know what to learn next.'],
            ['icon' => '⏰', 'name' => 'Deadlines', 'what' => "An activity's clock starts when it opens for you, not when you press Start. Most give you about {$deadline} days, plus {$grace} grace " . ($grace === 1 ? 'day' : 'days') . ". Late work loses {$penalty} of its points but can still be submitted.", 'gives' => 'The rhythm of a real job, where work has due dates.'],
            ['icon' => '🧑‍⚖️', 'name' => 'Reviews by the I-NNOVA team', 'what' => 'The team checks every submission against its grading criteria and writes feedback. Work sent back can be fixed and resubmitted.', 'gives' => 'Honest feedback from people who know what employers expect.'],
            ['icon' => '📈', 'name' => 'Career Capital score', 'what' => 'A 0-100 score of how job-ready you are: technical skills (' . round($weights['technical']) . '%), interviews (' . round($weights['interview']) . '%), portfolio (' . round($weights['portfolio']) . '%), collaboration (' . round($weights['collaboration']) . '%) and continuous learning (' . round($weights['learning']) . '%). Your level rises at 25, 50 and 75: ' . ($experience?->levelTitle(Tier::INTERN) ?? 'Intern') . ', ' . ($experience?->levelTitle(Tier::PROFESSIONAL) ?? 'Professional') . ', ' . ($experience?->levelTitle(Tier::ELITE) ?? 'Elite') . '.', 'gives' => 'One number recruiters understand, backed by reviewed work.'],
            ['icon' => '🤖', 'name' => $mentor['name'] . ', your AI mentor', 'what' => 'Ask about any lesson, error message or idea. Press "Stuck?" on a step to ask about exactly that step. It gives hints and questions, not answers.', 'gives' => 'Help at 2 a.m., without anyone doing the work for you.'],
            ['icon' => '🧠', 'name' => 'Explain it back', 'what' => 'Explain a lesson\'s key idea in plain words; the mentor tells you what was clear and what was missing.', 'gives' => 'If you can explain it simply, you really understand it.'],
            ['icon' => '🤝', 'name' => 'Review partners & community', 'what' => 'You are paired with another fellow on team activities, and your track\'s feed shows what classmates finish. Give kudos, ask for help.', 'gives' => 'Nobody gets a data job alone; teamwork is part of your score.'],
            ['icon' => '🎤', 'name' => 'Presentation Day', 'what' => 'Present what you built or learned. The team scores it, and it can raise several parts of your Career Capital at once.', 'gives' => 'Practice speaking about your work, the skill seniors use most.'],
            ['icon' => '✨', 'name' => 'Using AI tools', 'what' => 'ChatGPT, Copilot or Gemini are allowed when you say how you used them. You may be asked to explain your work, so only submit what you understand.', 'gives' => 'Learn to use AI the way good professionals do: openly and with judgement.'],
            ['icon' => '💼', 'name' => 'Employer briefs', 'what' => 'Real challenges posted by companies. Submit a solution; shortlisted and winning work appears on your portfolio.', 'gives' => 'Work for real clients before your first job.'],
            ['icon' => '🎓', 'name' => 'Certificates & public portfolio', 'what' => 'Finish every activity of a skill to earn a verifiable certificate. Your approved projects build a public portfolio page you can share.', 'gives' => 'Proof of skill that employers can check in one click.'],
        ];

        if ($types->contains('mock_interview')) {
            array_splice($cards, 8, 0, [['icon' => '🎙️', 'name' => 'Mock interviews', 'what' => 'Practice interviews on what you just learned, with an AI interviewer or a person. Retry as often as you need.', 'gives' => 'No surprises in a real interview.']]);
        }
        if (FlashCard::where('track_id', $track->id)->where('is_active', true)->exists()) {
            $cards[] = ['icon' => '🔁', 'name' => 'Daily review', 'what' => 'Three quick questions a day from chapters you have reached. Wrong answers come back tomorrow; right ones come back later and later.', 'gives' => 'What you learn in Chapter 1 is still there in Chapter 8.'];
        }
        if (PracticeChallenge::where('track_id', $track->id)->exists()) {
            $cards[] = ['icon' => '🧪', 'name' => 'Practice Lab', 'what' => 'Short hands-on challenges you solve right in the browser, checked instantly, with a daily challenge and a weekly leaderboard.', 'gives' => 'Hours of practice, the only way skills stick.'];
        }
        if (DetectiveCase::where('track_id', $track->id)->exists()) {
            $cards[] = ['icon' => '🕵️', 'name' => 'Data Detective', 'what' => 'A new mystery in the company data every week. Investigate with SQL or Python and crack the case.', 'gives' => 'Practice the way analysts actually work: from a question to an answer.'];
        }

        return $cards;
    }

    public function habits(): array
    {
        return [
            'do' => [
                ['icon' => '📅', 'text' => 'Work a little every day, even 30 minutes. Short, regular sessions beat one long night before the deadline.'],
                ['icon' => '☑️', 'text' => 'Follow the steps and tick them as you go. Run the self-check before you submit.'],
                ['icon' => '🙋', 'text' => 'Stuck for more than 30 minutes? Ask: the mentor, your partner or the community.'],
                ['icon' => '🚀', 'text' => 'Submit on time, then improve. Done and reviewed beats perfect and late.'],
                ['icon' => '🗣️', 'text' => 'Explain what you learned in your own words. Teaching is the fastest way to learn.'],
                ['icon' => '🌍', 'text' => 'Share your work in public: posts, presentations, your portfolio. Opportunities find people who are visible.'],
            ],
            'avoid' => [
                ['icon' => '🪤', 'text' => 'Submitting AI output you cannot explain.'],
                ['icon' => '⏳', 'text' => 'Starting the night before the deadline.'],
                ['icon' => '🙈', 'text' => 'Skipping the review questions because they "feel easy".'],
                ['icon' => '🤐', 'text' => 'Staying silent when you are lost.'],
            ],
        ];
    }

    // ------------------------------------------------------------------ quiz

    /** The rules quiz. Answers never go to the page. */
    protected function quizBank(Track $track): array
    {
        $mentor = app(MentorService::class)->persona($track)['short'];

        return [
            ['key' => 'clock', 'question' => "When does an activity's deadline start counting?",
                'options' => ['When I press Start', 'When the activity opens (unlocks) for me', 'When my review partner starts it', 'After the team reviews my previous activity'],
                'answer' => 1, 'explain' => 'The clock starts when the activity opens for you, so don\'t wait to press Start.'],
            ['key' => 'late', 'question' => 'You missed a deadline by one day. What should you do?',
                'options' => ['Nothing: the activity is closed now', 'Skip it and move to the next activity', 'Submit it anyway: you lose the late penalty but keep most of the points', 'Wait for the team to reset the deadline'],
                'answer' => 2, 'explain' => 'Late work loses the late penalty shown on the activity, but it can still be submitted, even after the grace period. Never skip it.'],
            ['key' => 'ai', 'question' => 'Can you use AI tools like ChatGPT for your work?',
                'options' => ['No, never', 'Yes, but I say how I used them, and I must be able to explain my work', 'Yes, and there is no need to mention it', 'Only for written posts'],
                'answer' => 1, 'explain' => 'AI tools are allowed when you are open about how you used them. You may be asked to explain your work, so only submit what you understand.'],
            ['key' => 'score', 'question' => 'What does your Career Capital score show?',
                'options' => ['How many activities I finished', 'My attendance at sessions', 'How job-ready I am across technical skills, interviews, portfolio, collaboration and continuous learning', 'My daily review streak'],
                'answer' => 2, 'explain' => 'It is a 0-100 picture of job readiness across five areas, built from reviewed work, interviews, presentations and more.'],
            ['key' => 'stuck', 'question' => 'You have been stuck on one step for 40 minutes. What is the best next move?',
                'options' => ['Ask for a hint: press "Stuck?" to ask ' . $mentor . ', or ask your partner or the community', 'Keep trying alone until the deadline', 'Skip the activity', 'Copy a full solution from the internet'],
                'answer' => 0, 'explain' => 'Asking early is a professional habit. A hint keeps the learning yours; a copied solution doesn\'t.'],
        ];
    }

    public function quiz(Track $track): array
    {
        return array_map(fn ($q) => ['key' => $q['key'], 'question' => $q['question'], 'options' => $q['options']], $this->quizBank($track));
    }

    /** @return array{passed: bool, results: array<string, array{correct: bool, explain: ?string}>} */
    public function checkQuiz(TrackOrientation $record, Track $track, array $answers): array
    {
        $results = [];
        foreach ($this->quizBank($track) as $q) {
            $given = $answers[$q['key']] ?? null;
            $correct = $given !== null && (int) $given === $q['answer'];
            // Explanations only for right answers, so a wrong guess doesn't reveal the answer.
            $results[$q['key']] = ['correct' => $correct, 'explain' => $correct ? $q['explain'] : null];
        }
        $passed = collect($results)->every(fn ($r) => $r['correct']);

        $record->quiz_attempts = min(255, $record->quiz_attempts + 1);
        if ($passed && !$record->quiz_passed_at) {
            $record->quiz_passed_at = now();
        }
        $record->save();
        if ($passed) {
            $this->markSection($record, 'quiz');
        }

        return ['passed' => $passed, 'results' => $results];
    }

    // ------------------------------------------------------------------ commitment

    public function complete(TrackOrientation $record, User $fellow, Track $track, array $commitment): void
    {
        $record->commitment = $commitment + ['signed_at' => now()->toDateTimeString()];
        $record->completed_at ??= now();
        $record->save();
        $this->markSection($record, 'commit');

        $first = explode(' ', trim($fellow->name))[0];
        $team = TrackExperience::for($track)?->team();
        app(TrackFeedService::class)->record($fellow, $track->id, 'orientation',
            $team ? "{$first} finished orientation and joined the {$team}" : "{$first} finished orientation and started the {$track->name} track",
            null, null, "orientation:{$track->id}");
    }

    /** The sealed letter opens when every required activity of the track is complete. */
    public function letterCanOpen(User $fellow, Track $track): bool
    {
        $required = TrackCurriculumActivity::where('track_id', $track->id)->where('is_active', true)->where('is_required', true)->pluck('id');
        if ($required->isEmpty()) {
            return false;
        }

        return FellowCurriculumProgress::where('fellow_id', $fellow->id)->whereIn('curriculum_activity_id', $required)
            ->where('status', CurriculumStatus::COMPLETED->value)->count() >= $required->count();
    }

    /** Classmates who already finished this track's orientation (first names), for social proof. */
    public function classmatesDone(Track $track, User $fellow): Collection
    {
        return TrackOrientation::where('track_id', $track->id)->whereNotNull('completed_at')->where('fellow_id', '!=', $fellow->id)
            ->with('fellow:id,name')->latest('completed_at')->limit(8)->get()
            ->map(fn ($o) => explode(' ', trim((string) $o->fellow?->name))[0])->filter()->values();
    }

    /** Staff-card number, e.g. "IKS-DS-0042". */
    public function staffNumber(User $fellow, Track $track): string
    {
        $code = collect(preg_split('/[\s\/-]+/', $track->name))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

        return 'IKS-' . $code . '-' . str_pad((string) $fellow->id, 4, '0', STR_PAD_LEFT);
    }

    public function startedOn(User $fellow, Track $track): ?\Carbon\Carbon
    {
        $ft = FellowTrack::where('fellow_id', $fellow->id)->where('track_id', $track->id)->first();

        return $ft?->started_at ?? $ft?->created_at;
    }
}
