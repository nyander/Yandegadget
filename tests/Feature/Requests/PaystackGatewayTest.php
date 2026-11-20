<?php

namespace Tests\Feature\Requests;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ProductRequest;
use App\Models\User;
use App\Services\Payments\GatewayException;
use App\Services\Payments\PaystackGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackGatewayTest extends TestCase
{
    use RefreshDatabase;

    private PaystackGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new PaystackGateway('sk_test_secret', 'https://api.paystack.co');
    }

    private function payment(): Payment
    {
        $payment = new Payment;
        $payment->forceFill([
            'payable_type' => (new ProductRequest)->getMorphClass(),
            'payable_id' => 1,
            'customer_id' => User::factory()->create()->id,
            'provider' => 'paystack',
            'reference' => 'ygp_test',
            'status' => PaymentStatus::Pending,
            'amount' => 120000,
            'currency' => 'GHS',
        ])->save();

        return $payment;
    }

    public function test_initialize_sends_amount_in_pesewas_and_returns_the_checkout_url(): void
    {
        Http::fake(['api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/abc', 'reference' => 'ygp_test'],
        ])]);
        $payment = $this->payment();

        $url = $this->gateway->initialize($payment, $payment->customer, 'https://shop.test/payments/callback');

        $this->assertSame('https://checkout.paystack.com/abc', $url);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer sk_test_secret')
            && $request['amount'] === 120000
            && $request['currency'] === 'GHS'
            && $request['reference'] === 'ygp_test'
            && $request['email'] === $payment->customer->email
            && $request['channels'] === ['card', 'mobile_money']);
    }

    public function test_verify_maps_paystack_statuses(): void
    {
        Http::fake(['api.paystack.co/transaction/verify/*' => Http::sequence()
            ->push(['status' => true, 'data' => ['status' => 'success', 'reference' => 'r', 'amount' => 500, 'currency' => 'GHS', 'id' => 99, 'channel' => 'mobile_money', 'paid_at' => '2026-11-19T10:00:00.000Z']])
            ->push(['status' => true, 'data' => ['status' => 'abandoned', 'reference' => 'r', 'amount' => 500, 'currency' => 'GHS']])
            ->push(['status' => true, 'data' => ['status' => 'ongoing', 'reference' => 'r', 'amount' => 500, 'currency' => 'GHS']]),
        ]);

        $success = $this->gateway->verify('r');
        $this->assertSame('success', $success->status);
        $this->assertSame(500, $success->amount);
        $this->assertSame('99', $success->transactionId);
        $this->assertSame('mobile_money', $success->channel);

        $this->assertSame('failed', $this->gateway->verify('r')->status);
        $this->assertSame('pending', $this->gateway->verify('r')->status);
    }

    public function test_errors_from_paystack_become_gateway_exceptions(): void
    {
        Http::fake(['*' => Http::response(['status' => false, 'message' => 'Invalid key'], 401)]);

        $this->expectException(GatewayException::class);
        $this->expectExceptionMessage('Invalid key');

        $this->gateway->verify('r');
    }

    public function test_webhook_signatures_are_checked_with_hmac_sha512(): void
    {
        $body = '{"event":"charge.success"}';

        $this->assertTrue($this->gateway->verifyWebhookSignature($body, hash_hmac('sha512', $body, 'sk_test_secret')));
        $this->assertFalse($this->gateway->verifyWebhookSignature($body, hash_hmac('sha512', $body, 'wrong')));
        $this->assertFalse($this->gateway->verifyWebhookSignature($body, null));
    }

    public function test_refund_uses_the_transaction_id(): void
    {
        Http::fake(['api.paystack.co/refund' => Http::response(['status' => true, 'data' => ['status' => 'pending']])]);
        $payment = $this->payment();
        $payment->forceFill(['provider_transaction_id' => '12345'])->save();

        $this->assertSame('pending', $this->gateway->refund($payment));
        Http::assertSent(fn (Request $request) => $request['transaction'] === '12345' && ! isset($request['amount']));
    }

    public function test_a_partial_refund_sends_the_amount_in_pesewas(): void
    {
        Http::fake(['api.paystack.co/refund' => Http::response(['status' => true, 'data' => ['status' => 'pending']])]);
        $payment = $this->payment();

        $this->gateway->refund($payment, 9000);
        Http::assertSent(fn (Request $request) => $request['amount'] === 9000);
    }
}
