<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\PaymentStatus;

readonly class PaymentGatewayStatus
{
    public function __construct(
        public string $merchantOrderId,
        public PaymentStatus $status,
        public int $amount,
        public ?string $reference = null,
        public ?string $rawStatus = null,
        public ?array $rawResponse = null,
    ) {}
}
