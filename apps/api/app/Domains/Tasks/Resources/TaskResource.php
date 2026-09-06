<?php

namespace App\Domains\Tasks\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status?->value ?? $this->status,
            'allowed_next_statuses' => $this->status ? array_map(fn ($s) => $s->value, $this->status->allowedTransitions()) : [],
            'priority' => $this->priority?->value ?? $this->priority,
            'assigned_worker_id' => $this->assigned_worker_id,
            'assigned_worker_name' => $this->assignedWorker?->user?->name,
            'due_at' => $this->due_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
