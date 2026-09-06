<?php

namespace App\Domains\Payments\Controllers\ClientPortal;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\CreatePaymentCommand;
use App\Domains\Payments\Enums\PaymentProvider;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Requests\InitiatePaymentRequest;
use App\Domains\Payments\Resources\PaymentTransactionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ClientPaymentController extends Controller
{
    public function pay(InitiatePaymentRequest $request, Invoice $invoice, PaymentGateway $gateway): PaymentTransactionResource|JsonResponse
    {
        Gate::authorize('view', $invoice);

        if (! $invoice->status->isPayable()) {
            return response()->json([
                'message' => "Invoice with status {$invoice->status->value} cannot be paid.",
            ], 422);
        }

        $user = $request->user();
        $client = $invoice->client;

        // Check for active pending transaction
        $existing = PaymentTransaction::where('invoice_id', $invoice->id)
            ->where('status', PaymentStatus::Pending)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existing && $existing->payment_url) {
            return new PaymentTransactionResource($existing);
        }

        $merchantOrderId = 'BDJG-'.$invoice->invoice_number.'-'.time();
        $expiryMinutes = (int) $request->input('expiry_minutes', 1440);

        $command = new CreatePaymentCommand(
            merchantOrderId: $merchantOrderId,
            amount: $invoice->amount - $invoice->paid_amount,
            productDetails: "Invoice payment {$invoice->invoice_number} - BDJG Studio",
            customerEmail: $client->billing_email ?: ($client->email ?: $user->email),
            customerName: $client->billing_name ?: ($client->display_name ?: $user->name),
            customerPhone: $client->billing_phone ?: $client->phone,
            paymentMethod: $request->input('payment_method'),
            expiryMinutes: $expiryMinutes,
        );

        $result = $gateway->createTransaction($command);

        $transaction = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'provider' => PaymentProvider::Duitku,
            'merchant_order_id' => $merchantOrderId,
            'provider_reference' => $result->reference,
            'amount' => $command->amount,
            'payment_method' => $request->input('payment_method'),
            'status' => $result->success ? PaymentStatus::Pending : PaymentStatus::Failed,
            'payment_url' => $result->paymentUrl,
            'va_number' => $result->vaNumber,
            'qr_string' => $result->qrString,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'raw_response' => $result->rawResponse,
        ]);

        if (! $result->success) {
            return response()->json([
                'message' => $result->statusMessage ?: 'Failed to create payment transaction with provider.',
                'transaction' => new PaymentTransactionResource($transaction),
            ], 502);
        }

        return (new PaymentTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    public function status(PaymentTransaction $transaction): PaymentTransactionResource|JsonResponse
    {
        Gate::authorize('view', $transaction->invoice);

        return new PaymentTransactionResource($transaction);
    }
}
