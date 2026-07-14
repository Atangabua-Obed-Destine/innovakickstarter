<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\ActivityComment;
use App\Models\TrackCurriculumActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityCommentController extends Controller
{
    /**
     * Store a new comment or reply for an activity.
     */
    public function store(Request $request, TrackCurriculumActivity $activity)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:activity_comments,id',
        ]);

        $fellow = Auth::user();

        // Ensure the fellow is enrolled in the track of this activity
        if (!$fellow->isEnrolledIn($activity->track_id)) {
            abort(403, 'You are not enrolled in this track.');
        }

        // Check if the parent comment actually belongs to this activity
        if (!empty($validated['parent_id'])) {
            $parent = ActivityComment::find($validated['parent_id']);
            if ($parent->curriculum_activity_id !== $activity->id) {
                abort(400, 'Invalid parent comment.');
            }
        }

        ActivityComment::create([
            'curriculum_activity_id' => $activity->id,
            'user_id' => $fellow->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return redirect()->route('curriculum.activity.show', $activity->id)->with('success', 'Comment posted successfully!');
    }

    /**
     * Delete a comment.
     */
    public function destroy(ActivityComment $comment)
    {
        $fellow = Auth::user();

        if ($comment->user_id !== $fellow->id) {
            abort(403, 'You can only delete your own comments.');
        }

        $activityId = $comment->curriculum_activity_id;
        $comment->delete();

        return redirect()->route('curriculum.activity.show', $activityId)->with('success', 'Comment deleted.');
    }
}
