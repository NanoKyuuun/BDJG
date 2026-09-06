<?php

namespace App\Domains\Media\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'storage_key' => $this->storage_key,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'category' => $this->category?->value ?? $this->category,
            'visibility' => $this->visibility?->value ?? $this->visibility,
            'status' => $this->status?->value ?? $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'upload_url' => $this->additional['upload_url'] ?? null,
            'upload_method' => $this->additional['upload_method'] ?? 'PUT',
            'headers' => $this->additional['headers'] ?? [],
        ];
    }
}
