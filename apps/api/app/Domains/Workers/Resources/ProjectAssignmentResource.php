<?php

namespace App\Domains\Workers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'worker_id' => $this->worker_id,
            'worker_name' => $this->worker?->user?->name,
            'profession' => $this->worker?->profession?->value ?? $this->worker?->profession,
            'assignment_role' => $this->assignment_role,
            'fee_amount' => $this->fee_amount,
            'is_active' => $this->is_active,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'removed_at' => $this->removed_at?->toISOString(),
        ];
    }
}
