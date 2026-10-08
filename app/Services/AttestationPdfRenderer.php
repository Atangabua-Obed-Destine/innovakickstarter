<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\InternshipAttestation;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

/**
 * Attestation PDF Renderer
 *
 * Turns an attestation's snapshot into the A4 bilingual document.
 * Everything is embedded (QR, logo, signature, stamp) so the PDF
 * never depends on a remote resource.
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class AttestationPdfRenderer
{
    /**
     * Render the PDF and return its binary content.
     *
     * @param array $record The record to print (the frozen snapshot, or a live preview of it)
     */
    public function render(InternshipAttestation $attestation, array $record, bool $draft = false): string
    {
        return Pdf::loadView('attestations.pdf', [
            'attestation' => $attestation,
            'record' => $record,
            'award' => $record['award'] ?? [],
            'draft' => $draft,
            'qr' => $this->qrDataUri($attestation->verify_url),
            'org' => [
                'address' => AdminSetting::get('attestation_org_address'),
                'email' => AdminSetting::get('attestation_org_email'),
                'website' => AdminSetting::get('attestation_org_website'),
            ],
            'images' => [
                'logo' => $this->imageDataUri(AdminSetting::get('attestation_logo_path')),
                'signature' => $this->imageDataUri(AdminSetting::get('attestation_signature_path')),
                'stamp' => $this->imageDataUri(AdminSetting::get('attestation_stamp_path')),
            ],
        ])->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->output();
    }

    /**
     * QR code for the verification URL as an embeddable PNG.
     */
    public function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 6,
            'quietzoneSize' => 2,
            'outputBase64' => true,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Read an uploaded image from storage as a data URI, or null if absent.
     */
    protected function imageDataUri(?string $path): ?string
    {
        if (!$path || !Storage::exists($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode(Storage::get($path));
    }
}
