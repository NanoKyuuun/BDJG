<?php

namespace App\Domains\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddOnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'service_id' => $this->service_id,
            'name' => $this->name,
            'description_internal' => $this->description_internal,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status?->value ?? $this->status,
            'sort_order' => $this->sort_order,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
