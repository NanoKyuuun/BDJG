<?php

namespace App\Domains\Billing\Resources;

use App\Domains\Clients\Resources\AdminClientResource;
use App\Domains\Commercial\Resources\AdminQuotationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'invoice_number' => $this->invoice_number,
            'client_id' => $this->client_id,
            'quotation_id' => $this->quotation_id,
            'project_id' => $this->project_id,
            'invoice_type' => $this->invoice_type?->value ?? $this->invoice_type,
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'remaining_balance' => max(0, $this->amount - $this->paid_amount),
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'issued_at' => $this->issued_at?->toISOString(),
            'due_at' => $this->due_at?->format('Y-m-d'),
            'paid_at' => $this->paid_at?->toISOString(),
            'voided_at' => $this->voided_at?->toISOString(),
            'terms' => $this->terms,
            'notes_internal' => $this->notes_internal,
            'client' => new AdminClientResource($this->whenLoaded('client')),
            'quotation' => new AdminQuotationResource($this->whenLoaded('quotation')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
