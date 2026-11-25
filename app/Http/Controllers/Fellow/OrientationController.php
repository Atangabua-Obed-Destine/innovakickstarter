<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Services\MentorService;
use App\Services\OrientationService;
use App\Support\TrackExperience;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The track orientation ("Chapter 0") before the first chapter. */
class OrientationController extends Controller
{
    public function __construct(protected OrientationService $orientation) {}

    protected function track(Request $request): Track
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);

        return $track;
    }

    public function show(Request $request)
    {
        $fellow = $request->user();
        $track = $this->track($request);
        $record = $this->orientation->record($fellow, $track);
        $experience = TrackExperience::for($track);
        $content = $this->orientation->content($track);
        $commitment = $record->commitment ?? [];

        return view('fellow.curriculum.orientation', [
            'track' => $track,
            'experience' => $experience,
            'record' => $record,
            'sections' => $this->orientation->sections($track),
            'content' => $content,
            'skills' => $this->orientation->skillPaths($track),
            'toolkit' => $this->orientation->toolkit($track),
            'habits' => $this->orientation->habits(),
            'plan' => $this->orientation->plan($track),
            'quiz' => $this->orientation->quiz($track),
            'required' => $this->orientation->isRequired($fellow, $track),
            'classmates' => $this->orientation->classmatesDone($track, $fellow),
            'staffNumber' => $this->orientation->staffNumber($fellow, $track),
            'startedOn' => $this->orientation->startedOn($fellow, $track) ?? now(),
            'mentor' => app(MentorService::class)->persona($track),
            'letterCanOpen' => $record->completed_at && $this->orientation->letterCanOpen($fellow, $track),
            'defaults' => [
                'goal_role' => $commitment['goal_role'] ?? ($experience?->levelTitle(\App\Enums\Tier::INTERN) ?? $track->name),
                'goal_month' => $commitment['goal_month'] ?? now()->addMonths(6)->format('Y-m'),
                'weekly_hours' => (int) ($commitment['weekly_hours'] ?? $this->orientation->plan($track)['pace']),
                'study_days' => $commitment['study_days'] ?? ['mon', 'tue', 'wed', 'thu', 'sat'],
                'why' => $commitment['why'] ?? '',
                'signature' => $commitment['signature'] ?? '',
            ],
        ]);
    }

    public function section(Request $request)
    {
        $data = $request->validate(['section' => ['required', Rule::in(OrientationService::SECTIONS)]]);
        $record = $this->orientation->record($request->user(), $this->track($request));
        if (!in_array($data['section'], ['quiz', 'commit'], true)) {
            $this->orientation->markSection($record, $data['section']);
        }

        return response()->json(['ok' => true]);
    }

    public function quiz(Request $request)
    {
        $data = $request->validate(['answers' => ['required', 'array'], 'answers.*' => ['nullable', 'integer', 'min:0', 'max:9']]);
        $track = $this->track($request);
        $record = $this->orientation->record($request->user(), $track);

        return response()->json($this->orientation->checkQuiz($record, $track, $data['answers']));
    }

    public function complete(Request $request)
    {
        $fellow = $request->user();
        $track = $this->track($request);
        $record = $this->orientation->record($fellow, $track);
        if (!$record->quiz_passed_at) {
            return response()->json(['ok' => false, 'error' => 'Pass the rules quiz first.'], 422);
        }

        $data = $request->validate([
            'goal_role' => ['required', 'string', 'max:120'],
            'goal_month' => ['required', 'date_format:Y-m', 'after_or_equal:' . now()->format('Y-m')],
            'weekly_hours' => ['required', 'integer', 'min:2', 'max:60'],
            'study_days' => ['required', 'array', 'min:1'],
            'study_days.*' => [Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'why' => ['required', 'string', 'min:40', 'max:2000'],
            'signature' => ['required', 'string', 'max:120'],
        ], [
            'why.min' => 'Write at least a few sentences to your future self (40 characters or more).',
            'goal_month.after_or_equal' => 'Pick a month from now on.',
        ]);

        $first = $record->completed_at === null;
        $this->orientation->complete($record, $fellow, $track, $data);

        return response()->json(['ok' => true, 'first' => $first, 'redirect' => route('curriculum.index')]);
    }

    /** Open the sealed letter once the whole track is complete. */
    public function letter(Request $request)
    {
        $fellow = $request->user();
        $track = $this->track($request);
        $record = $this->orientation->record($fellow, $track);
        abort_unless($record->completed_at && $this->orientation->letterCanOpen($fellow, $track), 403);
        $record->letter_opened_at ??= now();
        $record->save();

        return response()->json(['ok' => true, 'why' => $record->commitment['why'] ?? '', 'signed_at' => $record->commitment['signed_at'] ?? null]);
    }
}
