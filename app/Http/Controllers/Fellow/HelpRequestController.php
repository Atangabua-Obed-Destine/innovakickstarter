<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\FellowTrack;
use App\Models\HelpRequest;
use App\Models\Notification;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Services\FellowActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** "Ask a human": questions from fellows to the I-NNOVA team. */
class HelpRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('fellow.help.index', ['requests' => HelpRequest::where('fellow_id', $request->user()->id)->with('activity:id,title', 'answerer:id,name')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:15', 'max:2000'],
            'activity_id' => ['nullable', 'string', 'max:64'],
            'step_key' => ['nullable', 'string', 'max:120'],
        ], ['question.min' => 'Tell the team a bit more (at least 15 characters): what you tried and where you are stuck.']);
        $fellow = $request->user();
        if (HelpRequest::where('fellow_id', $fellow->id)->where('created_at', '>=', now()->startOfDay())->count() >= 5) {
            return response()->json(['ok' => false, 'error' => 'You have sent 5 questions today. The team will answer them soon.'], 429);
        }
        $activity = !empty($data['activity_id']) ? TrackCurriculumActivity::find($data['activity_id']) : null;
        $help = HelpRequest::create([
            'fellow_id' => $fellow->id,
            'track_id' => $activity?->track_id ?? FellowTrack::where('fellow_id', $fellow->id)->where('is_primary', true)->value('track_id'),
            'curriculum_activity_id' => $activity?->id, 'step_key' => $data['step_key'] ?? null, 'question' => trim($data['question']),
        ]);
        app(FellowActivityService::class)->record($fellow, 'help');
        foreach (User::role('admin')->get() as $admin) {
            Notification::send($admin, Notification::TYPE_NEW_MESSAGE, "🙋 {$fellow->name} asked for help",
                Str::limit(($activity ? "[{$activity->title}] " : '') . $help->question, 180), ['action_url' => route('admin.support.index', ['tab' => 'help']), 'action_label' => 'Answer']);
        }

        return response()->json(['ok' => true, 'message' => 'Sent! The team will answer here and in your notifications.', 'url' => route('help.index')]);
    }
}
