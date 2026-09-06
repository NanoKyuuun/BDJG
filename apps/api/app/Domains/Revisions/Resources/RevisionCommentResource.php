<?php

namespace App\Domains\Revisions\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevisionCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'revision_id' => $this->revision_id,
            'media_asset_id' => $this->media_asset_id,
            'user' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
                'email' => $this->author?->email,
            ],
            'timecode_seconds' => $this->timecode_seconds,
            'frame_number' => $this->frame_number,
            'coordinates' => $this->coordinates,
            'comment' => $this->comment,
            'status' => $this->status?->value ?? $this->status,
            'resolved_by' => $this->resolvedBy ? [
                'id' => $this->resolvedBy->id,
                'name' => $this->resolvedBy->name,
            ] : null,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
