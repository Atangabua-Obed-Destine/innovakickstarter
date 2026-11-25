<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Presentation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Presentations fellows gave, and the I-NNOVA team's review. An approved
 * presentation adds the reviewer's chosen amount to each Career Capital
 * category they ticked, for the presentation's track.
 */
class PresentationService
{
    /** Most a single presentation can add to one category (categories are out of 100). */
    public const MAX_BOOST_PER_CATEGORY = 100;

    public function __construct(protected CareerCapitalCalculator $calculator) {}

    /**
     * @param array $data validated form data
     * @param \Illuminate\Http\UploadedFile[] $files
     */
    public function submit(User $fellow, array $data, array $files = []): Presentation
    {
        return Presentation::create($this->attributes($data) + [
            'fellow_id' => $fellow->id,
            'track_id' => $data['track_id'],
            'files' => $this->storeFiles($fellow, $files) ?: null,
            'status' => Presentation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /** A fellow edits a presentation awaiting review, or resubmits one sent back for changes. */
    public function update(Presentation $presentation, array $data, array $files = [], array $removeFiles = []): Presentation
    {
        if (!$presentation->isEditableByFellow()) {
            throw new \RuntimeException('This presentation has already been reviewed.');
        }

        $kept = array_values(array_diff($presentation->files ?? [], $removeFiles));
        foreach (array_intersect($presentation->files ?? [], $removeFiles) as $path) {
            Storage::delete($path);
        }
        $newFiles = $this->storeFiles($presentation->fellow, $files);
        if (count($kept) + count($newFiles) > 5) {
            foreach ($newFiles as $path) {
                Storage::delete($path);
            }
            throw new \RuntimeException('A presentation can have at most 5 files.');
        }

        $resubmitted = $presentation->status === Presentation::STATUS_CHANGES_REQUESTED;

        $presentation->update($this->attributes($data) + [
            'track_id' => $data['track_id'],
            'files' => array_merge($kept, $newFiles) ?: null,
            'status' => Presentation::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'submission_count' => $presentation->submission_count + ($resubmitted ? 1 : 0),
        ]);

        return $presentation->fresh();
    }

    /**
     * @param string $decision approve | changes | reject
     * @param array  $boosts   category => amount added (approve only)
     */
    public function review(User $reviewer, Presentation $presentation, string $decision, array $boosts, ?string $feedback): Presentation
    {
        if ($presentation->status !== Presentation::STATUS_SUBMITTED) {
            throw new \RuntimeException('This presentation is not awaiting review.');
        }

        $boosts = collect($boosts)
            ->only(array_keys(Presentation::CATEGORIES))
            ->map(fn ($v) => round(max(0, min(self::MAX_BOOST_PER_CATEGORY, (float) $v)), 1))
            ->filter(fn ($v) => $v > 0)
            ->all();

        if ($decision === 'approve' && $boosts === []) {
            throw new \RuntimeException('Tick at least one category and give it an amount, or send the presentation back.');
        }

        return DB::transaction(function () use ($reviewer, $presentation, $decision, $boosts, $feedback) {
            $before = (float) ($presentation->fellow->fellowTracks()->where('track_id', $presentation->track_id)->value('score') ?? 0);

            $presentation->update([
                'status' => match ($decision) {
                    'approve' => Presentation::STATUS_APPROVED,
                    'changes' => Presentation::STATUS_CHANGES_REQUESTED,
                    default => Presentation::STATUS_REJECTED,
                },
                'category_boosts' => $decision === 'approve' ? $boosts : null,
                'review_feedback' => $feedback,
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $after = $before;
            if ($decision === 'approve') {
                $after = (float) $this->calculator->updateScore($presentation->fellow, $presentation->track)->score;
                app(TrackFeedService::class)->presentationApproved($presentation);
            }

            $this->audit($reviewer, $presentation, "presentation_{$decision}", [
                'boosts' => $boosts, 'score_before' => $before, 'score_after' => $after,
            ]);
            $this->notifyFellow($presentation, $decision, $after - $before);

            return $presentation->fresh();
        });
    }

    /** Undo an approval: the presentation goes back to the queue and its amounts are removed. */
    public function revoke(User $admin, Presentation $presentation, string $reason): Presentation
    {
        if (!in_array($presentation->status, [Presentation::STATUS_APPROVED, Presentation::STATUS_REJECTED], true)) {
            throw new \RuntimeException('Only a reviewed presentation can be reopened.');
        }

        return DB::transaction(function () use ($admin, $presentation, $reason) {
            $wasApproved = $presentation->status === Presentation::STATUS_APPROVED;
            $old = $presentation->category_boosts;

            $presentation->update([
                'status' => Presentation::STATUS_SUBMITTED,
                'category_boosts' => null,
                'reviewed_at' => null,
            ]);

            if ($wasApproved) {
                $this->calculator->updateScore($presentation->fellow, $presentation->track, notify: false, markActivity: false);
            }

            $this->audit($admin, $presentation, 'presentation_reopened', ['previous_boosts' => $old, 'reason' => $reason]);

            Notification::send(
                $presentation->fellow,
                Notification::TYPE_ACTIVITY_REVIEW_REVERTED,
                'Presentation review reopened',
                "The review of your presentation \"{$presentation->title}\" was reopened by the I-NNOVA team: {$reason}",
                ['action_url' => route('presentations.show', $presentation), 'action_label' => 'View presentation']
            );

            return $presentation->fresh();
        });
    }

    protected function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'kind' => $data['kind'],
            'presented_at' => $data['presented_at'],
            'event_name' => $data['event_name'],
            'audience' => $data['audience'] ?? null,
            'summary' => $data['summary'],
            'learnings' => $data['learnings'] ?? null,
            'co_presenters' => $data['co_presenters'] ?? null,
            'links' => array_filter([
                'slides' => $data['slides_url'] ?? null,
                'recording' => $data['recording_url'] ?? null,
                'repository' => $data['repository_url'] ?? null,
                'other' => $data['other_url'] ?? null,
            ]) ?: null,
        ];
    }

    protected function storeFiles(User $fellow, array $files): array
    {
        return array_values(array_map(fn ($file) => $file->store('presentations/' . $fellow->id), array_filter($files)));
    }

    protected function audit(User $by, Presentation $presentation, string $action, array $values): void
    {
        AuditLog::create([
            'fellow_id' => $presentation->fellow_id,
            'track_id' => $presentation->track_id,
            'admin_id' => $by->id,
            'action' => mb_substr($action, 0, 30),
            'category' => 'career_capital',
            'auditable_type' => Presentation::class,
            'auditable_id' => $presentation->id,
            'justification' => "Presentation \"{$presentation->title}\": " . str_replace('_', ' ', $action),
            'new_values' => $values,
        ]);
    }

    protected function notifyFellow(Presentation $presentation, string $decision, float $scoreChange): void
    {
        $options = ['action_url' => route('presentations.show', $presentation), 'action_label' => 'View presentation'];
        $title = $presentation->title;

        match ($decision) {
            'approve' => Notification::send($presentation->fellow, Notification::TYPE_ACHIEVEMENT, 'Presentation approved',
                "Your presentation \"{$title}\" was approved" . ($scoreChange > 0 ? ' and added ' . rtrim(rtrim(number_format($scoreChange, 2), '0'), '.') . ' to your Career Capital.' : '.'), $options),
            'changes' => Notification::send($presentation->fellow, Notification::TYPE_ACTIVITY_NEEDS_REVISION, 'Presentation: changes requested',
                "The I-NNOVA team asked for changes to your presentation \"{$title}\". Update it and submit again.", $options),
            default => Notification::send($presentation->fellow, Notification::TYPE_ACTIVITY_REJECTED, 'Presentation not accepted',
                "Your presentation \"{$title}\" was not accepted. See the team's feedback.", $options),
        };
    }
}
