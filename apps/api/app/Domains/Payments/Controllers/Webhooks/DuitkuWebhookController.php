<?php

namespace App\Domains\Payments\Controllers\Webhooks;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentStatusHistory;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Projects\Actions\ActivateProjectAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DuitkuWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway): JsonResponse
    {
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

        // Idempotency: If already in target status, return 200 OK immediately
        if ($transaction->status === $event->status) {
            return response()->json(['message' => 'Idempotent: Status already updated'], 200);
        }

        DB::transaction(function () use ($transaction, $event, $payload) {
            $lockedTransaction = PaymentTransaction::where('id', $transaction->id)->lockForUpdate()->first();

            $oldStatus = $lockedTransaction->status;
            $lockedTransaction->status = $event->status;
            if ($event->providerReference) {
                $lockedTransaction->provider_reference = $event->providerReference;
            }
            if ($event->paymentMethod) {
                $lockedTransaction->payment_method = $event->paymentMethod;
            }
            if ($event->status === PaymentStatus::Paid) {
                $lockedTransaction->paid_at = now();
            }
            $lockedTransaction->raw_response = $payload;
            $lockedTransaction->save();

            PaymentStatusHistory::create([
                'payment_transaction_id' => $lockedTransaction->id,
                'from_status' => $oldStatus,
                'to_status' => $event->status,
                'source' => 'DUITKU_CALLBACK',
                'payload' => $payload,
            ]);

            // Update linked Invoice if payment is successful
            if ($event->status === PaymentStatus::Paid && $lockedTransaction->invoice) {
                $invoice = $lockedTransaction->invoice()->lockForUpdate()->first();
                $invoice->status = InvoiceStatus::Paid;
                $invoice->paid_amount = $lockedTransaction->amount;
                $invoice->paid_at = now();
                $invoice->save();

                // Phase 12 Activation rule: If DP invoice is paid, activate project
                if ($invoice->invoice_type === InvoiceType::Dp && $invoice->quotation) {
                    (new ActivateProjectAction)->execute($invoice->quotation);
                }

                $client = $lockedTransaction->invoice?->client;
                if ($client && $client->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($client->email)
                            ->send(new \App\Mail\PaymentReceiptMail($lockedTransaction));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send payment receipt email: " . $e->getMessage());
                    }
                }
            }
        });

        return response()->json(['message' => 'Callback processed successfully'], 200);
    }
}
