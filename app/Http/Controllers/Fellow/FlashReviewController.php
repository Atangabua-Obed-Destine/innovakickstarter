<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\FlashCard;
use App\Services\FlashReviewService;
use App\Services\PracticeService;
use App\Support\LessonMarkdown;
use Illuminate\Http\Request;

/** Daily flash review: 3 quick questions from earlier chapters. */
class FlashReviewController extends Controller
{
    public function __construct(protected FlashReviewService $review) {}

    public function index(Request $request)
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);
        $cards = $this->review->todaysCards($request->user(), $track);

        return view('fellow.flash-review.index', [
            'track' => $track,
            'cards' => $cards->map(fn (FlashCard $c) => [
                'id' => $c->id,
                'chapter' => $c->chapter,
                'question_html' => (string) LessonMarkdown::render($c->question),
                'options_html' => array_map(fn ($o) => (string) LessonMarkdown::render($o), $c->options),
                'answer_url' => route('flash-review.answer', $c),
            ])->values(),
            'answeredToday' => $this->review->answeredToday($request->user(), $track)->count(),
            'stats' => app(PracticeService::class)->stats($request->user(), $track),
        ]);
    }

    public function answer(Request $request, FlashCard $card)
    {
        abort_unless($card->is_active && $request->user()->isEnrolledIn($card->track_id), 403);
        $data = $request->validate(['option' => ['required', 'integer', 'min:0', 'max:9']]);
        $result = $this->review->answer($request->user(), $card, (int) $data['option']);
        $result['explanation_html'] = $result['explanation'] ? (string) LessonMarkdown::render($result['explanation']) : null;
        unset($result['explanation']);

        return response()->json($result);
    }
}
