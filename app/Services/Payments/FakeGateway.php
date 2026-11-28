<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Local stand-in for Paystack. Checkout opens a pretend mobile money page
 * (/payments/test/{reference}) where you approve or decline; verify reports
 * what you chose (approved if nothing was chosen, e.g. in tests). Refuses to
 * run in production.
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
        return route('payments.test', $payment);
    }

    /** The test checkout's answer, remembered for verify(). */
    public function decide(Payment $payment, bool $approved, string $channel): void
    {
        Cache::put("fake-payment:{$payment->reference}", ['approved' => $approved, 'channel' => $channel], now()->addDay());
    }

    public function verify(string $reference): GatewayTransaction
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();
        /** @var array{approved: bool, channel: string}|null $decision */
        $decision = Cache::get("fake-payment:{$reference}");
        $approved = $decision['approved'] ?? true;

        return new GatewayTransaction(
            reference: $reference,
            status: $approved ? 'success' : 'failed',
            amount: $payment->amount,
            currency: $payment->currency,
            transactionId: 'fake_'.$payment->id,
            channel: $decision['channel'] ?? 'mobile_money',
            paidAt: $approved ? now() : null,
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
