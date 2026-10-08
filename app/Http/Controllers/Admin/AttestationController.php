<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\InternshipAttestation;
use App\Services\AttestationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Attestation Controller
 *
 * Review queue, issuing, revoking and reissuing of internship attestations,
 * plus the signatory / branding settings printed on the document.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationController extends Controller
{
    /** Uploadable images and the setting each one is stored under. */
    private const IMAGE_SETTINGS = [
        'logo' => 'attestation_logo_path',
        'signature' => 'attestation_signature_path',
        'stamp' => 'attestation_stamp_path',
    ];

    public function __construct(protected AttestationService $attestations)
    {
    }

    /**
     * Attestation queue, split by what the admin can do next.
     */
    public function index(Request $request): View
    {
        // Keeps the queue correct even where the daily scheduler is not running.
        $this->attestations->syncCompleted();

        $tab = in_array($request->tab, ['ready', 'hold', 'issued', 'revoked'], true) ? $request->tab : 'ready';

        $drafts = InternshipAttestation::draft()
            ->with(['fellow', 'internshipProfile'])
            ->latest()
            ->get()
            ->each(fn (InternshipAttestation $a) => $a->setAttribute(
                'eligibility',
                $this->attestations->eligibility($a->internshipProfile)
            ));

        [$ready, $hold] = $drafts->partition(fn (InternshipAttestation $a) => $a->eligibility['eligible']);

        $issuedQuery = InternshipAttestation::with(['fellow', 'internshipProfile'])
            ->whereIn('status', [InternshipAttestation::STATUS_ISSUED, InternshipAttestation::STATUS_SUPERSEDED]);
        $revokedQuery = InternshipAttestation::with(['fellow', 'internshipProfile'])
            ->where('status', InternshipAttestation::STATUS_REVOKED);

        return view('admin.attestations.index', [
            'tab' => $tab,
            'rows' => match ($tab) {
                'ready' => $ready->values(),
                'hold' => $hold->values(),
                'issued' => $issuedQuery->latest('issued_at')->paginate(20)->withQueryString(),
                'revoked' => $revokedQuery->latest('revoked_at')->paginate(20)->withQueryString(),
            },
            'counts' => [
                'ready' => $ready->count(),
                'hold' => $hold->count(),
                'issued' => (clone $issuedQuery)->where('status', InternshipAttestation::STATUS_ISSUED)->count(),
                'revoked' => $revokedQuery->count(),
            ],
        ]);
    }

    /**
     * Review a draft, or inspect an issued attestation.
     */
    public function show(InternshipAttestation $attestation): View
    {
        $attestation->load(['fellow', 'internshipProfile', 'issuer', 'revoker', 'supersedes', 'supersededBy']);

        return view('admin.attestations.show', [
            'attestation' => $attestation,
            'record' => $attestation->snapshot ?? [],
            'eligibility' => $this->attestations->eligibility($attestation->internshipProfile),
            'mentions' => InternshipAttestation::MENTIONS,
        ]);
    }

    /**
     * Recompile a draft from the fellow's current data.
     */
    public function refresh(InternshipAttestation $attestation): RedirectResponse
    {
        return $this->attempt($attestation, function () use ($attestation) {
            $this->attestations->refresh($attestation);

            return 'Record refreshed from the latest data.';
        });
    }

    /**
     * Watermarked PDF preview of a draft. Nothing is stored.
     */
    public function preview(Request $request, InternshipAttestation $attestation): Response
    {
        abort_unless($attestation->isDraft(), 404);

        $data = $request->validate($this->awardRules(required: false));

        return response($this->attestations->previewPdf($attestation, array_filter($data)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="attestation-preview.pdf"',
        ]);
    }

    /**
     * Issue a draft attestation.
     */
    public function issue(Request $request, InternshipAttestation $attestation): RedirectResponse
    {
        $data = $request->validate($this->awardRules(required: true));

        return $this->attempt($attestation, function () use ($attestation, $data, $request) {
            $issued = $this->attestations->issue($attestation, $data, $request->user());

            return "Attestation {$issued->reference} issued. The fellow has been notified.";
        });
    }

    /**
     * Revoke an issued attestation.
     */
    public function revoke(Request $request, InternshipAttestation $attestation): RedirectResponse
    {
        $data = $request->validate([
            'revocation_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        return $this->attempt($attestation, function () use ($attestation, $data, $request) {
            $this->attestations->revoke($attestation, $data['revocation_reason'], $request->user());

            return "Attestation {$attestation->reference} revoked.";
        });
    }

    /**
     * Open a new draft version replacing an issued or revoked attestation.
     */
    public function reissue(Request $request, InternshipAttestation $attestation): RedirectResponse
    {
        try {
            $draft = $this->attestations->reissue($attestation, $request->user());
        } catch (DomainException $e) {
            return redirect()->route('admin.attestations.show', $attestation)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.attestations.show', $draft)
            ->with('success', 'New version opened as a draft. Review it and issue when ready.');
    }

    /**
     * Download the stored PDF of an issued attestation.
     */
    public function download(InternshipAttestation $attestation): StreamedResponse
    {
        abort_unless($attestation->pdf_path && Storage::exists($attestation->pdf_path), 404);

        return Storage::download($attestation->pdf_path, $attestation->reference . '.pdf');
    }

    /**
     * Signatory, organisation details and mention thresholds.
     */
    public function settings(): View
    {
        $keys = array_filter(
            array_keys(AdminSetting::DEFAULTS),
            fn (string $key) => AdminSetting::DEFAULTS[$key]['group'] === 'attestation'
        );

        return view('admin.attestations.settings', [
            'values' => collect($keys)->mapWithKeys(fn ($key) => [$key => AdminSetting::get($key)])->all(),
        ]);
    }

    /**
     * Save attestation settings and any uploaded images.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attestation_signatory_name' => ['required', 'string', 'max:255'],
            'attestation_signatory_title' => ['required', 'string', 'max:255'],
            'attestation_signatory_title_fr' => ['required', 'string', 'max:255'],
            'attestation_issue_place' => ['required', 'string', 'max:100'],
            'attestation_org_address' => ['required', 'string', 'max:255'],
            'attestation_org_email' => ['nullable', 'email', 'max:255'],
            'attestation_org_website' => ['nullable', 'string', 'max:255'],
            'attestation_mention_excellent_min' => ['required', 'integer', 'min:1', 'max:100', 'gt:attestation_mention_very_good_min'],
            'attestation_mention_very_good_min' => ['required', 'integer', 'min:1', 'max:100', 'gt:attestation_mention_good_min'],
            'attestation_mention_good_min' => ['required', 'integer', 'min:1', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'stamp' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);

        foreach (self::IMAGE_SETTINGS as $field => $key) {
            if ($request->hasFile($field)) {
                $old = AdminSetting::get($key);
                $data[$key] = $request->file($field)->store('attestation-assets');
                if ($old) {
                    Storage::delete($old);
                }
            }
            unset($data[$field]);
        }

        foreach ($data as $key => $value) {
            AdminSetting::set($key, $value ?? '', $request->user()->id);
        }

        return redirect()->route('admin.attestations.settings')
            ->with('success', 'Attestation settings saved. They apply to attestations issued from now on.');
    }

    /**
     * Validation for the mention and remarks an admin awards.
     */
    protected function awardRules(bool $required): array
    {
        return [
            'mention' => [$required ? 'required' : 'nullable', Rule::in(array_keys(InternshipAttestation::MENTIONS))],
            'remarks_en' => ['nullable', 'string', 'max:1200'],
            'remarks_fr' => ['nullable', 'string', 'max:1200'],
        ];
    }

    /**
     * Run a lifecycle action and turn a business-rule failure into a flash error.
     *
     * @param callable(): string $action Returns the success message
     */
    protected function attempt(InternshipAttestation $attestation, callable $action): RedirectResponse
    {
        try {
            $message = $action();
        } catch (DomainException $e) {
            return redirect()->route('admin.attestations.show', $attestation)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.attestations.show', $attestation)->with('success', $message);
    }
}
