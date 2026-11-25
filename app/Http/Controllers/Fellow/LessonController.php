<?php

namespace App\Http\Controllers\Fellow;

use App\Enums\CurriculumStatus;
use App\Http\Controllers\Controller;
use App\Models\FellowCurriculumProgress;
use App\Models\FlashCard;
use App\Models\TrackCurriculumActivity;
use App\Services\FlashReviewService;
use App\Services\PreSubmitCheckService;
use App\Services\YouTubeVideoService;
use App\Support\LessonSteps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Fellows tick quickly: lock the row so two ticks arriving together can't overwrite each other.
        $checked = DB::transaction(function () use ($progress, $valid, $data) {
            $row = FellowCurriculumProgress::whereKey($progress->getKey())->lockForUpdate()->first();
            $checked = collect($row->checked_steps ?? [])->intersect($valid);
            $checked = $data['done'] ? $checked->push($data['key'])->unique() : $checked->reject(fn ($k) => $k === $data['key']);
            $row->checked_steps = $checked->values()->all();
            $row->save();

            return $row->checked_steps;
        });

        if ($data['done']) {
            app(\App\Services\FellowActivityService::class)->record($request->user(), 'step');
        }

        return response()->json(['ok' => true, 'checked' => $checked]);
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
            'video' => ['nullable', 'string', 'max:500'],
        ]);
        if (trim(($data['url'] ?? '') . ($data['text'] ?? '')) === '' && empty($data['files'])) {
            return response()->json(['ok' => false, 'error' => 'Add your link, files or written evidence first, then check.'], 422);
        }
        $progress->loadMissing('curriculumActivity.track', 'fellow');

        return response()->json($checker->check($progress, $data));
    }

    /** Show & Tell: check a YouTube link with YouTube before the fellow submits. */
    public function videoCheck(Request $request, FellowCurriculumProgress $progress, YouTubeVideoService $youtube)
    {
        abort_unless($progress->fellow_id === $request->user()->id, 403);
        $data = $request->validate(['url' => ['required', 'string', 'max:500']]);
        $r = $youtube->check($data['url']);

        return response()->json($r + ($r['ok'] ? ['embed' => $youtube->embedUrl($r['id'])] : []), $r['ok'] ? 200 : 422);
    }
}
