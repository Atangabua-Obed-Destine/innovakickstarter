<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Services\AttestationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fellow Attestation Controller
 *
 * Lets a fellow follow the progress of their internship attestation and
 * download it once issued. Reachable after the internship has ended, when
 * the rest of the fellow portal is closed to them.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationController extends Controller
{
    /**
     * Attestation status page.
     */
    public function show(Request $request, AttestationService $attestations): View
    {
        $profile = $request->user()->internshipProfile;

        return view('fellow.attestation.show', [
            'profile' => $profile,
            'eligibility' => $profile ? $attestations->eligibility($profile) : null,
            'current' => $profile?->currentAttestation(),
            'issued' => $profile?->issuedAttestation(),
        ]);
    }

    /**
     * Download the fellow's own issued attestation.
     */
    public function download(Request $request): StreamedResponse
    {
        $attestation = $request->user()->internshipProfile?->issuedAttestation();

        abort_unless($attestation && $attestation->pdf_path && Storage::exists($attestation->pdf_path), 404);

        return Storage::download($attestation->pdf_path, $attestation->reference . '.pdf');
    }
}
