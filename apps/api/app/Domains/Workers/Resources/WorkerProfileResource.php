<?php

namespace App\Domains\Workers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'profession' => $this->profession?->value ?? $this->profession,
            'skills' => $this->skills ?? [],
            'phone' => $this->phone,
            'status' => $this->status?->value ?? $this->status,
            'notes_internal' => $this->notes_internal,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
