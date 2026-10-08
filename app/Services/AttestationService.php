<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\AuditLog;
use App\Models\Fee;
use App\Models\InternshipAttestation;
use App\Models\InternshipProfile;
use App\Models\Notification;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Attestation Service
 *
 * Owns the attestation lifecycle: a draft is compiled when an internship
 * completes, an admin issues it (which freezes the record and stores the
 * PDF), and an issued attestation can later be revoked or reissued.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationService
{
    /** Characters for the printed verification code (no 0/O, 1/I/L). */
    private const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function __construct(
        protected AttestationRecordBuilder $builder,
        protected AttestationPdfRenderer $renderer,
    ) {
    }

    // ─── Eligibility ─────────────────────────────────────────────

    /**
     * Requirements for issuing: the internship has ended and no fee is owing.
     *
     * @return array{eligible: bool, checks: array<int, array{label: string, passed: bool, detail: string}>}
     */
    public function eligibility(InternshipProfile $profile): array
    {
        $ended = $profile->status === InternshipProfile::STATUS_COMPLETED
            && $profile->approved_start_date && $profile->approved_end_date;

        $owing = Fee::forFellow($profile->user_id)->unpaid()->get()
            ->filter(fn (Fee $fee) => $fee->balance > 0);

        $checks = [
            [
                'label' => 'Internship period ended',
                'passed' => (bool) $ended,
                'detail' => $ended
                    ? 'Ended ' . $profile->approved_end_date->format('M j, Y')
                    : 'Internship status is "' . $profile->status . '"',
            ],
            [
                'label' => 'All fees settled',
                'passed' => $owing->isEmpty(),
                'detail' => $owing->isEmpty()
                    ? 'No outstanding balance'
                    : number_format($owing->sum->balance) . ' CFA outstanding on ' . $owing->count() . ' fee(s)',
            ],
        ];

        return [
            'eligible' => collect($checks)->every(fn ($c) => $c['passed']),
            'checks' => $checks,
        ];
    }

    // ─── Drafts ──────────────────────────────────────────────────

    /**
     * Mark an expired internship completed, tell the fellow, and open a draft.
     */
    public function completeInternship(InternshipProfile $profile): void
    {
        $profile->update([
            'status' => InternshipProfile::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $profile->user_id,
            'type' => 'internship_status',
            'title' => 'Internship period ended',
            'message' => "Your approved internship ended on {$profile->approved_end_date->format('M j, Y')}. Your attestation is being prepared.",
            'action_url' => route('fellow.attestation.show'),
        ]);

        $this->ensureDraft($profile);
    }

    /**
     * Complete every internship whose window has passed and make sure each
     * completed internship has an attestation. Safe to run repeatedly.
     *
     * @return int Number of internships transitioned to completed
     */
    public function syncCompleted(): int
    {
        $expired = InternshipProfile::whereIn('status', [InternshipProfile::STATUS_APPROVED, InternshipProfile::STATUS_ACTIVE])
            ->whereNotNull('approved_end_date')
            ->whereDate('approved_end_date', '<', now()->toDateString())
            ->get();

        $expired->each(fn (InternshipProfile $profile) => $this->completeInternship($profile));

        InternshipProfile::where('status', InternshipProfile::STATUS_COMPLETED)
            ->whereNotNull('approved_start_date')
            ->whereNotNull('approved_end_date')
            ->whereDoesntHave('attestations')
            ->get()
            ->each(fn (InternshipProfile $profile) => $this->ensureDraft($profile));

        return $expired->count();
    }

    /**
     * Return the internship's current attestation, creating a draft if none exists.
     */
    public function ensureDraft(InternshipProfile $profile): ?InternshipAttestation
    {
        if (!$profile->approved_start_date || !$profile->approved_end_date) {
            return null;
        }

        $existing = $profile->currentAttestation();
        if ($existing) {
            return $existing;
        }

        return $this->compileInto(new InternshipAttestation([
            'internship_profile_id' => $profile->id,
            'fellow_id' => $profile->user_id,
            'status' => InternshipAttestation::STATUS_DRAFT,
            'version' => 1,
        ]), $profile);
    }

    /**
     * Recompile a draft from the fellow's current data.
     */
    public function refresh(InternshipAttestation $attestation): InternshipAttestation
    {
        $this->assertDraft($attestation);

        return $this->compileInto($attestation, $attestation->internshipProfile);
    }

    /**
     * Suggest a mention from curriculum results, completion, attendance and interviews.
     */
    public function suggestMention(array $record): ?string
    {
        $primary = $record['tracks'][0]['curriculum'] ?? null;

        $components = array_filter([
            $primary['average_score'] ?? null,
            ($primary['required_total'] ?? 0) > 0
                ? ($primary['required_completed'] / $primary['required_total']) * 100
                : null,
            $record['attendance']['rate'] ?? null,
            $record['interviews']['average_score'] ?? null,
        ], fn ($v) => $v !== null);

        if (!$components) {
            return null;
        }

        $result = array_sum($components) / count($components);

        return match (true) {
            $result >= AdminSetting::get('attestation_mention_excellent_min', 85) => 'excellent',
            $result >= AdminSetting::get('attestation_mention_very_good_min', 70) => 'very_good',
            $result >= AdminSetting::get('attestation_mention_good_min', 55) => 'good',
            default => 'satisfactory',
        };
    }

    // ─── Issue / revoke / reissue ────────────────────────────────

    /**
     * Issue a draft: freeze the record, assign the reference, store the PDF.
     *
     * @param array{mention: string, remarks_en?: ?string, remarks_fr?: ?string} $data
     * @throws DomainException when the attestation cannot be issued
     */
    public function issue(InternshipAttestation $attestation, array $data, User $admin): InternshipAttestation
    {
        $this->assertDraft($attestation);

        $profile = $attestation->internshipProfile;
        if (!$this->eligibility($profile)['eligible']) {
            throw new DomainException('This attestation cannot be issued yet: the internship must have ended and all fees must be settled.');
        }

        $this->assertPublicVerifyUrl($attestation);

        return DB::transaction(function () use ($attestation, $data, $admin, $profile) {
            $record = $this->builder->build($profile);
            $issuedAt = now();

            $record['award'] = [
                'mention' => $data['mention'],
                'mention_en' => InternshipAttestation::MENTIONS[$data['mention']][0],
                'mention_fr' => InternshipAttestation::MENTIONS[$data['mention']][1],
                'remarks_en' => $data['remarks_en'] ?? null,
                'remarks_fr' => $data['remarks_fr'] ?? null,
                'signatory_name' => AdminSetting::get('attestation_signatory_name'),
                'signatory_title' => AdminSetting::get('attestation_signatory_title'),
                'signatory_title_fr' => AdminSetting::get('attestation_signatory_title_fr'),
                'place' => AdminSetting::get('attestation_issue_place'),
                'issued_at' => $issuedAt->toDateString(),
            ];

            $reference = $this->generateReference();

            $attestation->fill([
                'reference' => $reference,
                'verification_code' => $this->generateVerificationCode(),
                'status' => InternshipAttestation::STATUS_ISSUED,
                'mention' => $data['mention'],
                'suggested_mention' => $this->suggestMention($record),
                'remarks_en' => $data['remarks_en'] ?? null,
                'remarks_fr' => $data['remarks_fr'] ?? null,
                'snapshot' => $record,
                'content_hash' => hash('sha256', $reference . '|' . json_encode($record)),
                'signatory_name' => $record['award']['signatory_name'],
                'signatory_title' => $record['award']['signatory_title'],
                'issued_at' => $issuedAt,
                'issued_by' => $admin->id,
            ]);

            $path = 'attestations/' . $attestation->fellow->uuid . '/' . $reference . '.pdf';
            Storage::put($path, $this->renderer->render($attestation, $record));
            $attestation->pdf_path = $path;
            $attestation->save();

            // A reissued version replaces the one it was created from.
            if ($attestation->supersedes_id) {
                InternshipAttestation::whereKey($attestation->supersedes_id)
                    ->where('status', InternshipAttestation::STATUS_ISSUED)
                    ->update(['status' => InternshipAttestation::STATUS_SUPERSEDED]);
            }

            $this->audit($attestation, $admin, 'attestation_issued', "Attestation {$reference} issued with mention \"{$record['award']['mention_en']}\"", [
                'reference' => $reference,
                'mention' => $data['mention'],
                'version' => $attestation->version,
            ]);

            Notification::create([
                'user_id' => $attestation->fellow_id,
                'type' => 'attestation_issued',
                'title' => 'Your internship attestation is ready',
                'message' => "Attestation {$reference} has been issued. You can download it and share its verification link.",
                'action_url' => route('fellow.attestation.show'),
            ]);

            return $attestation;
        });
    }

    /**
     * Revoke an issued attestation. Its verification page will say so.
     */
    public function revoke(InternshipAttestation $attestation, string $reason, User $admin): InternshipAttestation
    {
        if (!$attestation->isIssued()) {
            throw new DomainException('Only an issued attestation can be revoked.');
        }

        return DB::transaction(function () use ($attestation, $reason, $admin) {
            $attestation->update([
                'status' => InternshipAttestation::STATUS_REVOKED,
                'revoked_at' => now(),
                'revoked_by' => $admin->id,
                'revocation_reason' => $reason,
            ]);

            $this->audit($attestation, $admin, 'attestation_revoked', "Attestation {$attestation->reference} revoked: {$reason}", [
                'reference' => $attestation->reference,
            ]);

            Notification::create([
                'user_id' => $attestation->fellow_id,
                'type' => 'attestation_revoked',
                'title' => 'Your internship attestation was revoked',
                'message' => "Attestation {$attestation->reference} is no longer valid. Reason: {$reason}",
                'action_url' => route('fellow.attestation.show'),
            ]);

            return $attestation;
        });
    }

    /**
     * Open a new draft version that will replace an issued or revoked attestation.
     */
    public function reissue(InternshipAttestation $attestation, User $admin): InternshipAttestation
    {
        if (!$attestation->isIssued() && !$attestation->isRevoked()) {
            throw new DomainException('Only an issued or revoked attestation can be reissued.');
        }

        $profile = $attestation->internshipProfile;
        $openDraft = $profile->attestations()->draft()->first();
        if ($openDraft) {
            return $openDraft;
        }

        $draft = $this->compileInto(new InternshipAttestation([
            'internship_profile_id' => $profile->id,
            'fellow_id' => $profile->user_id,
            'status' => InternshipAttestation::STATUS_DRAFT,
            'version' => $profile->attestations()->max('version') + 1,
            'supersedes_id' => $attestation->id,
            'mention' => $attestation->mention,
            'remarks_en' => $attestation->remarks_en,
            'remarks_fr' => $attestation->remarks_fr,
        ]), $profile);

        $this->audit($draft, $admin, 'attestation_reissue_started', "New version of attestation {$attestation->reference} opened", [
            'replaces' => $attestation->reference,
            'version' => $draft->version,
        ]);

        return $draft;
    }

    /**
     * Render a draft as it would print today, watermarked, without storing it.
     */
    public function previewPdf(InternshipAttestation $attestation, array $data = []): string
    {
        $record = $attestation->snapshot ?? $this->builder->build($attestation->internshipProfile);
        $mention = $data['mention'] ?? $attestation->mention ?? $attestation->suggested_mention;

        $record['award'] = [
            'mention' => $mention,
            'mention_en' => InternshipAttestation::MENTIONS[$mention][0] ?? null,
            'mention_fr' => InternshipAttestation::MENTIONS[$mention][1] ?? null,
            'remarks_en' => $data['remarks_en'] ?? $attestation->remarks_en,
            'remarks_fr' => $data['remarks_fr'] ?? $attestation->remarks_fr,
            'signatory_name' => AdminSetting::get('attestation_signatory_name'),
            'signatory_title' => AdminSetting::get('attestation_signatory_title'),
            'signatory_title_fr' => AdminSetting::get('attestation_signatory_title_fr'),
            'place' => AdminSetting::get('attestation_issue_place'),
            'issued_at' => now()->toDateString(),
        ];

        return $this->renderer->render($attestation, $record, draft: true);
    }

    // ─── Internals ───────────────────────────────────────────────

    /**
     * Compile the live record into a draft and save it.
     */
    protected function compileInto(InternshipAttestation $attestation, InternshipProfile $profile): InternshipAttestation
    {
        $record = $this->builder->build($profile);

        $attestation->snapshot = $record;
        $attestation->suggested_mention = $this->suggestMention($record);
        $attestation->track_id = $profile->fellow->primaryTrack?->track_id;
        $attestation->save();

        return $attestation;
    }

    /**
     * Next reference for the current year: IKS-ATT-YYYY-NNNN.
     */
    protected function generateReference(): string
    {
        $prefix = 'IKS-ATT-' . now()->format('Y') . '-';

        $last = InternshipAttestation::where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected function generateVerificationCode(): string
    {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
        }

        return substr($code, 0, 4) . '-' . substr($code, 4);
    }

    protected function assertDraft(InternshipAttestation $attestation): void
    {
        if (!$attestation->isDraft()) {
            throw new DomainException('This attestation has already been issued and can no longer be changed.');
        }
    }

    /**
     * The QR link is printed into a permanent document, so refuse to bake in
     * a localhost address anywhere other than a developer machine.
     */
    protected function assertPublicVerifyUrl(InternshipAttestation $attestation): void
    {
        $host = parse_url($attestation->verify_url, PHP_URL_HOST);

        if (in_array($host, ['localhost', '127.0.0.1'], true) && !app()->environment('local', 'testing')) {
            throw new DomainException('The verification link would point to "' . $host . '". Set APP_URL to the public address before issuing.');
        }
    }

    protected function audit(InternshipAttestation $attestation, User $admin, string $action, string $justification, array $values): void
    {
        AuditLog::create([
            'fellow_id' => $attestation->fellow_id,
            'admin_id' => $admin->id,
            'action' => $action,
            'auditable_type' => InternshipAttestation::class,
            'auditable_id' => $attestation->uuid,
            'justification' => $justification,
            'new_values' => $values,
        ]);
    }
}
