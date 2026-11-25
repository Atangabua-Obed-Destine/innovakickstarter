<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FlashAnswer;
use App\Models\FlashCard;
use App\Models\FlashReview;
use App\Models\Track;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Daily flash review with Leitner boxes: 3 cards a day from chapters the
 * fellow has reached, due cards first, then new ones (oldest chapters first).
 * Right: next box (1, 3, 7, 14, 30 days). Wrong: back to box 1, due tomorrow.
 * Lesson quick checks are cards linked to an activity: answered first in the
 * lesson (source "lesson"), they then come back in the daily review when due.
 */
class FlashReviewService
{
    public const SESSION_SIZE = 3;
    public const INTERVALS = [1 => 1, 2 => 3, 3 => 7, 4 => 14, 5 => 30];
    public const XP_PER_CORRECT = 5;

    /** The chapter the fellow is on: the first milestone whose required work isn't all approved. */
    public function currentChapter(User $fellow, Track $track): int
    {
        $milestones = TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')
            ->with(['curriculumActivities' => fn ($q) => $q->where('is_required', true)->where('is_active', true)->select('id', 'milestone_id')])->get();
        $done = FellowCurriculumProgress::where('fellow_id', $fellow->id)->where('status', CurriculumStatus::COMPLETED->value)->pluck('curriculum_activity_id')->flip();

        foreach ($milestones as $m) {
            $ids = $m->curriculumActivities->pluck('id');
            if ($ids->isEmpty() || $ids->contains(fn ($id) => !$done->has($id))) {
                return (int) $m->sequence_order;
            }
        }

        return (int) ($milestones->last()?->sequence_order ?? 1);
    }

    public function answeredToday(User $fellow, Track $track): Collection
    {
        return FlashAnswer::where('fellow_id', $fellow->id)->where('source', 'daily')->whereDate('answered_on', now()->toDateString())
            ->whereIn('card_id', FlashCard::where('track_id', $track->id)->pluck('id'))->get();
    }

    /** The cards still to answer today (up to SESSION_SIZE in total per day). */
    public function todaysCards(User $fellow, Track $track): Collection
    {
        $answered = $this->answeredToday($fellow, $track);
        $remaining = self::SESSION_SIZE - $answered->count();
        if ($remaining <= 0) {
            return collect();
        }

        $chapter = $this->currentChapter($fellow, $track);
        $cards = FlashCard::where('track_id', $track->id)->where('is_active', true)->where('chapter', '<=', $chapter)
            ->whereNotIn('id', $answered->pluck('card_id'))->get()->keyBy('id');
        $reviews = FlashReview::where('fellow_id', $fellow->id)->whereIn('card_id', $cards->keys())->get()->keyBy('card_id');

        $due = $reviews->filter(fn ($r) => $r->due_on->lte(now()->startOfDay()))->sortBy([['due_on', 'asc'], ['box', 'asc']])
            ->map(fn ($r) => $cards[$r->card_id]);
        // Quick checks only join the daily review after the fellow met them in their lesson.
        $new = $cards->reject(fn ($c) => $reviews->has($c->id) || $c->curriculum_activity_id)->sortBy([['chapter', 'asc'], ['key', 'asc']]);

        return $due->concat($new)->take($remaining)->values();
    }

    /**
     * A lesson's quick checks with the fellow's state. Answers and explanations
     * are only included for cards the fellow has already answered.
     */
    public function quickChecks(User $fellow, \App\Models\TrackCurriculumActivity $activity): Collection
    {
        $cards = FlashCard::where('curriculum_activity_id', $activity->id)->where('is_active', true)->orderBy('key')->get();
        $answers = FlashAnswer::where('fellow_id', $fellow->id)->whereIn('card_id', $cards->pluck('id'))->orderBy('id')->get()->groupBy('card_id');

        return $cards->map(function (FlashCard $card) use ($answers) {
            $mine = $answers->get($card->id);
            $first = $mine?->first();

            return [
                'id' => $card->id, 'after_step' => $card->after_step, 'question' => $card->question, 'options' => $card->options,
                'answered' => (bool) $first, 'first_try_correct' => (bool) $first?->correct,
                'answer_index' => $first ? $card->answer_index : null, 'explanation' => $first ? $card->explanation : null,
                'url' => route('curriculum.quick-check', $card),
            ];
        })->values();
    }

    /** @return array{correct: bool, answer_index: int, explanation: ?string, box: int, next_due: string, xp: int, remaining: int} */
    public function answer(User $fellow, FlashCard $card, int $option, string $source = 'daily'): array
    {
        $correct = $option === (int) $card->answer_index;
        $review = FlashReview::firstOrNew(['fellow_id' => $fellow->id, 'card_id' => $card->id], ['box' => 1]);
        if ($source === 'lesson' && $review->exists) {
            // Re-answering a quick check in the lesson doesn't move it in the spaced review.
            $box = (int) $review->box;
            $due = $review->due_on->copy();
        } else {
            $box = $correct ? min(5, ($review->exists ? $review->box : 0) + 1) : 1;
            $due = now()->startOfDay()->addDays($correct ? self::INTERVALS[$box] : 1);
            $review->fill([
                'box' => $box,
                'due_on' => $due->toDateString(),
                'last_reviewed_on' => now()->toDateString(),
                'times_right' => ($review->times_right ?? 0) + ($correct ? 1 : 0),
                'times_wrong' => ($review->times_wrong ?? 0) + ($correct ? 0 : 1),
            ])->save();
        }

        // XP: in the daily review once per card per day; in a lesson only for a first-try right answer.
        $earlier = FlashAnswer::where('fellow_id', $fellow->id)->where('card_id', $card->id);
        $xpAllowed = $source === 'lesson' ? !(clone $earlier)->exists() : !(clone $earlier)->whereDate('answered_on', now()->toDateString())->exists();
        $xp = $correct && $xpAllowed ? self::XP_PER_CORRECT : 0;
        FlashAnswer::create(['fellow_id' => $fellow->id, 'card_id' => $card->id, 'correct' => $correct, 'xp_awarded' => $xp, 'source' => $source, 'answered_on' => now()->toDateString()]);
        app(FellowActivityService::class)->record($fellow, $source === 'lesson' ? 'quick_check' : 'review');

        $track = Track::find($card->track_id);
        if ($correct) {
            app(TrackFeedService::class)->practiceStreak($fellow, $card->track_id, app(PracticeService::class)->stats($fellow, $track)['streak']);
        }

        return [
            'correct' => $correct,
            'answer_index' => (int) $card->answer_index,
            'explanation' => $card->explanation,
            'box' => $box,
            'next_due' => $due->toDateString(),
            'xp' => $xp,
            'remaining' => max(0, self::SESSION_SIZE - $this->answeredToday($fellow, $track)->count()),
        ];
    }
}
