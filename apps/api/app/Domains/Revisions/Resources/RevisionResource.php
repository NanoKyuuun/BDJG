<?php

namespace App\Domains\Revisions\Resources;

use App\Domains\Media\Resources\MediaAssetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'media_asset_id' => $this->media_asset_id,
            'media_asset' => $this->whenLoaded('mediaAsset', fn () => new MediaAssetResource($this->mediaAsset)),
            'round_number' => $this->round_number,
            'title' => $this->title,
            'status' => $this->status?->value ?? $this->status,
            'notes' => $this->notes,
            'requested_by' => [
                'id' => $this->requestedBy?->id,
                'name' => $this->requestedBy?->name,
                'email' => $this->requestedBy?->email,
            ],
            'resolved_at' => $this->resolved_at?->toISOString(),
            'resolved_by' => $this->resolvedBy ? [
                'id' => $this->resolvedBy->id,
                'name' => $this->resolvedBy->name,
            ] : null,
            'comments' => RevisionCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->comments()->count(),
            'open_comments_count' => $this->comments()->where('status', '!=', 'RESOLVED')->count(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
