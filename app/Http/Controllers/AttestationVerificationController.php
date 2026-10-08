<?php

namespace App\Http\Controllers;

use App\Models\InternshipAttestation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Attestation Verification Controller
 *
 * Public, unauthenticated verification of internship attestations:
 * the QR code on the document lands here.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationVerificationController extends Controller
{
    /**
     * Manual lookup form (reference + printed verification code).
     */
    public function lookup(): View
    {
        return view('public.attestation-lookup');
    }

    /**
     * Resolve a reference + code pair to its verification page.
     */
    public function find(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'max:12'],
        ]);

        $attestation = InternshipAttestation::where('reference', strtoupper(trim($data['reference'])))
            ->where('verification_code', strtoupper(trim($data['code'])))
            ->first();

        if (!$attestation) {
            return back()->withInput()->withErrors([
                'reference' => 'No attestation matches this reference and verification code.',
            ]);
        }

        return redirect()->route('attestation.verify', $attestation->uuid);
    }

    /**
     * Verification page for one attestation. Drafts are never public.
     */
    public function show(string $uuid): View
    {
        $attestation = InternshipAttestation::with('supersededBy')
            ->where('uuid', $uuid)
            ->where('status', '!=', InternshipAttestation::STATUS_DRAFT)
            ->firstOrFail();

        return view('public.attestation-verify', [
            'attestation' => $attestation,
            'record' => $attestation->snapshot,
            'award' => $attestation->snapshot['award'] ?? [],
        ]);
    }
}
