<?php

namespace App\Domains\Payments\Contracts;

use App\Domains\Payments\DTOs\CreatePaymentCommand;
use App\Domains\Payments\DTOs\PaymentGatewayResult;
use App\Domains\Payments\DTOs\PaymentGatewayStatus;
use App\Domains\Payments\DTOs\VerifiedPaymentEvent;

interface PaymentGateway
{
    public function createTransaction(CreatePaymentCommand $command): PaymentGatewayResult;

    public function checkTransaction(string $merchantOrderId): PaymentGatewayStatus;

    public function verifyCallback(array $payload, array $headers = []): VerifiedPaymentEvent;
}
