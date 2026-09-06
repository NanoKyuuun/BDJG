<?php

namespace App\Domains\Projects\Requests;

use App\Domains\Projects\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'name' => ['required', 'string', 'max:255'],
            'service_name_snapshot' => ['nullable', 'string', 'max:255'],
            'package_name_snapshot' => ['nullable', 'string', 'max:255'],
            'contract_value' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', new Enum(ProjectStatus::class)],
            'start_date' => ['nullable', 'date'],
            'shoot_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'brief' => ['nullable', 'string'],
            'assigned_admin_id' => ['nullable', 'exists:users,id'],
            'notes_internal' => ['nullable', 'string'],
        ];
    }
}
