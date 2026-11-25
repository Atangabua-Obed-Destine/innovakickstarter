<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Services\CareerCapitalCalculator;
use App\Services\PresentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * The I-NNOVA team reviews presentations: ticks the Career Capital categories
 * a presentation showed and decides how much it adds to each, with a live
 * preview of the effect on the fellow's score.
 */
class PresentationController extends Controller
{
    public function __construct(protected PresentationService $service, protected CareerCapitalCalculator $calculator) {}

    public function index(Request $request)
    {
        $status = $request->query('status', Presentation::STATUS_SUBMITTED);
        $statuses = [Presentation::STATUS_SUBMITTED, Presentation::STATUS_CHANGES_REQUESTED, Presentation::STATUS_APPROVED, Presentation::STATUS_REJECTED, 'all'];
        $status = in_array($status, $statuses, true) ? $status : Presentation::STATUS_SUBMITTED;

        $presentations = Presentation::with(['fellow', 'track', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy($status === Presentation::STATUS_SUBMITTED ? 'submitted_at' : 'updated_at', $status === Presentation::STATUS_SUBMITTED ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        $counts = Presentation::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.presentations.index', compact('presentations', 'status', 'counts'));
    }

    public function show(Presentation $presentation)
    {
        $presentation->load(['fellow', 'track', 'reviewer']);

        $enrolled = $presentation->fellow->fellowTracks()->where('track_id', $presentation->track_id)->exists();
        $basis = $enrolled ? $this->calculator->presentationPreviewBasis($presentation->fellow, $presentation->track, $presentation->id) : null;

        $otherPresentations = Presentation::where('fellow_id', $presentation->fellow_id)
            ->whereKeyNot($presentation->id)
            ->latest('submitted_at')
            ->limit(10)
            ->get(['id', 'title', 'status', 'presented_at', 'category_boosts']);

        return view('admin.presentations.show', compact('presentation', 'basis', 'otherPresentations'));
    }

    public function review(Request $request, Presentation $presentation)
    {
        $max = PresentationService::MAX_BOOST_PER_CATEGORY;
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'changes', 'reject'])],
            'categories' => ['nullable', 'array'],
            'categories.*' => [Rule::in(array_keys(Presentation::CATEGORIES))],
            'boosts' => ['nullable', 'array'],
            'boosts.*' => ['nullable', 'numeric', 'min:0', "max:{$max}"],
            'feedback' => ['nullable', 'string', 'max:3000', 'required_unless:decision,approve'],
        ], [
            'feedback.required_unless' => 'Tell the fellow what to change, or why it was not accepted.',
            'boosts.*.max' => "One presentation can add at most {$max} to a category.",
        ]);

        // Only ticked categories count.
        $boosts = collect($validated['categories'] ?? [])
            ->mapWithKeys(fn ($category) => [$category => (float) ($validated['boosts'][$category] ?? 0)])
            ->all();

        try {
            $this->service->review($request->user(), $presentation, $validated['decision'], $boosts, $validated['feedback'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = match ($validated['decision']) {
            'approve' => 'Presentation approved and Career Capital updated.',
            'changes' => 'Sent back to the fellow for changes.',
            default => 'Presentation marked as not accepted.',
        };

        return redirect()->route('admin.presentations.index')->with('success', $message);
    }

    public function reopen(Request $request, Presentation $presentation)
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $this->service->revoke($request->user(), $presentation, $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.presentations.show', $presentation)
            ->with('success', 'Review reopened. Any amounts it added have been removed from the fellow\'s score.');
    }

    public function file(Presentation $presentation, int $index)
    {
        $path = ($presentation->files ?? [])[$index] ?? abort(404);

        return Storage::download($path, basename($path));
    }
}
