<?php

namespace App\Domains\Delivery\Resources;

use App\Domains\Media\Resources\MediaAssetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'status' => $this->status?->value ?? $this->status,
            'total_size_bytes' => $this->total_size_bytes,
            'file_count' => $this->file_count,
            'download_count' => $this->download_count,
            'expires_at' => $this->expires_at?->toISOString(),
            'is_expired' => $this->isExpired(),
            'released_at' => $this->released_at?->toISOString(),
            'released_by' => $this->releasedBy ? [
                'id' => $this->releasedBy->id,
                'name' => $this->releasedBy->name,
            ] : null,
            'notes' => $this->notes,
            'media_assets' => MediaAssetResource::collection($this->whenLoaded('mediaAssets')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
