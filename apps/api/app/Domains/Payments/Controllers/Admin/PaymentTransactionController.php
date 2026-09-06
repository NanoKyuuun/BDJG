<?php

namespace App\Domains\Payments\Controllers\Admin;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentStatusHistory;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Resources\PaymentTransactionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentTransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', PaymentTransaction::class);

        $transactions = PaymentTransaction::with(['invoice.client'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('invoice_id'), fn ($q) => $q->where('invoice_id', $request->input('invoice_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('merchant_order_id', 'like', $term)
                        ->orWhere('provider_reference', 'like', $term)
                        ->orWhereHas('invoice', fn ($inv) => $inv->where('invoice_number', 'like', $term));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return PaymentTransactionResource::collection($transactions);
    }

    public function show(PaymentTransaction $transaction): PaymentTransactionResource
    {
        Gate::authorize('view', $transaction);

        return new PaymentTransactionResource($transaction->load(['invoice.client', 'statusHistories']));
    }

    public function checkStatus(PaymentTransaction $transaction, PaymentGateway $gateway): PaymentTransactionResource|JsonResponse
    {
        Gate::authorize('update', $transaction);

        $statusResult = $gateway->checkTransaction($transaction->merchant_order_id);

        if ($statusResult->status !== $transaction->status) {
            DB::transaction(function () use ($transaction, $statusResult) {
                $oldStatus = $transaction->status;
                $transaction->status = $statusResult->status;
                if ($statusResult->reference) {
                    $transaction->provider_reference = $statusResult->reference;
                }
                if ($statusResult->status === PaymentStatus::Paid) {
                    $transaction->paid_at = now();
                }
                $transaction->raw_response = $statusResult->rawResponse;
                $transaction->save();

                PaymentStatusHistory::create([
                    'payment_transaction_id' => $transaction->id,
                    'from_status' => $oldStatus,
                    'to_status' => $statusResult->status,
                    'source' => 'ADMIN_STATUS_CHECK',
                    'payload' => $statusResult->rawResponse,
                ]);

                if ($statusResult->status === PaymentStatus::Paid && $transaction->invoice) {
                    $transaction->invoice->update([
                        'status' => InvoiceStatus::Paid,
                        'paid_amount' => $transaction->amount,
                        'paid_at' => now(),
                    ]);
                }
            });
        }

        return new PaymentTransactionResource($transaction->fresh()->load(['invoice.client', 'statusHistories']));
    }
}
