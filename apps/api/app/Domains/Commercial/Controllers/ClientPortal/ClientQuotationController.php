<?php

namespace App\Domains\Commercial\Controllers\ClientPortal;

use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Requests\ClientRespondQuotationRequest;
use App\Domains\Commercial\Resources\ClientQuotationResource;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientQuotationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $clientIds = $user->clients()->pluck('clients.id');

        $quotations = Quotation::whereIn('client_id', $clientIds)
            ->whereNotIn('status', [QuotationStatus::Draft]) // Client cannot see drafts
            ->with(['client', 'currentVersion.items', 'versions.items'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ClientQuotationResource::collection($quotations);
    }

    public function show(Quotation $quotation): ClientQuotationResource|JsonResponse
    {
        Gate::authorize('view', $quotation);

        if ($quotation->status === QuotationStatus::Draft) {
            return response()->json(['message' => 'Quotation is not published.'], 404);
        }

        // Mark as VIEWED on first access
        if ($quotation->status === QuotationStatus::Sent) {
            $quotation->status = QuotationStatus::Viewed;
            if (! $quotation->viewed_at) {
                $quotation->viewed_at = now();
            }
            $quotation->save();
        }

        $quotation->load(['client', 'currentVersion.items', 'acceptedVersion.items', 'versions.items']);

        return new ClientQuotationResource($quotation);
    }

    public function accept(ClientRespondQuotationRequest $request, Quotation $quotation): ClientQuotationResource|JsonResponse
    {
        Gate::authorize('view', $quotation);

        if (! $quotation->status->canClientRespond()) {
            return response()->json([
                'message' => "Cannot accept quotation with status {$quotation->status->value}.",
            ], 422);
        }

        $quotation->status = QuotationStatus::Accepted;
        $quotation->accepted_at = now();
        $quotation->accepted_version_id = $quotation->current_version_id;
        $quotation->save();

        if ($quotation->inquiry) {
            $quotation->inquiry->update(['status' => InquiryStatus::Won]);
        }

        $quotation->load(['client', 'currentVersion.items', 'acceptedVersion.items', 'versions.items']);

        return new ClientQuotationResource($quotation);
    }

    public function requestRevision(ClientRespondQuotationRequest $request, Quotation $quotation): ClientQuotationResource|JsonResponse
    {
        Gate::authorize('view', $quotation);

        if (! $quotation->status->canClientRespond()) {
            return response()->json([
                'message' => "Cannot request revision for quotation with status {$quotation->status->value}.",
            ], 422);
        }

        $notes = $request->validated('notes') ?? 'Client requested revision.';
        $quotation->status = QuotationStatus::RevisionRequested;
        $quotation->revision_request_notes = $notes;
        $quotation->save();

        $quotation->load(['client', 'currentVersion.items', 'versions.items']);

        return new ClientQuotationResource($quotation);
    }

    public function decline(ClientRespondQuotationRequest $request, Quotation $quotation): ClientQuotationResource|JsonResponse
    {
        Gate::authorize('view', $quotation);

        if (! $quotation->status->canClientRespond()) {
            return response()->json([
                'message' => "Cannot decline quotation with status {$quotation->status->value}.",
            ], 422);
        }

        $reason = $request->validated('reason') ?? 'Client declined quotation.';
        $quotation->status = QuotationStatus::Declined;
        $quotation->declined_at = now();
        $quotation->decline_reason = $reason;
        $quotation->save();

        if ($quotation->inquiry) {
            $quotation->inquiry->update([
                'status' => InquiryStatus::Lost,
                'lost_reason' => $reason,
            ]);
        }

        $quotation->load(['client', 'currentVersion.items', 'versions.items']);

        return new ClientQuotationResource($quotation);
    }
}
