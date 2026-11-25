<?php

namespace App\Http\Controllers\Fellow;

use App\Enums\CurriculumStatus;
use App\Http\Controllers\Controller;
use App\Models\FellowCurriculumProgress;
use App\Models\FlashCard;
use App\Models\TrackCurriculumActivity;
use App\Services\FlashReviewService;
use App\Services\PreSubmitCheckService;
use App\Support\LessonSteps;
use Illuminate\Http\Request;

/** Interactive lesson pieces: ticking steps, quick checks and the pre-submit check. */
class LessonController extends Controller
{
    protected function openProgress(Request $request, TrackCurriculumActivity $activity): FellowCurriculumProgress
    {
        $progress = FellowCurriculumProgress::where('fellow_id', $request->user()->id)->where('curriculum_activity_id', $activity->id)->first();
        abort_if(!$progress || $progress->status === CurriculumStatus::LOCKED, 403, 'This lesson is not open for you yet.');

        return $progress;
    }

    /** Tick or untick a step / self-check item. */
    public function step(Request $request, TrackCurriculumActivity $activity)
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:120'], 'done' => ['required', 'boolean']]);
        $progress = $this->openProgress($request, $activity);
        $valid = LessonSteps::keys(LessonSteps::parse($activity->instructions));
        abort_unless(in_array($data['key'], $valid, true), 422);

        $checked = collect($progress->checked_steps ?? [])->intersect($valid);
        $checked = $data['done'] ? $checked->push($data['key'])->unique() : $checked->reject(fn ($k) => $k === $data['key']);
        $progress->checked_steps = $checked->values()->all();
        $progress->save();

        return response()->json(['ok' => true, 'checked' => $progress->checked_steps]);
    }

    /** Answer a lesson quick check. It then joins the fellow's spaced daily review. */
    public function quickCheck(Request $request, FlashCard $card, FlashReviewService $flash)
    {
        $data = $request->validate(['option' => ['required', 'integer', 'min:0', 'max:9']]);
        abort_unless($card->curriculum_activity_id && $card->is_active, 404);
        $this->openProgress($request, TrackCurriculumActivity::findOrFail($card->curriculum_activity_id));

        $result = $flash->answer($request->user(), $card, $data['option'], 'lesson');
        unset($result['remaining']);

        return response()->json($result);
    }

    /** "Check my work first": AI feedback on a draft submission against the activity's requirements. */
    public function precheck(Request $request, FellowCurriculumProgress $progress, PreSubmitCheckService $checker)
    {
        abort_unless($progress->fellow_id === $request->user()->id, 403);
        abort_unless($progress->status->canSubmit(), 422);
        $data = $request->validate([
            'url' => ['nullable', 'string', 'max:500'],
            'text' => ['nullable', 'string', 'max:5000'],
            'reflection' => ['nullable', 'string', 'max:2000'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['string', 'max:200'],
        ]);
        if (trim(($data['url'] ?? '') . ($data['text'] ?? '')) === '' && empty($data['files'])) {
            return response()->json(['ok' => false, 'error' => 'Add your link, files or written evidence first, then check.'], 422);
        }
        $progress->loadMissing('curriculumActivity.track', 'fellow');

        return response()->json($checker->check($progress, $data));
    }
}
