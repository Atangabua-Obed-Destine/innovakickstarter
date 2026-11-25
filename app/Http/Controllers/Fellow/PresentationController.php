<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Services\PresentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Fellows submit presentations they gave (project demos, workshops, talks),
 * any time, for the I-NNOVA team to review.
 */
class PresentationController extends Controller
{
    public function __construct(protected PresentationService $service) {}

    public function index(Request $request)
    {
        $presentations = Presentation::with('track')
            ->where('fellow_id', $request->user()->id)
            ->latest('submitted_at')
            ->get();

        return view('fellow.presentations.index', compact('presentations'));
    }

    public function create(Request $request)
    {
        return view('fellow.presentations.form', [
            'presentation' => new Presentation(['presented_at' => now()]),
            'tracks' => $this->tracks($request),
            'defaultTrackId' => $request->attributes->get('activeTrack')?->track_id ?? $request->user()->primaryTrack?->track_id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $presentation = $this->service->submit($request->user(), $data, $request->file('files', []));

        return redirect()->route('presentations.show', $presentation)
            ->with('success', 'Presentation submitted. The I-NNOVA team will review it.');
    }

    public function show(Request $request, Presentation $presentation)
    {
        $this->authorizeOwner($request, $presentation);
        $presentation->load(['track', 'reviewer']);

        return view('fellow.presentations.show', compact('presentation'));
    }

    public function edit(Request $request, Presentation $presentation)
    {
        $this->authorizeOwner($request, $presentation);
        if (!$presentation->isEditableByFellow()) {
            return redirect()->route('presentations.show', $presentation)->with('error', 'This presentation has already been reviewed.');
        }

        return view('fellow.presentations.form', [
            'presentation' => $presentation,
            'tracks' => $this->tracks($request),
            'defaultTrackId' => $presentation->track_id,
        ]);
    }

    public function update(Request $request, Presentation $presentation)
    {
        $this->authorizeOwner($request, $presentation);
        $data = $this->validated($request, $presentation);

        try {
            $this->service->update($presentation, $data, $request->file('files', []), (array) $request->input('remove_files', []));
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('presentations.show', $presentation)
            ->with('success', 'Presentation updated and sent for review.');
    }

    public function file(Request $request, Presentation $presentation, int $index)
    {
        $this->authorizeOwner($request, $presentation);
        $path = ($presentation->files ?? [])[$index] ?? abort(404);

        return Storage::download($path, basename($path));
    }

    protected function validated(Request $request, ?Presentation $presentation = null): array
    {
        $existingFiles = count($presentation?->files ?? []) - count((array) $request->input('remove_files', []));

        $data = $request->validate([
            'track_id' => ['required', Rule::in($this->tracks($request)->pluck('id'))],
            'title' => ['required', 'string', 'max:200'],
            'kind' => ['required', Rule::in(array_keys(Presentation::KINDS))],
            'presented_at' => ['required', 'date', 'before_or_equal:today', 'after:' . now()->subYears(2)->toDateString()],
            'event_name' => ['required', 'string', 'max:200'],
            'audience' => ['nullable', 'string', 'max:200'],
            'summary' => ['required', 'string', 'min:50', 'max:5000'],
            'learnings' => ['nullable', 'string', 'max:3000'],
            'co_presenters' => ['nullable', 'string', 'max:500'],
            'slides_url' => ['nullable', 'url', 'max:500'],
            'recording_url' => ['nullable', 'url', 'max:500'],
            'repository_url' => ['nullable', 'url', 'max:500'],
            'other_url' => ['nullable', 'url', 'max:500'],
            'files' => ['nullable', 'array', 'max:' . max(0, 5 - max(0, $existingFiles))],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,ppt,pptx,key,odp,jpg,jpeg,png,webp,mp4,mov,zip'],
        ], [
            'track_id.in' => 'Choose one of your tracks.',
            'presented_at.before_or_equal' => 'Submit a presentation after you have given it.',
            'summary.min' => 'Describe the presentation in at least 50 characters.',
            'files.max' => 'A presentation can have at most 5 files.',
        ]);

        $hasEvidence = collect(['slides_url', 'recording_url', 'repository_url', 'other_url'])->contains(fn ($k) => filled($data[$k] ?? null))
            || $request->hasFile('files') || $existingFiles > 0;
        if (!$hasEvidence) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'slides_url' => 'Add at least one link (slides, recording, repository) or upload a file, so the team can see the presentation.',
            ]);
        }

        return $data;
    }

    protected function tracks(Request $request)
    {
        return $request->user()->fellowTracks()->with('track')->get()->pluck('track')->filter()->values();
    }

    protected function authorizeOwner(Request $request, Presentation $presentation): void
    {
        abort_unless($presentation->fellow_id === $request->user()->id, 403);
    }
}
