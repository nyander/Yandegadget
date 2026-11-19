<?php

namespace App\Services\Payments;

use Carbon\CarbonImmutable;

/**
 * A gateway's view of one transaction, normalised.
 */
final readonly class GatewayTransaction
{
    /**
     * @param  'success'|'failed'|'pending'  $status
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $reference,
        public string $status,
        public int $amount,
        public string $currency,
        public ?string $transactionId = null,
        public ?string $channel = null,
        public ?CarbonImmutable $paidAt = null,
        public array $raw = [],
    ) {}
}
