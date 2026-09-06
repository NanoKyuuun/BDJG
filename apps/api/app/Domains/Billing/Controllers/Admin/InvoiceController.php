<?php

namespace App\Domains\Billing\Controllers\Admin;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Billing\Models\InvoiceItem;
use App\Domains\Billing\Requests\IssueInvoiceRequest;
use App\Domains\Billing\Requests\StoreInvoiceRequest;
use App\Domains\Billing\Resources\AdminInvoiceResource;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Invoice::class);

        $invoices = Invoice::with(['client', 'quotation', 'items'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->input('client_id')))
            ->when($request->filled('invoice_type'), fn ($q) => $q->where('invoice_type', $request->input('invoice_type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('invoice_number', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('display_name', 'like', $term));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return AdminInvoiceResource::collection($invoices);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $itemsData = $data['items'];
        $user = $request->user();

        $invoice = DB::transaction(function () use ($data, $itemsData, $user) {
            $totalAmount = 0;
            $itemsToCreate = [];
            foreach ($itemsData as $idx => $item) {
                $lineTotal = (int) $item['quantity'] * (int) $item['unit_price'];
                $totalAmount += $lineTotal;
                $itemsToCreate[] = [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'sort_order' => $item['sort_order'] ?? $idx,
                ];
            }

            $invoice = Invoice::create([
                'client_id' => $data['client_id'],
                'quotation_id' => $data['quotation_id'] ?? null,
                'invoice_type' => $data['invoice_type'],
                'amount' => $totalAmount,
                'status' => InvoiceStatus::Draft,
                'due_at' => $data['due_at'] ?? now()->addDays(7)->toDateString(),
                'terms' => $data['terms'] ?? null,
                'notes_internal' => $data['notes_internal'] ?? null,
                'created_by' => $user?->id,
            ]);

            foreach ($itemsToCreate as $itemAttr) {
                $itemAttr['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemAttr);
            }

            return $invoice;
        });

        return (new AdminInvoiceResource($invoice->load(['client', 'quotation', 'items'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): AdminInvoiceResource
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['client', 'quotation', 'items']);

        return new AdminInvoiceResource($invoice);
    }

    public function issue(IssueInvoiceRequest $request, Invoice $invoice): AdminInvoiceResource|JsonResponse
    {
        Gate::authorize('update', $invoice);

        if ($invoice->status !== InvoiceStatus::Draft) {
            return response()->json([
                'message' => "Invoice with status {$invoice->status->value} cannot be issued.",
            ], 422);
        }

        $invoice->status = InvoiceStatus::Issued;
        $invoice->issued_at = now();
        if ($request->filled('due_at')) {
            $invoice->due_at = $request->validated('due_at');
        } elseif (! $invoice->due_at) {
            $invoice->due_at = now()->addDays(7);
        }

        if ($request->filled('terms')) {
            $invoice->terms = $request->validated('terms');
        }
        if ($request->filled('notes_internal')) {
            $invoice->notes_internal = $request->validated('notes_internal');
        }

        $invoice->save();

        if ($invoice->client && $invoice->client->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($invoice->client->email)
                    ->send(new \App\Mail\InvoiceIssuedMail($invoice));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to send invoice issued email: " . $e->getMessage());
            }
        }

        return new AdminInvoiceResource($invoice->load(['client', 'quotation', 'items']));
    }

    public function void(Invoice $invoice): AdminInvoiceResource|JsonResponse
    {
        Gate::authorize('update', $invoice);

        if ($invoice->status === InvoiceStatus::Paid) {
            return response()->json([
                'message' => 'Cannot void an invoice that is already marked as PAID.',
            ], 422);
        }

        $invoice->status = InvoiceStatus::Void;
        $invoice->voided_at = now();
        $invoice->save();

        return new AdminInvoiceResource($invoice->load(['client', 'quotation', 'items']));
    }

    public function generateDpInvoice(Quotation $quotation, Request $request): JsonResponse
    {
        Gate::authorize('update', $quotation);

        if ($quotation->status !== QuotationStatus::Accepted) {
            return response()->json([
                'message' => 'DP Invoice can only be generated from an ACCEPTED quotation.',
            ], 422);
        }

        $acceptedVersion = $quotation->acceptedVersion ?? $quotation->currentVersion;
        if (! $acceptedVersion) {
            return response()->json(['message' => 'No version found for quotation.'], 422);
        }

        // Check if DP invoice already exists
        $existingInvoice = Invoice::where('quotation_id', $quotation->id)
            ->where('invoice_type', InvoiceType::Dp)
            ->first();

        if ($existingInvoice) {
            return (new AdminInvoiceResource($existingInvoice->load(['client', 'quotation', 'items'])))
                ->response()
                ->setStatusCode(200);
        }

        $user = $request->user();

        $invoice = DB::transaction(function () use ($quotation, $acceptedVersion, $user) {
            $dpAmount = $acceptedVersion->dp_amount;

            $invoice = Invoice::create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'invoice_type' => InvoiceType::Dp,
                'amount' => $dpAmount,
                'status' => InvoiceStatus::Issued,
                'issued_at' => now(),
                'due_at' => now()->addDays(7)->toDateString(),
                'terms' => $acceptedVersion->terms,
                'notes_internal' => "Down payment (DP) invoice for Quotation #{$quotation->quotation_number}",
                'created_by' => $user?->id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => "Down Payment (DP) - {$acceptedVersion->project_name}",
                'description' => "DP {$acceptedVersion->dp_value}% for {$acceptedVersion->package_name_snapshot}",
                'quantity' => 1,
                'unit_price' => $dpAmount,
                'line_total' => $dpAmount,
                'sort_order' => 0,
            ]);

            return $invoice;
        });

        return (new AdminInvoiceResource($invoice->load(['client', 'quotation', 'items'])))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        Gate::authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
