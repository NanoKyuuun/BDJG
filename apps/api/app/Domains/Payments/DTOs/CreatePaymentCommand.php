<?php

namespace App\Domains\Payments\DTOs;

readonly class CreatePaymentCommand
{
    public function __construct(
        public string $merchantOrderId,
        public int $amount,
        public string $productDetails,
        public string $customerEmail,
        public string $customerName,
        public ?string $customerPhone = null,
        public ?string $paymentMethod = null,
        public ?int $expiryMinutes = 1440,
    ) {}
}
