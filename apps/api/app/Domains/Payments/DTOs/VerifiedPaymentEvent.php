<?php

namespace App\Domains\Payments\DTOs;

use App\Domains\Payments\Enums\PaymentStatus;

readonly class VerifiedPaymentEvent
{
    public function __construct(
        public bool $isValid,
        public string $merchantOrderId,
        public PaymentStatus $status,
        public int $amount,
        public ?string $providerReference = null,
        public ?string $paymentMethod = null,
        public ?string $errorMessage = null,
        public ?array $rawPayload = null,
    ) {}
}
