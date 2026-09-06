<?php

namespace App\Domains\Projects\Resources;

use App\Domains\Schedules\Resources\ScheduleResource;
use App\Domains\Tasks\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_number' => $this->project_number,
            'name' => $this->name,
            'service_name' => $this->service_name_snapshot,
            'package_name' => $this->package_name_snapshot,
            'status' => $this->status?->value ?? $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'shoot_date' => $this->shoot_date?->toDateString(),
            'deadline' => $this->deadline?->toDateString(),
            'location' => $this->location,
            'brief' => $this->brief,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
