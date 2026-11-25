<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\FellowTrack;
use App\Models\TrackFeedEvent;
use App\Services\TrackFeedService;
use App\Support\TrackExperience;
use Illuminate\Http\Request;

/** The track community feed: what fellows in your track achieved, with kudos. */
class CommunityController extends Controller
{
    public function __construct(protected TrackFeedService $feed) {}

    public function index(Request $request)
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);

        return view('fellow.community.index', [
            'track' => $track,
            'experience' => TrackExperience::for($track),
            'events' => $this->feed->feed($track, $request->user()),
            'fellowCount' => FellowTrack::where('track_id', $track->id)->count(),
        ]);
    }

    public function kudos(Request $request, TrackFeedEvent $event)
    {
        abort_unless($request->user()->isEnrolledIn($event->track_id), 403);
        [$given, $count] = $this->feed->toggleKudos($request->user(), $event);

        return response()->json(['given' => $given, 'count' => $count]);
    }

    public function visibility(Request $request)
    {
        $data = $request->validate(['show_in_track_feed' => ['required', 'boolean']]);
        $request->user()->update(['show_in_track_feed' => (bool) $data['show_in_track_feed']]);

        return back()->with('success', $data['show_in_track_feed'] ? 'Your achievements now appear in your track feed.' : 'Your achievements are now hidden from other fellows.');
    }
}
