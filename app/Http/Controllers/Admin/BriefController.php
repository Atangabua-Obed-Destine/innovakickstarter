<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BriefSubmission;
use App\Models\EmployerBrief;
use App\Models\Notification;
use App\Models\Track;
use App\Models\User;
use App\Services\TrackFeedService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The team posts employer mini-briefs and reviews submissions. */
class BriefController extends Controller
{
    public function index()
    {
        $briefs = EmployerBrief::with('track')->withCount('submissions')->latest()->get();

        return view('admin.briefs.index', compact('briefs'));
    }

    public function create()
    {
        return view('admin.briefs.form', ['brief' => new EmployerBrief(['status' => 'draft', 'deadline_on' => now()->addWeeks(2)])] + $this->formData());
    }

    public function store(Request $request)
    {
        $brief = EmployerBrief::create($this->validated($request) + ['created_by' => $request->user()->id]);
        if ($brief->status === 'open') {
            $this->announce($brief);
        }

        return redirect()->route('admin.briefs.show', $brief)->with('success', 'Brief saved.');
    }

    public function edit(EmployerBrief $brief)
    {
        return view('admin.briefs.form', ['brief' => $brief] + $this->formData());
    }

    public function update(Request $request, EmployerBrief $brief)
    {
        $wasOpen = $brief->status === 'open';
        $brief->update($this->validated($request));
        if (!$wasOpen && $brief->status === 'open') {
            $this->announce($brief);
        }

        return redirect()->route('admin.briefs.show', $brief)->with('success', 'Brief updated.');
    }

    public function show(EmployerBrief $brief)
    {
        $brief->load(['track', 'recruiter', 'submissions.fellow']);

        return view('admin.briefs.show', compact('brief'));
    }

    public function review(Request $request, EmployerBrief $brief, BriefSubmission $submission)
    {
        abort_unless($submission->brief_id === $brief->id, 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['shortlisted', 'winner', 'not_selected'])],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);
        $submission->update($data + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        $fellow = $submission->fellow;
        $label = BriefSubmission::STATUSES[$data['status']];
        Notification::send($fellow, Notification::TYPE_ACHIEVEMENT, $data['status'] === 'not_selected' ? "Brief result: {$brief->title}" : "🎉 {$label}: {$brief->company_name} brief",
            $data['status'] === 'not_selected' ? 'Thank you for taking part. See the team\'s feedback on your submission.' : "Your submission to \"{$brief->title}\" was {$label}.",
            ['action_url' => route('briefs.show', $brief), 'action_label' => 'View brief']);

        if (in_array($data['status'], ['shortlisted', 'winner'], true)) {
            $trackId = $brief->track_id ?? $fellow->primaryTrack?->track_id;
            if ($trackId) {
                $first = explode(' ', trim($fellow->name))[0];
                app(TrackFeedService::class)->record($fellow, $trackId, 'brief',
                    $data['status'] === 'winner' ? "{$first} won the {$brief->company_name} brief: {$brief->title}" : "{$first} was shortlisted for the {$brief->company_name} brief",
                    null, null, "brief:{$brief->id}:{$data['status']}");
            }
        }

        return back()->with('success', "Marked as {$label}. The fellow was notified.");
    }

    /** Tell the fellows of the brief's track (or all fellows) that a new brief is open. */
    protected function announce(EmployerBrief $brief): void
    {
        $fellows = $brief->track_id
            ? User::whereIn('id', \App\Models\FellowTrack::where('track_id', $brief->track_id)->pluck('fellow_id'))->get()
            : User::where('role', 'fellow')->get();
        foreach ($fellows as $fellow) {
            Notification::send($fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, "💼 New employer brief: {$brief->company_name}",
                "{$brief->title}. Submit your solution by {$brief->deadline_on->format('j M')}.", ['action_url' => route('briefs.show', $brief), 'action_label' => 'View brief']);
        }
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'company_name' => ['required', 'string', 'max:150'],
            'track_id' => ['nullable', 'exists:tracks,id'],
            'recruiter_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'recruiter')],
            'summary' => ['required', 'string', 'max:5000'],
            'deliverable' => ['required', 'string', 'max:3000'],
            'resources_url' => ['nullable', 'url', 'max:500'],
            'deadline_on' => ['required', 'date'],
            'reward' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(array_keys(EmployerBrief::STATUSES))],
        ]);
    }

    protected function formData(): array
    {
        return [
            'tracks' => Track::orderBy('name')->get(['id', 'name']),
            'recruiters' => User::where('role', 'recruiter')->orderBy('company_name')->get(['id', 'name', 'company_name']),
        ];
    }
}
