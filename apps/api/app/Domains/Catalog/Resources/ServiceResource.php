<?php

namespace App\Domains\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $category = match ($this->slug) {
            'happiness-package' => 'HAPPINESS',
            'photography-package' => 'PHOTOGRAPHY',
            'videography-package' => 'VIDEOGRAPHY',
            'custom-your-story' => 'CUSTOM',
            default => 'WEDDING',
        };

        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $category,
            'description' => $this->description_internal,
            'description_internal' => $this->description_internal,
            'status' => $this->status?->value ?? $this->status,
            'sort_order' => $this->sort_order,
            'packages' => PackageResource::collection($this->whenLoaded('packages')),
            'add_ons' => AddOnResource::collection($this->whenLoaded('addOns')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
