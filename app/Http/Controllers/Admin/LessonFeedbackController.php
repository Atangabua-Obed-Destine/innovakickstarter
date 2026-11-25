<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonFeedback;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Which lessons fellows find unclear, so the curriculum team fixes those first. */
class LessonFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $tracks = Track::orderBy('name')->get(['id', 'name']);
        $trackId = $request->query('track_id');

        $rows = DB::table('lesson_feedback as f')
            ->join('track_curriculum_activities as a', 'a.id', '=', 'f.curriculum_activity_id')
            ->join('track_milestones as m', 'm.id', '=', 'a.milestone_id')
            ->join('tracks as t', 't.id', '=', 'a.track_id')
            ->when($trackId, fn ($q) => $q->where('a.track_id', $trackId))
            ->groupBy('a.id', 'a.title', 'm.title', 'm.sequence_order', 'a.sequence_order', 't.name')
            ->selectRaw('a.id, a.title, m.title as milestone, m.sequence_order as milestone_order, a.sequence_order, t.name as track,
                count(*) as responses, sum(case when f.is_clear then 0 else 1 end) as unclear')
            ->orderByDesc('unclear')
            ->orderBy('milestone_order')
            ->get()
            ->map(function ($row) {
                $row->unclear_pct = $row->responses ? round(100 * $row->unclear / $row->responses) : 0;

                return $row;
            });

        $comments = LessonFeedback::with('fellow:id,name')
            ->whereIn('curriculum_activity_id', $rows->pluck('id'))
            ->whereNotNull('comment')
            ->latest()
            ->get()
            ->groupBy('curriculum_activity_id');

        return view('admin.curriculum.lesson-feedback', compact('rows', 'comments', 'tracks', 'trackId'));
    }
}
