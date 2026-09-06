<?php

namespace App\Domains\Tasks\Requests;

use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.update') || ($this->user()?->hasRole('OWNER') || $this->user()?->hasRole('ADMIN'));
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'priority' => ['nullable', new Enum(TaskPriority::class)],
            'assigned_worker_id' => ['nullable', 'exists:worker_profiles,id'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
