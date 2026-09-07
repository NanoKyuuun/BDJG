<?php

namespace App\Domains\Payments\Controllers\Webhooks;

use App\Domains\Payments\Actions\ProcessPaymentProviderEventAction;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentGateway $gateway,
        ProcessPaymentProviderEventAction $processPayment
    ): JsonResponse {
        $payload = $request->all();
        $headers = $request->headers->all();

        $event = $gateway->verifyCallback($payload, $headers);

        if (! $event->isValid) {
            Log::warning('Duitku callback verification failed: '.$event->errorMessage, [
                'payload' => $payload,
            ]);

            return response()->json([
                'message' => $event->errorMessage,
            ], 400);
        }

        $transaction = PaymentTransaction::where('merchant_order_id', $event->merchantOrderId)->first();

        if (! $transaction) {
            Log::error("Duitku callback: Transaction not found for merchant_order_id {$event->merchantOrderId}");

            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status === $event->status) {
            return response()->json(['message' => 'Idempotent: Status already updated'], 200);
        }

        try {
            $processPayment->execute(
                merchantOrderId: $event->merchantOrderId,
                targetStatus: $event->status,
                reportedAmount: $event->amount,
                providerReference: $event->providerReference,
                paymentMethod: $event->paymentMethod,
                source: 'DUITKU_CALLBACK',
                rawPayload: $payload
            );

            return response()->json(['message' => 'Callback processed successfully'], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
