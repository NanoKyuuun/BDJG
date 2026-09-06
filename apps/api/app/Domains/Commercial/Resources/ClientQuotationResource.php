<?php

namespace App\Domains\Commercial\Resources;

use App\Domains\Clients\Resources\ClientProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientQuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'quotation_number' => $this->quotation_number,
            'status' => $this->status?->value ?? $this->status,
            'sent_at' => $this->sent_at?->toISOString(),
            'viewed_at' => $this->viewed_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'declined_at' => $this->declined_at?->toISOString(),
            'decline_reason' => $this->decline_reason,
            'revision_request_notes' => $this->revision_request_notes,
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'client' => new ClientProfileResource($this->whenLoaded('client')),
            'current_version' => new QuotationVersionResource($this->whenLoaded('currentVersion')),
            'accepted_version' => new QuotationVersionResource($this->whenLoaded('acceptedVersion')),
            'versions' => QuotationVersionResource::collection($this->whenLoaded('versions')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
