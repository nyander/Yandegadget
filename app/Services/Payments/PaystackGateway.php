<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Paystack (https://paystack.com/docs/api): hosted checkout for card and
 * mobile money, amounts in the currency's subunit (pesewas for GHS).
 */
class PaystackGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl = 'https://api.paystack.co',
    ) {}

    public function name(): string
    {
        return 'paystack';
    }

    public function initialize(Payment $payment, User $customer, string $callbackUrl): string
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post('/transaction/initialize', [
            'email' => $customer->email,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl,
            'channels' => ['card', 'mobile_money'],
            'metadata' => [
                'payment_id' => $payment->id,
                'payable' => class_basename($payment->payable_type).':'.$payment->payable_id,
            ],
        ]));

        $url = $response->json('data.authorization_url');

        if (! is_string($url) || $url === '') {
            throw new GatewayException('Paystack did not return a checkout link.');
        }

        return $url;
    }

    public function verify(string $reference): GatewayTransaction
    {
        $response = $this->send(fn (PendingRequest $http) => $http->get('/transaction/verify/'.rawurlencode($reference)));

        /** @var array<string, mixed> $data */
        $data = $response->json('data') ?? [];
        $paidAt = $data['paid_at'] ?? null;

        return new GatewayTransaction(
            reference: (string) ($data['reference'] ?? $reference),
            status: match ($data['status'] ?? null) {
                'success' => 'success',
                'failed', 'abandoned', 'reversed' => 'failed',
                default => 'pending',
            },
            amount: (int) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? ''),
            transactionId: isset($data['id']) ? (string) $data['id'] : null,
            channel: isset($data['channel']) ? (string) $data['channel'] : null,
            paidAt: is_string($paidAt) ? CarbonImmutable::parse($paidAt) : null,
            raw: array_intersect_key($data, array_flip(['id', 'status', 'reference', 'amount', 'currency', 'channel', 'paid_at', 'gateway_response'])),
        );
    }

    public function refund(Payment $payment): string
    {
        $response = $this->send(fn (PendingRequest $http) => $http->post('/refund', [
            'transaction' => $payment->provider_transaction_id ?? $payment->reference,
        ]));

        return (string) ($response->json('data.status') ?? 'pending');
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $this->secretKey), $signature);
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     */
    private function send(callable $call): Response
    {
        try {
            $response = $call(Http::baseUrl($this->baseUrl)
                ->withToken($this->secretKey)
                ->acceptJson()
                ->timeout(15)
                ->retry(2, 500, throw: false));
        } catch (ConnectionException $e) {
            throw new GatewayException('Could not reach Paystack. Please try again.', previous: $e);
        }

        if ($response->failed() || $response->json('status') !== true) {
            throw new GatewayException((string) ($response->json('message') ?? 'Paystack refused the request.'));
        }

        return $response;
    }
}
