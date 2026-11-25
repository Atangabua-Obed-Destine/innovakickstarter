<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\TrackMilestone;
use App\Services\PathwayService;
use Illuminate\Http\Request;

/**
 * Choosing a pathway (major), optional minors and the stack to build in.
 * Guided by a short quiz and real job-advert demand stored in the track's experience.
 */
class PathwayController extends Controller
{
    public function __construct(protected PathwayService $pathways) {}

    private function track(Request $request): ?Track
    {
        return $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
    }

    public function show(Request $request)
    {
        $track = $this->track($request);
        abort_unless($track && $this->pathways->hasPathways($track), 404);
        $fellow = $request->user();
        $chapters = TrackMilestone::where('track_id', $track->id)->where('is_active', true)->orderBy('sequence_order')->get();

        return view('fellow.curriculum.pathway', [
            'track' => $track,
            'definitions' => $this->pathways->definitions($track),
            'quiz' => (array) ($track->experience['pathway_quiz'] ?? []),
            'chapters' => $chapters,
            'major' => $this->pathways->major($fellow, $track),
            'minors' => $this->pathways->minors($fellow, $track),
            'stacks' => (array) ($this->pathways->enrolment($fellow, $track)?->stacks ?? []),
            'coreComplete' => $this->pathways->coreComplete($fellow, $track),
        ]);
    }

    public function store(Request $request)
    {
        $track = $this->track($request);
        abort_unless($track && $this->pathways->hasPathways($track), 404);
        $keys = array_column($this->pathways->definitions($track), 'key');
        $data = $request->validate([
            'pathway' => ['required', 'in:' . implode(',', $keys)],
            'minors' => ['nullable', 'array', 'max:6'],
            'minors.*' => ['string', 'max:40'],
            'stack' => ['nullable', 'string', 'max:60'],
        ]);
        $result = $this->pathways->choose($request->user(), $track, $data['pathway'], $data['minors'] ?? []);
        if (!$result['ok']) {
            return back()->with('error', $result['error']);
        }
        $def = $this->pathways->definition($track, $data['pathway']);
        $allowedStacks = array_merge([(string) ($def['stack'] ?? '')], array_map('strval', (array) ($def['alternatives'] ?? [])));
        if (!empty($data['stack']) && in_array($data['stack'], $allowedStacks, true)) {
            $enrolment = $this->pathways->enrolment($request->user(), $track);
            $enrolment->forceFill(['stacks' => array_merge((array) ($enrolment->stacks ?? []), [$data['pathway'] => $data['stack']])])->save();
        }
        app(\App\Services\FellowActivityService::class)->record($request->user(), 'orientation');

        return redirect()->route('curriculum.index')->with('success', 'Your pathway is set: ' . ($def['title'] ?? $data['pathway']) . '. You can change it or add minors any time.');
    }
}
