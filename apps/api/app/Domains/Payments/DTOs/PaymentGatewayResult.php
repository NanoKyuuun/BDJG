<?php

namespace App\Domains\Payments\DTOs;

readonly class PaymentGatewayResult
{
    public function __construct(
        public bool $success,
        public string $merchantOrderId,
        public ?string $paymentUrl = null,
        public ?string $reference = null,
        public ?string $vaNumber = null,
        public ?string $qrString = null,
        public ?string $statusCode = null,
        public ?string $statusMessage = null,
        public ?array $rawResponse = null,
    ) {}
}
