<?php

namespace App\Domains\Orders\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'message' => $this->message,
            'attachments' => $this->attachments,
            'is_internal_note' => $this->is_internal_note,
            'sender' => $this->sender ? [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'role' => $this->sender->roles->pluck('name')->first() ?? 'USER',
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'read_at' => $this->read_at?->toISOString(),
        ];
    }
}
