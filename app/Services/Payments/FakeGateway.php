<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;

/**
 * Local stand-in for Paystack: "checkout" goes straight to the callback and
 * every payment succeeds. Refuses to run in production.
 */
class FakeGateway implements PaymentGateway
{
    public function __construct()
    {
        if (app()->isProduction()) {
            throw new GatewayException('The fake payment gateway cannot be used in production.');
        }
    }

    public function name(): string
    {
        return 'fake';
    }

    public function initialize(Payment $payment, User $customer, string $callbackUrl): string
    {
        return $callbackUrl.(str_contains($callbackUrl, '?') ? '&' : '?').http_build_query(['reference' => $payment->reference]);
    }

    public function verify(string $reference): GatewayTransaction
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();

        return new GatewayTransaction(
            reference: $reference,
            status: 'success',
            amount: $payment->amount,
            currency: $payment->currency,
            transactionId: 'fake_'.$payment->id,
            channel: 'mobile_money',
            paidAt: now(),
        );
    }

    public function refund(Payment $payment, ?int $amount = null): string
    {
        return 'processed';
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        return false;
    }
}
