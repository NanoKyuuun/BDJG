<?php

namespace App\Domains\Commercial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version_number' => $this->version_number,
            'project_name' => $this->project_name,
            'service_name_snapshot' => $this->service_name_snapshot,
            'package_name_snapshot' => $this->package_name_snapshot,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'grand_total' => $this->grand_total,
            'dp_type' => $this->dp_type?->value ?? $this->dp_type,
            'dp_value' => (float) $this->dp_value,
            'dp_amount' => $this->dp_amount,
            'remaining_amount' => $this->remaining_amount,
            'terms' => $this->terms,
            'revision_notes' => $this->revision_notes,
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
