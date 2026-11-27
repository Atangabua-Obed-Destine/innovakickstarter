<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

// Plays the role of the mobile-money provider: sends a signed callback to your API.
// Example: php artisan nkwen:fake-momo-callback 7b0c...e1 36005
class FakeMomoCallback extends Command
{
    protected $signature = 'nkwen:fake-momo-callback {reference} {amount}
        {--status=SUCCESSFUL : SUCCESSFUL or FAILED}
        {--url=http://127.0.0.1:8000 : Base URL of the API}
        {--secret= : Shared secret (default: MOMO_WEBHOOK_SECRET from .env)}
        {--bad-signature : Send a wrong signature on purpose}';

    protected $description = 'Send a signed mobile-money callback, like the provider would';

    public function handle(): int
    {
        $body = json_encode([
            'referenceId' => $this->argument('reference'),
            'externalId' => 'nkwen-test',
            'amount' => (string) $this->argument('amount'),
            'currency' => 'XAF',
            'financialTransactionId' => (string) random_int(100000000, 999999999),
            'status' => $this->option('status'),
        ]);
        $secret = $this->option('secret') ?: (string) config('services.momo.webhook_secret');
        $signature = 'sha256='.hash_hmac('sha256', $body, $secret);
        if ($this->option('bad-signature')) {
            $signature = 'sha256='.str_repeat('0', 64);
        }

        $response = Http::withHeaders(['X-Nkwen-Signature' => $signature, 'Accept' => 'application/json'])
            ->withBody($body, 'application/json')
            ->post(rtrim($this->option('url'), '/').'/api/payments/momo/callback');

        $this->line("Sent:      {$body}");
        $this->line("Signature: {$signature}");
        $this->line("Answer:    HTTP {$response->status()} {$response->body()}");

        return $response->successful() ? 0 : 1;
    }
}
