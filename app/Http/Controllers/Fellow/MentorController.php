<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Services\MentorService;
use App\Support\LessonMarkdown;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The AI track mentor chat. */
class MentorController extends Controller
{
    public function __construct(protected MentorService $mentor) {}

    public function index(Request $request)
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);

        return view('fellow.mentor.index', ['track' => $track, 'persona' => $this->mentor->persona($track)]);
    }

    public function history(Request $request)
    {
        [$track, $type, $id] = $this->context($request);

        return response()->json([
            'messages' => $this->mentor->history($request->user(), $type, $id)->map(fn ($m) => [
                'role' => $m->role,
                'html' => $m->role === 'assistant' ? (string) LessonMarkdown::render($m->content) : null,
                'text' => $m->role === 'user' ? $m->content : null,
            ])->values(),
            'remaining' => max(0, MentorService::DAILY_LIMIT - $this->mentor->usedToday($request->user())),
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate(['message' => ['required', 'string', 'min:2', 'max:1500']]);
        [$track, $type, $id] = $this->context($request);

        $result = $this->mentor->ask($request->user(), $track, trim($request->input('message')), $type, $id);
        if ($result['ok']) {
            $result['html'] = (string) LessonMarkdown::render($result['reply']);
            unset($result['reply']);
        }

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /** @return array{0: Track, 1: string, 2: ?string} */
    protected function context(Request $request): array
    {
        $data = $request->validate([
            'context_type' => ['nullable', Rule::in(['general', 'activity', 'challenge'])],
            'context_id' => ['nullable', 'string', 'max:64'],
        ]);
        $type = $data['context_type'] ?? 'general';
        $id = $type === 'general' ? null : ($data['context_id'] ?? null);
        $fellow = $request->user();

        $trackId = match ($type) {
            'activity' => TrackCurriculumActivity::whereKey($id)->value('track_id'),
            'challenge' => PracticeChallenge::whereKey($id)->value('track_id'),
            default => ($request->attributes->get('activeTrack')?->track_id ?? $fellow->primaryTrack?->track_id),
        };
        abort_unless($trackId && $fellow->isEnrolledIn($trackId), 403);

        return [Track::findOrFail($trackId), $type, $id];
    }
}
