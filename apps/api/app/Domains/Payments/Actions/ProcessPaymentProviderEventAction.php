<?php

namespace App\Domains\Payments\Actions;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentStatusHistory;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Projects\Actions\ActivateProjectAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessPaymentProviderEventAction
{
    /**
     * Process a provider payment event (callback, manual status check, or poll) monotonically and idempotently.
     */
    public function execute(
        string $merchantOrderId,
        PaymentStatus $targetStatus,
        ?int $reportedAmount = null,
        ?string $providerReference = null,
        ?string $paymentMethod = null,
        string $source = 'DUITKU_CALLBACK',
        array $rawPayload = []
    ): PaymentTransaction {
        return DB::transaction(function () use (
            $merchantOrderId,
            $targetStatus,
            $reportedAmount,
            $providerReference,
            $paymentMethod,
            $source,
            $rawPayload
        ) {
            /** @var PaymentTransaction $transaction */
            $transaction = PaymentTransaction::where('merchant_order_id', $merchantOrderId)
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Amount Verification: Prevent amount tampering if reported
            if ($reportedAmount !== null && (int) $reportedAmount !== (int) $transaction->amount) {
                Log::error("Payment amount mismatch for {$merchantOrderId}: Expected {$transaction->amount}, got {$reportedAmount}", [
                    'source' => $source,
                    'raw' => $rawPayload,
                ]);

                throw new \DomainException("Payment amount mismatch: Expected {$transaction->amount}, got {$reportedAmount}.");
            }

            // 2. Monotonic State Machine Guards:
            // Once PAID, never allow regression to PENDING, FAILED, or EXPIRED.
            if ($transaction->status === PaymentStatus::Paid) {
                if ($targetStatus !== PaymentStatus::Paid) {
                    Log::warning("Ignored non-PAID transition ({$targetStatus->value}) for already PAID transaction #{$transaction->merchant_order_id}");
                    return $transaction;
                }

                // If already PAID and target is PAID, it is an idempotent duplicate
                return $transaction;
            }

            // If status has not changed, return early
            if ($transaction->status === $targetStatus) {
                return $transaction;
            }

            // 3. Update Transaction State
            $oldStatus = $transaction->status;
            $transaction->status = $targetStatus;

            if ($providerReference) {
                $transaction->provider_reference = $providerReference;
            }
            if ($paymentMethod) {
                $transaction->payment_method = $paymentMethod;
            }
            if ($targetStatus === PaymentStatus::Paid) {
                $transaction->paid_at = $transaction->paid_at ?? now();
            }
            $transaction->raw_response = $rawPayload;
            $transaction->save();

            // 4. Record Status Transition History
            PaymentStatusHistory::create([
                'payment_transaction_id' => $transaction->id,
                'from_status' => $oldStatus,
                'to_status' => $targetStatus,
                'source' => $source,
                'payload' => $rawPayload,
            ]);

            // 5. Reconcile Linked Invoice
            if ($transaction->invoice_id) {
                /** @var Invoice|null $invoice */
                $invoice = Invoice::where('id', $transaction->invoice_id)->lockForUpdate()->first();

                if ($invoice) {
                    // Recompute total settled amount from all PAID transactions
                    $totalSettled = (int) PaymentTransaction::where('invoice_id', $invoice->id)
                        ->where('status', PaymentStatus::Paid)
                        ->sum('amount');

                    $invoice->paid_amount = $totalSettled;

                    if ($totalSettled >= $invoice->amount && $invoice->amount > 0) {
                        $invoice->status = InvoiceStatus::Paid;
                        $invoice->paid_at = $invoice->paid_at ?? now();
                    } elseif ($totalSettled > 0) {
                        $invoice->status = InvoiceStatus::PartiallyPaid;
                    }

                    $invoice->save();

                    // Update ServiceOrder status to Paid if linked
                    if ($invoice->status === InvoiceStatus::Paid) {
                        $serviceOrder = \App\Domains\Orders\Models\ServiceOrder::where('invoice_id', $invoice->id)->first();
                        if ($serviceOrder && $serviceOrder->status === \App\Domains\Orders\Enums\ServiceOrderStatus::AwaitingPayment) {
                            $serviceOrder->update(['status' => \App\Domains\Orders\Enums\ServiceOrderStatus::Paid]);
                        }
                    }

                    // 6. Exactly-Once Project Activation on DP Satisfaction
                    if (
                        $invoice->status === InvoiceStatus::Paid
                        && $invoice->invoice_type === InvoiceType::Dp
                        && $invoice->quotation_id
                    ) {
                        $invoice->loadMissing('quotation');
                        if ($invoice->quotation) {
                            (new ActivateProjectAction)->execute($invoice->quotation);
                        }
                    }

                    // 7. Dispatch Payment Receipt Email on Settlement (After DB Commit)
                    if ($targetStatus === PaymentStatus::Paid) {
                        $client = $invoice->client;
                        if ($client && $client->email) {
                            DB::afterCommit(function () use ($client, $transaction) {
                                try {
                                    Mail::to($client->email)->send(new \App\Mail\PaymentReceiptMail($transaction));
                                } catch (\Throwable $e) {
                                    Log::warning("Failed to send payment receipt email: " . $e->getMessage());
                                }
                            });
                        }
                    }
                }
            }

            return $transaction;
        });
    }
}
