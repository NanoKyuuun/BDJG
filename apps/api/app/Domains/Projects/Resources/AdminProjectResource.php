<?php

namespace App\Domains\Projects\Resources;

use App\Domains\Clients\Resources\AdminClientResource;
use App\Domains\Schedules\Resources\ScheduleResource;
use App\Domains\Tasks\Resources\TaskResource;
use App\Domains\Workers\Resources\ProjectAssignmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_number' => $this->project_number,
            'client_id' => $this->client_id,
            'client' => new AdminClientResource($this->whenLoaded('client')),
            'quotation_id' => $this->quotation_id,
            'name' => $this->name,
            'service_name_snapshot' => $this->service_name_snapshot,
            'package_name_snapshot' => $this->package_name_snapshot,
            'contract_value' => $this->contract_value,
            'status' => $this->status?->value ?? $this->status,
            'allowed_next_statuses' => $this->status ? array_map(fn ($s) => $s->value, $this->status->allowedTransitions()) : [],
            'start_date' => $this->start_date?->toDateString(),
            'shoot_date' => $this->shoot_date?->toDateString(),
            'deadline' => $this->deadline?->toDateString(),
            'location' => $this->location,
            'brief' => $this->brief,
            'assigned_admin_id' => $this->assigned_admin_id,
            'activated_at' => $this->activated_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'notes_internal' => $this->notes_internal,
            'assignments' => ProjectAssignmentResource::collection($this->whenLoaded('assignments')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
