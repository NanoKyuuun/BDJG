<?php

namespace App\Integrations\Duitku;

use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\CreatePaymentCommand;
use App\Domains\Payments\DTOs\PaymentGatewayResult;
use App\Domains\Payments\DTOs\PaymentGatewayStatus;
use App\Domains\Payments\DTOs\VerifiedPaymentEvent;
use App\Domains\Payments\Enums\PaymentStatus;
use Exception;
use Illuminate\Support\Facades\Log;

class DuitkuPaymentGateway implements PaymentGateway
{
    public function __construct(
        protected DuitkuClient $client,
        protected DuitkuConfig $config
    ) {}

    public function createTransaction(CreatePaymentCommand $command): PaymentGatewayResult
    {
        $signature = DuitkuSignature::generateRequestSignature(
            $this->config->merchantCode,
            $command->merchantOrderId,
            $command->amount,
            $this->config->apiKey
        );

        $params = [
            'paymentAmount' => $command->amount,
            'merchantOrderId' => $command->merchantOrderId,
            'productDetails' => $command->productDetails,
            'email' => $command->customerEmail,
            'customerVaName' => $command->customerName,
            'phoneNumber' => $command->customerPhone ?? '081234567890',
            'paymentMethod' => $command->paymentMethod ?? 'VC',
            'merchantCode' => $this->config->merchantCode,
            'callbackUrl' => $this->config->callbackUrl,
            'returnUrl' => $this->config->returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $command->expiryMinutes ?? 1440,
        ];

        try {
            $response = $this->client->createInvoice($params);
            $body = $response->json();

            if ($response->successful() && isset($body['statusCode']) && $body['statusCode'] === '00') {
                return new PaymentGatewayResult(
                    success: true,
                    merchantOrderId: $command->merchantOrderId,
                    paymentUrl: $body['paymentUrl'] ?? null,
                    reference: $body['reference'] ?? null,
                    vaNumber: $body['vaNumber'] ?? null,
                    qrString: $body['qrString'] ?? null,
                    statusCode: $body['statusCode'],
                    statusMessage: $body['statusMessage'] ?? 'Success',
                    rawResponse: $body,
                );
            }

            return new PaymentGatewayResult(
                success: false,
                merchantOrderId: $command->merchantOrderId,
                statusCode: $body['statusCode'] ?? 'ERR',
                statusMessage: $body['statusMessage'] ?? ($response->body() ?: 'Failed to create transaction'),
                rawResponse: $body ?? ['body' => $response->body()],
            );
        } catch (Exception $e) {
            Log::error('Duitku create transaction exception: '.$e->getMessage());

            return new PaymentGatewayResult(
                success: false,
                merchantOrderId: $command->merchantOrderId,
                statusCode: 'EXC',
                statusMessage: $e->getMessage(),
            );
        }
    }

    public function checkTransaction(string $merchantOrderId): PaymentGatewayStatus
    {
        try {
            $response = $this->client->checkTransaction($merchantOrderId);
            $body = $response->json();

            if ($response->successful() && isset($body['statusCode'])) {
                $status = DuitkuStatusMapper::mapResultCode($body['statusCode']);

                return new PaymentGatewayStatus(
                    merchantOrderId: $merchantOrderId,
                    status: $status,
                    amount: (int) ($body['amount'] ?? 0),
                    reference: $body['reference'] ?? null,
                    rawStatus: $body['statusCode'],
                    rawResponse: $body,
                );
            }

            return new PaymentGatewayStatus(
                merchantOrderId: $merchantOrderId,
                status: PaymentStatus::Pending,
                amount: 0,
                rawStatus: $body['statusCode'] ?? 'ERR',
                rawResponse: $body ?? ['body' => $response->body()],
            );
        } catch (Exception $e) {
            Log::error('Duitku check transaction exception: '.$e->getMessage());

            return new PaymentGatewayStatus(
                merchantOrderId: $merchantOrderId,
                status: PaymentStatus::Pending,
                amount: 0,
                rawStatus: 'EXC',
            );
        }
    }

    public function verifyCallback(array $payload, array $headers = []): VerifiedPaymentEvent
    {
        $merchantCode = (string) ($payload['merchantCode'] ?? '');
        $amount = (int) ($payload['amount'] ?? 0);
        $merchantOrderId = (string) ($payload['merchantOrderId'] ?? '');
        $signature = (string) ($payload['signature'] ?? '');
        $resultCode = (string) ($payload['resultCode'] ?? '');
        $reference = $payload['reference'] ?? null;
        $paymentMethod = $payload['paymentCode'] ?? ($payload['paymentMethod'] ?? null);

        if (empty($merchantOrderId) || empty($signature) || empty($merchantCode) || $amount <= 0) {
            return new VerifiedPaymentEvent(
                isValid: false,
                merchantOrderId: $merchantOrderId,
                status: PaymentStatus::Failed,
                amount: $amount,
                errorMessage: 'Missing or invalid required callback fields.',
                rawPayload: $payload,
            );
        }

        // Verify Merchant Code strictly matches config
        if ($merchantCode !== $this->config->merchantCode) {
            return new VerifiedPaymentEvent(
                isValid: false,
                merchantOrderId: $merchantOrderId,
                status: PaymentStatus::Failed,
                amount: $amount,
                errorMessage: 'Merchant code mismatch.',
                rawPayload: $payload,
            );
        }

        // Verify Signature
        $isSignatureValid = DuitkuSignature::verifyCallbackSignature(
            $signature,
            $this->config->merchantCode,
            $amount,
            $merchantOrderId,
            $this->config->apiKey
        );

        if (! $isSignatureValid) {
            return new VerifiedPaymentEvent(
                isValid: false,
                merchantOrderId: $merchantOrderId,
                status: PaymentStatus::Failed,
                amount: $amount,
                errorMessage: 'Invalid callback signature.',
                rawPayload: $payload,
            );
        }

        $canonicalStatus = DuitkuStatusMapper::mapResultCode($resultCode);

        return new VerifiedPaymentEvent(
            isValid: true,
            merchantOrderId: $merchantOrderId,
            status: $canonicalStatus,
            amount: $amount,
            providerReference: $reference,
            paymentMethod: $paymentMethod,
            rawPayload: $payload,
        );
    }
}
