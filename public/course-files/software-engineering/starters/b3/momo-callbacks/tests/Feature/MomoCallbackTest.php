<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Exceptions\OrderNotAwaitingPayment;
use App\Services\PlaceOrder;
use App\Services\RequestPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Payments: Mobile Money Callbacks".
class MomoCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = app(PlaceOrder::class)->handle('CAV', 'Mbah Clarisse', 'mtn_momo', [
            ['product_id' => 6, 'quantity' => 2], ['product_id' => 15, 'quantity' => 1],
        ]);
    }

    private function requestPayment(): string
    {
        $payment = app(RequestPayment::class)->handle($this->order, '237670112233');
        $this->assertSame('pending', $payment->status);
        $this->assertSame($this->order->total_fcfa, $payment->amount_fcfa, 'Ask for exactly the order total.');

        return $payment->reference;
    }

    // Sends the callback the way the provider would: the raw JSON body, signed with the shared secret.
    private function sendCallback(array $body, ?string $signature = null)
    {
        $raw = json_encode($body);
        $signature ??= 'sha256='.hash_hmac('sha256', $raw, config('services.momo.webhook_secret'));

        return $this->call('POST', '/api/payments/momo/callback', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_NKWEN_SIGNATURE' => $signature,
        ], $raw);
    }

    private function body(string $reference, string $status = 'SUCCESSFUL', ?int $amount = null): array
    {
        return [
            'referenceId' => $reference, 'externalId' => $this->order->code,
            'amount' => (string) ($amount ?? $this->order->total_fcfa), 'currency' => 'XAF',
            'financialTransactionId' => '1234567890', 'status' => $status,
        ];
    }

    private function orderStatus(): string
    {
        return $this->order->fresh()->status;
    }

    public function test_asking_to_pay_creates_one_pending_payment(): void
    {
        $first = $this->requestPayment();
        $again = $this->requestPayment(); // the till retried after a network error
        $this->assertSame($first, $again, 'Asking twice should return the same pending payment.');
        $this->assertSame(1, Payment::count());
        $this->assertSame('awaiting_payment', $this->orderStatus(), 'Asking to pay does not make the order paid.');
    }

    public function test_a_correctly_signed_successful_callback_marks_the_order_paid(): void
    {
        $reference = $this->requestPayment();

        $this->sendCallback($this->body($reference))->assertOk();

        $this->assertSame('paid', $this->orderStatus());
        $this->assertSame('successful', Payment::where('reference', $reference)->value('status'));
    }

    public function test_a_missing_or_wrong_signature_is_401_and_changes_nothing(): void
    {
        $reference = $this->requestPayment();

        $this->sendCallback($this->body($reference), '')->assertStatus(401);
        $this->sendCallback($this->body($reference), 'sha256='.str_repeat('0', 64))->assertStatus(401);
        $wrongSecret = 'sha256='.hash_hmac('sha256', json_encode($this->body($reference)), 'a-guessed-secret');
        $this->sendCallback($this->body($reference), $wrongSecret)->assertStatus(401);

        $this->assertSame('awaiting_payment', $this->orderStatus(), 'A forged callback must never mark an order paid.');
    }

    public function test_a_changed_body_breaks_the_signature(): void
    {
        $reference = $this->requestPayment();
        $signedForOtherBody = 'sha256='.hash_hmac('sha256', json_encode($this->body($reference, 'FAILED')), config('services.momo.webhook_secret'));

        $this->sendCallback($this->body($reference, 'SUCCESSFUL'), $signedForOtherBody)->assertStatus(401);
        $this->assertSame('awaiting_payment', $this->orderStatus());
    }

    public function test_the_same_callback_twice_is_applied_once(): void
    {
        $reference = $this->requestPayment();

        $this->sendCallback($this->body($reference))->assertOk();
        $this->sendCallback($this->body($reference))->assertOk(); // the provider retried

        $this->assertSame('paid', $this->orderStatus());
        $this->assertSame(1, Payment::where('status', 'successful')->count());
    }

    public function test_a_late_failed_callback_cannot_undo_a_success(): void
    {
        $reference = $this->requestPayment();
        $this->sendCallback($this->body($reference))->assertOk();
        $this->sendCallback($this->body($reference, 'FAILED'))->assertOk();

        $this->assertSame('paid', $this->orderStatus());
    }

    public function test_a_failed_payment_leaves_the_order_waiting(): void
    {
        $reference = $this->requestPayment();
        $this->sendCallback($this->body($reference, 'FAILED'))->assertOk();

        $this->assertSame('awaiting_payment', $this->orderStatus());
        $this->assertSame('failed', Payment::where('reference', $reference)->value('status'));
    }

    public function test_the_wrong_amount_is_refused(): void
    {
        $reference = $this->requestPayment();
        $this->sendCallback($this->body($reference, 'SUCCESSFUL', 100))->assertStatus(422);
        $this->assertSame('awaiting_payment', $this->orderStatus());
    }

    public function test_an_unknown_reference_is_404(): void
    {
        $this->sendCallback($this->body('7b0c4a8e-0000-4000-8000-000000000000'))->assertNotFound();
    }

    public function test_a_cash_order_cannot_be_paid_by_mobile_money(): void
    {
        $cash = app(PlaceOrder::class)->handle('CAV', 'Che Divine', 'cash', [['product_id' => 17, 'quantity' => 1]]);
        $this->expectException(OrderNotAwaitingPayment::class);
        app(RequestPayment::class)->handle($cash, '237670112233');
    }
}
