<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InternshipProfile;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternshipController extends Controller
{
    /**
     * List internship profiles with filters.
     */
    public function index(Request $request): View
    {
        // Only show profiles from fellows who actually finished onboarding.
        // Anything else is a draft the fellow is still working on.
        $submittedScope = fn ($q) => $q->whereHas('fellow', fn ($u) => $u->whereNotNull('onboarding_completed_at'));

        $query = InternshipProfile::with(['fellow'])->tap($submittedScope);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default view: things that need action
            $query->whereIn('status', [
                InternshipProfile::STATUS_PENDING,
                InternshipProfile::STATUS_NEEDS_REVISION,
            ]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('institution_name', 'like', "%{$search}%")
                    ->orWhere('supervisor_name', 'like', "%{$search}%")
                    ->orWhere('supervisor_email', 'like', "%{$search}%")
                    ->orWhereHas('fellow', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $profiles = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $countBase = fn () => InternshipProfile::query()->tap($submittedScope);
        $counts = [
            'pending' => (clone $countBase())->where('status', InternshipProfile::STATUS_PENDING)->count(),
            'approved' => (clone $countBase())->where('status', InternshipProfile::STATUS_APPROVED)->count(),
            'needs_revision' => (clone $countBase())->where('status', InternshipProfile::STATUS_NEEDS_REVISION)->count(),
            'rejected' => (clone $countBase())->where('status', InternshipProfile::STATUS_REJECTED)->count(),
            'drafts' => InternshipProfile::whereHas('fellow', fn ($u) => $u->whereNull('onboarding_completed_at'))->count(),
        ];

        return view('admin.internships.index', [
            'profiles' => $profiles,
            'counts' => $counts,
            'filters' => $request->only(['status', 'type', 'search']),
        ]);
    }

    /**
     * Show a single internship profile.
     */
    public function show(InternshipProfile $internship): View
    {
        $internship->load(['fellow', 'reviewer']);

        return view('admin.internships.show', [
            'profile' => $internship,
        ]);
    }

    /**
     * Approve an internship profile.
     */
    public function approve(Request $request, InternshipProfile $internship): RedirectResponse
    {
        $validated = $request->validate([
            'approved_start_date' => ['required', 'date'],
            'approved_end_date' => ['required', 'date', 'after:approved_start_date'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'approved_start_date.required' => 'You must confirm an internship start date.',
            'approved_end_date.required' => 'You must confirm an internship end date.',
            'approved_end_date.after' => 'End date must be after start date.',
        ]);

        $today = now()->startOfDay();
        $start = \Carbon\Carbon::parse($validated['approved_start_date'])->startOfDay();
        $end = \Carbon\Carbon::parse($validated['approved_end_date'])->startOfDay();

        // Choose the right status based on the approved window vs today.
        if ($today->gt($end)) {
            $status = InternshipProfile::STATUS_COMPLETED;
        } elseif ($today->gte($start)) {
            $status = InternshipProfile::STATUS_ACTIVE;
        } else {
            $status = InternshipProfile::STATUS_APPROVED;
        }

        $oldStatus = $internship->status;

        $internship->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_notes' => $validated['review_notes'] ?? null,
            'approved_start_date' => $start,
            'approved_end_date' => $end,
            'completed_at' => $status === InternshipProfile::STATUS_COMPLETED ? now() : null,
        ]);

        AuditLog::create([
            'fellow_id' => $internship->user_id,
            'admin_id' => $request->user()->id,
            'action' => 'internship_approved',
            'auditable_type' => InternshipProfile::class,
            'auditable_id' => $internship->uuid,
            'justification' => "Internship approved for {$start->toDateString()} → {$end->toDateString()}"
                . ($validated['review_notes'] ?? null ? ": {$validated['review_notes']}" : ''),
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $status,
                'approved_start_date' => $start->toDateString(),
                'approved_end_date' => $end->toDateString(),
            ],
        ]);

        $days = (int) $start->diffInDays($end) + 1;
        $msg = match ($status) {
            InternshipProfile::STATUS_ACTIVE => "Your internship at {$internship->institution_name} is approved and now active. You have {$days} days ({$start->format('M j')} → {$end->format('M j, Y')}).",
            InternshipProfile::STATUS_APPROVED => "Your internship at {$internship->institution_name} is approved. It starts on {$start->format('M j, Y')} and runs for {$days} days.",
            default => "Your internship at {$internship->institution_name} was approved but the window has already ended.",
        };

        Notification::create([
            'user_id' => $internship->user_id,
            'type' => 'internship_status',
            'title' => 'Internship approved',
            'message' => $msg,
            'action_url' => route('fellow.onboarding'),
        ]);

        return redirect()->route('admin.internships.show', $internship)
            ->with('success', 'Internship profile approved.');
    }

    /**
     * Request changes from the fellow.
     */
    public function requestChanges(Request $request, InternshipProfile $internship): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->transition(
            $internship,
            InternshipProfile::STATUS_NEEDS_REVISION,
            $request->user()->id,
            $validated['review_notes'],
            'needs_revision',
            "Your internship profile needs revision. Reason: {$validated['review_notes']}"
        );

        return redirect()->route('admin.internships.show', $internship)
            ->with('success', 'Revision requested. The fellow has been notified.');
    }

    /**
     * Reject an internship profile.
     */
    public function reject(Request $request, InternshipProfile $internship): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $this->transition(
            $internship,
            InternshipProfile::STATUS_REJECTED,
            $request->user()->id,
            $validated['review_notes'],
            'rejected',
            "Your internship profile was rejected. Reason: {$validated['review_notes']}"
        );

        return redirect()->route('admin.internships.index')
            ->with('success', 'Internship profile rejected.');
    }

    /**
     * Stream the uploaded internship letter to authenticated admins.
     */
    public function downloadLetter(InternshipProfile $internship): StreamedResponse
    {
        abort_unless($internship->internship_letter_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($internship->internship_letter_path), 404);

        $filename = basename($internship->internship_letter_path);

        return $disk->download($internship->internship_letter_path, $filename);
    }

    /**
     * Shared status transition with audit log and fellow notification.
     */
    protected function transition(
        InternshipProfile $internship,
        string $status,
        int $adminId,
        ?string $notes,
        string $action,
        string $notificationMessage,
    ): void {
        $oldStatus = $internship->status;

        $internship->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
            'review_notes' => $notes,
        ]);

        AuditLog::create([
            'fellow_id' => $internship->user_id,
            'admin_id' => $adminId,
            'action' => "internship_{$action}",
            'auditable_type' => InternshipProfile::class,
            'auditable_id' => $internship->uuid,
            'justification' => "Internship profile {$action}" . ($notes ? ": {$notes}" : ''),
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $status],
        ]);

        Notification::create([
            'user_id' => $internship->user_id,
            'type' => 'internship_status',
            'title' => 'Internship profile ' . str_replace('_', ' ', $status),
            'message' => $notificationMessage,
            'action_url' => route('fellow.onboarding'),
        ]);
    }
}
