<?php

namespace App\Domains\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $deliverables = $this->description_internal
            ? array_values(array_filter(array_map('trim', explode(',', $this->description_internal))))
            : [];

        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'service_id' => $this->service_id,
            'name' => $this->name,
            'price' => $this->base_price,
            'base_price' => $this->base_price,
            'description' => $this->description_internal,
            'description_internal' => $this->description_internal,
            'deliverables' => $deliverables,
            'currency' => $this->currency,
            'default_dp_type' => $this->default_dp_type?->value ?? $this->default_dp_type,
            'default_dp_value' => (float) $this->default_dp_value,
            'status' => $this->status?->value ?? $this->status,
            'sort_order' => $this->sort_order,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
