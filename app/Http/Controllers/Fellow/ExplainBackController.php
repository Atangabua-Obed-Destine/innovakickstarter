<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\TrackCurriculumActivity;
use App\Services\ExplainBackService;
use Illuminate\Http\Request;

/** "Explain it back" on a curriculum activity. */
class ExplainBackController extends Controller
{
    public function store(Request $request, TrackCurriculumActivity $activity, ExplainBackService $service)
    {
        abort_unless($request->user()->isEnrolledIn($activity->track_id), 403);
        $data = $request->validate(['text' => ['required', 'string', 'min:60', 'max:2000']], [
            'text.min' => 'Write a little more: 3 to 5 full sentences.',
        ]);
        $result = $service->evaluate($request->user(), $activity, trim($data['text']));

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
