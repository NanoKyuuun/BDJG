<?php

namespace App\Domains\Billing\Resources;

use App\Domains\Clients\Resources\ClientProfileResource;
use App\Domains\Commercial\Resources\ClientQuotationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type?->value ?? $this->invoice_type,
            'amount' => $this->amount,
            'paid_amount' => $this->paid_amount,
            'remaining_balance' => max(0, $this->amount - $this->paid_amount),
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'issued_at' => $this->issued_at?->toISOString(),
            'due_at' => $this->due_at?->format('Y-m-d'),
            'paid_at' => $this->paid_at?->toISOString(),
            'terms' => $this->terms,
            'client' => new ClientProfileResource($this->whenLoaded('client')),
            'quotation' => new ClientQuotationResource($this->whenLoaded('quotation')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
