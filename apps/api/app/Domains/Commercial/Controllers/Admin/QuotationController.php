<?php

namespace App\Domains\Commercial\Controllers\Admin;

use App\Domains\Catalog\Enums\DpType;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Models\QuotationItem;
use App\Domains\Commercial\Models\QuotationVersion;
use App\Domains\Commercial\Requests\CreateQuotationRevisionRequest;
use App\Domains\Commercial\Requests\SendQuotationRequest;
use App\Domains\Commercial\Requests\StoreQuotationRequest;
use App\Domains\Commercial\Resources\AdminQuotationResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuotationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Quotation::class);

        $quotations = Quotation::with(['client', 'currentVersion.items', 'inquiry'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->input('client_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('quotation_number', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('display_name', 'like', $term))
                        ->orWhereHas('currentVersion', fn ($v) => $v->where('project_name', 'like', $term));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return AdminQuotationResource::collection($quotations);
    }

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $itemsData = $data['items'];
        $user = $request->user();

        $quotation = DB::transaction(function () use ($data, $itemsData, $user) {
            $quotation = Quotation::create([
                'client_id' => $data['client_id'],
                'inquiry_id' => $data['inquiry_id'] ?? null,
                'status' => QuotationStatus::Draft,
                'expires_at' => $data['expires_at'] ?? now()->addDays(14)->toDateString(),
                'created_by' => $user?->id,
                'notes_internal' => $data['notes_internal'] ?? null,
            ]);

            // Calculate version totals
            $subtotal = 0;
            $itemsToCreate = [];
            foreach ($itemsData as $idx => $item) {
                $lineTotal = (int) $item['quantity'] * (int) $item['unit_price'];
                $subtotal += $lineTotal;
                $itemsToCreate[] = [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'sort_order' => $item['sort_order'] ?? $idx,
                ];
            }

            $discount = (int) ($data['discount'] ?? 0);
            $tax = (int) ($data['tax'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount + $tax);

            $dpType = $data['dp_type'] ?? DpType::Percentage->value;
            $dpValue = (float) ($data['dp_value'] ?? 50.00);

            if ($dpType === DpType::Percentage->value) {
                $dpAmount = (int) round(($grandTotal * $dpValue) / 100);
            } else {
                $dpAmount = (int) min($grandTotal, $dpValue);
            }
            $remainingAmount = max(0, $grandTotal - $dpAmount);

            $version = QuotationVersion::create([
                'quotation_id' => $quotation->id,
                'version_number' => 1,
                'project_name' => $data['project_name'],
                'service_name_snapshot' => $data['service_name_snapshot'] ?? null,
                'package_name_snapshot' => $data['package_name_snapshot'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'dp_type' => $dpType,
                'dp_value' => $dpValue,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'terms' => $data['terms'] ?? 'Pembayaran DP 50% saat penandatanganan penawaran, sisa 50% sebelum rilis final media.',
                'created_by' => $user?->id,
            ]);

            foreach ($itemsToCreate as $itemAttr) {
                $itemAttr['quotation_version_id'] = $version->id;
                QuotationItem::create($itemAttr);
            }

            $quotation->current_version_id = $version->id;
            $quotation->save();

            return $quotation;
        });

        return (new AdminQuotationResource($quotation->load(['client', 'currentVersion.items', 'versions.items', 'inquiry'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Quotation $quotation): AdminQuotationResource
    {
        Gate::authorize('view', $quotation);

        $quotation->load(['client', 'currentVersion.items', 'acceptedVersion.items', 'versions.items', 'inquiry']);

        return new AdminQuotationResource($quotation);
    }

    public function createRevision(CreateQuotationRevisionRequest $request, Quotation $quotation): JsonResponse
    {
        if ($quotation->status->isFinal()) {
            return response()->json([
                'message' => "Cannot create revision for final quotation with status {$quotation->status->value}.",
            ], 422);
        }

        $data = $request->validated();
        $itemsData = $data['items'];
        $user = $request->user();

        $quotation = DB::transaction(function () use ($quotation, $data, $itemsData, $user) {
            $nextVersionNumber = $quotation->versions()->max('version_number') + 1;

            $subtotal = 0;
            $itemsToCreate = [];
            foreach ($itemsData as $idx => $item) {
                $lineTotal = (int) $item['quantity'] * (int) $item['unit_price'];
                $subtotal += $lineTotal;
                $itemsToCreate[] = [
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'sort_order' => $item['sort_order'] ?? $idx,
                ];
            }

            $discount = (int) ($data['discount'] ?? 0);
            $tax = (int) ($data['tax'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount + $tax);

            $dpType = $data['dp_type'] ?? DpType::Percentage->value;
            $dpValue = (float) ($data['dp_value'] ?? 50.00);

            if ($dpType === DpType::Percentage->value) {
                $dpAmount = (int) round(($grandTotal * $dpValue) / 100);
            } else {
                $dpAmount = (int) min($grandTotal, $dpValue);
            }
            $remainingAmount = max(0, $grandTotal - $dpAmount);

            $version = QuotationVersion::create([
                'quotation_id' => $quotation->id,
                'version_number' => $nextVersionNumber,
                'project_name' => $data['project_name'],
                'service_name_snapshot' => $data['service_name_snapshot'] ?? null,
                'package_name_snapshot' => $data['package_name_snapshot'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'dp_type' => $dpType,
                'dp_value' => $dpValue,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'terms' => $data['terms'] ?? $quotation->currentVersion?->terms,
                'revision_notes' => $data['revision_notes'] ?? null,
                'created_by' => $user?->id,
            ]);

            foreach ($itemsToCreate as $itemAttr) {
                $itemAttr['quotation_version_id'] = $version->id;
                QuotationItem::create($itemAttr);
            }

            $quotation->current_version_id = $version->id;
            $quotation->status = QuotationStatus::Draft;
            if (! empty($data['notes_internal'])) {
                $quotation->notes_internal = $data['notes_internal'];
            }
            if (! empty($data['expires_at'])) {
                $quotation->expires_at = $data['expires_at'];
            }
            $quotation->save();

            return $quotation;
        });

        return (new AdminQuotationResource($quotation->load(['client', 'currentVersion.items', 'versions.items', 'inquiry'])))
            ->response()
            ->setStatusCode(201);
    }

    public function send(SendQuotationRequest $request, Quotation $quotation): AdminQuotationResource|JsonResponse
    {
        if (! $quotation->status->canBeSent()) {
            return response()->json([
                'message' => "Quotation with status {$quotation->status->value} cannot be sent.",
            ], 422);
        }

        $quotation->status = QuotationStatus::Sent;
        $quotation->sent_at = now();

        if ($request->filled('expires_at')) {
            $quotation->expires_at = $request->validated('expires_at');
        } elseif (! $quotation->expires_at) {
            $quotation->expires_at = now()->addDays(14);
        }

        if ($request->filled('notes_internal')) {
            $quotation->notes_internal = $request->validated('notes_internal');
        }

        $quotation->save();

        if ($quotation->client && $quotation->client->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($quotation->client->email)
                    ->send(new \App\Mail\QuotationSentMail($quotation));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to send quotation email: " . $e->getMessage());
            }
        }

        return new AdminQuotationResource($quotation->load(['client', 'currentVersion.items', 'versions.items', 'inquiry']));
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        Gate::authorize('delete', $quotation);

        $quotation->delete();

        return response()->json(['message' => 'Quotation deleted successfully']);
    }
}
