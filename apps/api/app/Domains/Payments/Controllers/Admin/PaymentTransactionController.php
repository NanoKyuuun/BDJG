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

    public function checkStatus(
        PaymentTransaction $transaction,
        PaymentGateway $gateway,
        \App\Domains\Payments\Actions\ProcessPaymentProviderEventAction $processPayment
    ): PaymentTransactionResource|JsonResponse {
        Gate::authorize('update', $transaction);

        $statusResult = $gateway->checkTransaction($transaction->merchant_order_id);

        try {
            $updated = $processPayment->execute(
                merchantOrderId: $transaction->merchant_order_id,
                targetStatus: $statusResult->status,
                reportedAmount: $statusResult->amount > 0 ? $statusResult->amount : null,
                providerReference: $statusResult->reference,
                paymentMethod: null,
                source: 'ADMIN_STATUS_CHECK',
                rawPayload: $statusResult->rawResponse ?? []
            );

            return new PaymentTransactionResource($updated->load(['invoice.client', 'statusHistories']));
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
