<?php

namespace App\Domains\Media\Resources;

use App\Domains\Media\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class MediaAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storageService = app(MediaStorageService::class);
        $signedUrl = null;
        $thumbnailUrl = null;
        $previewUrl = null;

        // Generate signed URL if requested or safe
        if ($request->boolean('with_url', true)) {
            $signedUrl = $storageService->generateSignedViewUrl($this->resource);

            if (! empty($this->metadata['thumbnail_storage_key'])) {
                $thumbnailUrl = Storage::disk($this->disk)->temporaryUrl(
                    $this->metadata['thumbnail_storage_key'],
                    now()->addMinutes(120)
                );
            }

            if (! empty($this->metadata['preview_storage_key'])) {
                $previewUrl = Storage::disk($this->disk)->temporaryUrl(
                    $this->metadata['preview_storage_key'],
                    now()->addMinutes(120)
                );
            }
        }

        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'category' => $this->category?->value ?? $this->category,
            'visibility' => $this->visibility?->value ?? $this->visibility,
            'version_number' => $this->version_number,
            'processing_status' => $this->processing_status?->value ?? $this->processing_status,
            'metadata' => $this->metadata,
            'is_video' => $this->isVideo(),
            'is_image' => $this->isImage(),
            'url' => $signedUrl,
            'thumbnail_url' => $thumbnailUrl,
            'preview_url' => $previewUrl,
            'released_at' => $this->released_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
