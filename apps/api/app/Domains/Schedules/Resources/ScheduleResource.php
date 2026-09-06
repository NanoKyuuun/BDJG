<?php

namespace App\Domains\Schedules\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'project_id' => $this->project_id,
            'project_number' => $this->project?->project_number,
            'project_name' => $this->project?->name,
            'title' => $this->title,
            'schedule_type' => $this->schedule_type?->value ?? $this->schedule_type,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'location' => $this->location,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
