<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(ProjectStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
