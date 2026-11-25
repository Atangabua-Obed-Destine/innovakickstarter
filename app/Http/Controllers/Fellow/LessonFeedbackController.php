<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\LessonFeedback;
use App\Models\TrackCurriculumActivity;
use Illuminate\Http\Request;

/** "Was this lesson clear?" on a curriculum activity. */
class LessonFeedbackController extends Controller
{
    public function store(Request $request, TrackCurriculumActivity $activity)
    {
        $fellow = $request->user();
        abort_unless($fellow->isEnrolledIn($activity->track_id), 403);

        $validated = $request->validate([
            'is_clear' => ['required', 'boolean'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        LessonFeedback::updateOrCreate(
            ['curriculum_activity_id' => $activity->id, 'fellow_id' => $fellow->id],
            ['is_clear' => (bool) $validated['is_clear'], 'comment' => $validated['comment'] ?? null]
        );

        return redirect()->to(route('curriculum.activity.show', $activity) . '#lesson-feedback')
            ->with('feedback_saved', true);
    }
}
