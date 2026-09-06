<?php

namespace App\Domains\Billing\Controllers\ClientPortal;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Billing\Resources\ClientInvoiceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientInvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $clientIds = $user->clients()->pluck('clients.id');

        $invoices = Invoice::whereIn('client_id', $clientIds)
            ->whereNotIn('status', [InvoiceStatus::Draft]) // Client cannot see drafts
            ->with(['client', 'quotation', 'items'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ClientInvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice): ClientInvoiceResource|JsonResponse
    {
        Gate::authorize('view', $invoice);

        if ($invoice->status === InvoiceStatus::Draft) {
            return response()->json(['message' => 'Invoice not found or unpublished.'], 404);
        }

        $invoice->load(['client', 'quotation', 'items']);

        return new ClientInvoiceResource($invoice);
    }
}
