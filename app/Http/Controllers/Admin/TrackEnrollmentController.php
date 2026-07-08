<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FellowTrack;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackEnrollmentController extends Controller
{
    /**
     * Queue of track enrollment requests.
     */
    public function index(Request $request): View
    {
        $query = FellowTrack::with(['fellow', 'track']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->awaitingReview();
        }

        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('fellow', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $enrollments = $query->orderByDesc('requested_at')->paginate(20)->withQueryString();

        $counts = [
            'pending' => FellowTrack::where('status', FellowTrack::STATUS_PENDING)->count(),
            'needs_revision' => FellowTrack::where('status', FellowTrack::STATUS_NEEDS_REVISION)->count(),
            'approved' => FellowTrack::where('status', FellowTrack::STATUS_APPROVED)->count(),
            'rejected' => FellowTrack::where('status', FellowTrack::STATUS_REJECTED)->count(),
        ];

        $tracks = \App\Models\Track::orderBy('name')->get(['id', 'name']);

        return view('admin.track-enrollments.index', [
            'enrollments' => $enrollments,
            'counts' => $counts,
            'tracks' => $tracks,
            'filters' => $request->only(['status', 'track_id', 'search']),
        ]);
    }

    public function show(FellowTrack $enrollment): View
    {
        $enrollment->load(['fellow', 'track', 'reviewer']);

        // Other active tracks the fellow already has
        $otherTracks = FellowTrack::with('track')
            ->where('fellow_id', $enrollment->fellow_id)
            ->where('id', '!=', $enrollment->id)
            ->approved()
            ->get();

        return view('admin.track-enrollments.show', [
            'enrollment' => $enrollment,
            'otherTracks' => $otherTracks,
        ]);
    }

    public function approve(Request $request, FellowTrack $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->transition(
            $enrollment,
            FellowTrack::STATUS_APPROVED,
            $request->user()->id,
            $validated['review_notes'] ?? null,
            'approved',
            "Your enrollment in {$enrollment->track?->name} was approved. You can now start earning Career Capital in this track."
        );

        // Ensure started_at is set on first approval
        if (!$enrollment->started_at) {
            $enrollment->update(['started_at' => now()]);
        }

        return redirect()->route('admin.track-enrollments.show', $enrollment)
            ->with('success', 'Track enrollment approved.');
    }

    public function requestChanges(Request $request, FellowTrack $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->transition(
            $enrollment,
            FellowTrack::STATUS_NEEDS_REVISION,
            $request->user()->id,
            $validated['review_notes'],
            'needs_revision',
            "Your enrollment request for {$enrollment->track?->name} needs revision. Reason: {$validated['review_notes']}"
        );

        return redirect()->route('admin.track-enrollments.show', $enrollment)
            ->with('success', 'Revision requested. Fellow notified.');
    }

    public function reject(Request $request, FellowTrack $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->transition(
            $enrollment,
            FellowTrack::STATUS_REJECTED,
            $request->user()->id,
            $validated['review_notes'],
            'rejected',
            "Your enrollment request for {$enrollment->track?->name} was rejected. Reason: {$validated['review_notes']}"
        );

        return redirect()->route('admin.track-enrollments.index')
            ->with('success', 'Track enrollment rejected.');
    }

    protected function transition(
        FellowTrack $enrollment,
        string $status,
        int $adminId,
        ?string $notes,
        string $action,
        string $notificationMessage,
    ): void {
        $oldStatus = $enrollment->status;

        $enrollment->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'review_notes' => $notes,
        ]);

        AuditLog::create([
            'fellow_id' => $enrollment->fellow_id,
            'admin_id' => $adminId,
            'action' => "track_{$action}",
            'auditable_type' => FellowTrack::class,
            'auditable_id' => $enrollment->id,
            'justification' => "Track enrollment {$action}" . ($notes ? ": {$notes}" : ''),
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $status],
        ]);

        Notification::create([
            'user_id' => $enrollment->fellow_id,
            'type' => 'track_enrollment',
            'title' => 'Track enrollment ' . str_replace('_', ' ', $status),
            'message' => $notificationMessage,
            'action_url' => route('tracks.index'),
        ]);
    }
}
