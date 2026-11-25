<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\JobAdvertCheck;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Services\JobAdvertService;
use App\Services\JobMarketService;
use App\Services\StakeholderService;
use App\Support\LessonMarkdown;
use App\Support\TrackExperience;
use Illuminate\Http\Request;

/** Job Market: what employers ask for, the fellow's proven readiness, job advert checks, the hiring loop and the mock offer. */
class CareerController extends Controller
{
    public function __construct(protected JobMarketService $market) {}

    protected function track(Request $request): Track
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);

        return $track;
    }

    public function index(Request $request)
    {
        $fellow = $request->user();
        $track = $this->track($request);
        $data = $this->market->data($track);
        if (!$data) {
            return view('fellow.career.soon', ['track' => $track]);
        }
        $this->market->checkOffer($fellow, $track);
        $proof = $this->market->proof($fellow, $track);
        $ready = $this->market->readiness($fellow, $track, null, $proof);
        $roles = collect($data['roles'])->map(fn ($r) => $r + ['percent' => $this->market->readiness($fellow, $track, $r, $proof)['percent']])->all();
        $checks = JobAdvertCheck::where('fellow_id', $fellow->id)->latest()->limit(10)->get();

        return view('fellow.career.index', [
            'track' => $track, 'experience' => TrackExperience::for($track), 'data' => $data, 'proof' => $proof, 'ready' => $ready, 'roles' => $roles,
            'hiringLoop' => $this->market->interviewLoop($fellow, $track, $ready['percent']),
            'checks' => $checks, 'current' => $request->query('check') ? $checks->firstWhere('id', (int) $request->query('check')) : null,
            'enrolment' => $this->market->enrolment($fellow, $track),
            'tab' => $request->query('tab', $request->query('check') ? 'advert' : 'map'),
        ]);
    }

    public function role(Request $request)
    {
        $track = $this->track($request);
        $keys = array_column($this->market->data($track)['roles'] ?? [], 'key');
        $data = $request->validate(['role' => ['required', 'string', 'in:' . implode(',', $keys)]]);
        $this->market->enrolment($request->user(), $track)?->update(['career_role' => $data['role']]);

        return redirect()->route('career.index')->with('success', 'Target role updated.');
    }

    public function advert(Request $request, JobAdvertService $adverts)
    {
        $data = $request->validate(['advert' => ['required', 'string', 'min:200', 'max:12000']],
            ['advert.min' => 'Paste the whole advert (at least a few paragraphs, including the requirements).']);
        $result = $adverts->analyse($request->user(), $this->track($request), $data['advert']);
        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['error']);
        }

        return redirect()->route('career.index', ['check' => $result['check']->id]);
    }

    public function offer(Request $request)
    {
        $fellow = $request->user();
        $track = $this->track($request);
        $this->market->checkOffer($fellow, $track);
        $enrolment = $this->market->enrolment($fellow, $track);
        abort_unless($enrolment?->offer_unlocked_at, 404);
        $role = collect($this->market->data($track)['roles'] ?? [])->firstWhere('key', $enrolment->offer_role) ?? $this->market->role($fellow, $track);
        $ready = $this->market->readiness($fellow, $track, $role);

        return view('fellow.career.offer', ['track' => $track, 'experience' => TrackExperience::for($track), 'role' => $role, 'ready' => $ready, 'enrolment' => $enrolment,
            'hiringLoop' => $this->market->interviewLoop($fellow, $track, $ready['percent'])]);
    }

    /** Work tickets: ask the stakeholder a clarifying question. */
    public function stakeholder(Request $request, TrackCurriculumActivity $activity, StakeholderService $stakeholders)
    {
        abort_unless($request->user()->isEnrolledIn($activity->track_id), 403);
        $data = $request->validate(['question' => ['required', 'string', 'min:8', 'max:800'], 'history' => ['nullable', 'array', 'max:10']]);
        $result = $stakeholders->ask($request->user(), $activity->loadMissing('track', 'milestone'), $data['question'], $data['history'] ?? []);
        if ($result['ok']) {
            $result['html'] = (string) LessonMarkdown::render($result['reply']);
        }

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
