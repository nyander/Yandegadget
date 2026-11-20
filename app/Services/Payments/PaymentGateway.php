<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;

interface PaymentGateway
{
    public function name(): string;

    /**
     * Starts a hosted checkout and returns the URL to send the customer to.
     *
     * @throws GatewayException
     */
    public function initialize(Payment $payment, User $customer, string $callbackUrl): string;

    /**
     * Asks the provider for the transaction's real status. Never trust the redirect alone.
     *
     * @throws GatewayException
     */
    public function verify(string $reference): GatewayTransaction;

    /**
     * Refunds the payment in full and returns the provider's refund status.
     *
     * @throws GatewayException
     */
    /**
     * Refunds all of a payment, or part of it (in minor units). Returns the provider's status.
     */
    public function refund(Payment $payment, ?int $amount = null): string;

    public function verifyWebhookSignature(string $payload, ?string $signature): bool;
}
