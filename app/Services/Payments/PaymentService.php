<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Events\PaymentSucceeded;
use App\Models\Payment;
use App\Models\User;
use App\Models\WebhookCall;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Takes online payments through the configured gateway. A payment is only
 * marked paid after the provider confirms it (verify call or signed webhook),
 * and confirming twice is harmless.
 */
class PaymentService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Creates a pending payment and returns the checkout URL for the customer.
     *
     * @throws GatewayException
     */
    public function start(Model $payable, User $customer, Money $amount, string $callbackUrl, ?string $exchangeRate = null): string
    {
        $payment = new Payment;
        $payment->forceFill([
            'payable_type' => $payable->getMorphClass(),
            'payable_id' => $payable->getKey(),
            'customer_id' => $customer->id,
            'provider' => $this->gateway->name(),
            'reference' => 'ygp_'.Str::lower((string) Str::ulid()),
            'status' => PaymentStatus::Pending,
            'amount' => $amount->getMinorAmount()->toInt(),
            'currency' => $amount->getCurrency()->getCurrencyCode(),
            'exchange_rate' => $exchangeRate,
        ])->save();

        return $this->gateway->initialize($payment, $customer, $callbackUrl);
    }

    /**
     * Checks the payment with the provider and records the result.
     *
     * @throws GatewayException
     */
    public function confirm(string $reference): ?Payment
    {
        $payment = Payment::where('reference', $reference)->first();

        if ($payment === null || $payment->status !== PaymentStatus::Pending) {
            return $payment;
        }

        $transaction = $this->gateway->verify($reference);

        $succeeded = DB::transaction(function () use ($payment, $transaction) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Pending) {
                return false;
            }

            $payment->provider_response = $transaction->raw;

            if ($transaction->status === 'success') {
                if ($transaction->amount !== $payment->amount || strtoupper($transaction->currency) !== $payment->currency) {
                    // Paid the wrong amount: keep it for a person to look at rather than accepting it.
                    Log::warning('Payment amount mismatch', ['reference' => $payment->reference, 'expected' => $payment->amount, 'got' => $transaction->amount]);
                    $payment->save();

                    return false;
                }

                $payment->forceFill([
                    'status' => PaymentStatus::Succeeded,
                    'provider_transaction_id' => $transaction->transactionId,
                    'channel' => $transaction->channel,
                    'paid_at' => $transaction->paidAt ?? now(),
                ])->save();

                return true;
            }

            if ($transaction->status === 'failed') {
                $payment->status = PaymentStatus::Failed;
            }

            $payment->save();

            return false;
        });

        $payment->refresh();

        if ($succeeded) {
            PaymentSucceeded::dispatch($payment);
        }

        return $payment;
    }

    /**
     * Refunds a paid payment in full.
     *
     * @throws GatewayException
     */
    public function refund(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::Succeeded) {
            throw new GatewayException('Only paid payments can be refunded.');
        }

        $status = $this->gateway->refund($payment);

        $payment->forceFill([
            'status' => PaymentStatus::Refunded,
            'refunded_amount' => $payment->amount,
            'refund_status' => $status,
            'refunded_at' => now(),
        ])->save();
    }

    /**
     * Handles a provider webhook. Returns false if the signature is invalid.
     */
    public function handleWebhook(string $body, ?string $signature): bool
    {
        if (! $this->gateway->verifyWebhookSignature($body, $signature)) {
            return false;
        }

        /** @var array{event?: string, data?: array<string, mixed>} $payload */
        $payload = json_decode($body, true) ?: [];
        $event = (string) ($payload['event'] ?? 'unknown');

        $hash = hash('sha256', $body);
        $call = WebhookCall::where('provider', $this->gateway->name())->where('payload_hash', $hash)->first();

        if ($call?->processed_at !== null) {
            return true;
        }

        $call ??= new WebhookCall;
        $call->forceFill([
            'provider' => $this->gateway->name(),
            'payload_hash' => $hash,
            'event' => $event,
            'payload' => $payload,
        ])->save();

        try {
            $data = $payload['data'] ?? [];

            match ($event) {
                'charge.success' => $this->confirm((string) ($data['reference'] ?? '')),
                'refund.processed', 'refund.failed', 'refund.pending' => $this->recordRefundStatus($data, Str::after($event, 'refund.')),
                default => null,
            };

            $call->forceFill(['processed_at' => now(), 'error' => null])->save();
        } catch (\Throwable $e) {
            $call->forceFill(['error' => $e->getMessage()])->save();
            report($e);
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function recordRefundStatus(array $data, string $status): void
    {
        /** @var array<string, mixed> $transaction */
        $transaction = is_array($data['transaction'] ?? null) ? $data['transaction'] : [];
        $reference = $transaction['reference'] ?? $data['transaction_reference'] ?? null;

        if (! is_string($reference)) {
            return;
        }

        Payment::where('reference', $reference)->update(['refund_status' => $status]);
    }
}
