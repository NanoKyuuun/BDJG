<?php

namespace App\Domains\Workers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('projects.update') || ($this->user()?->hasRole('OWNER') || $this->user()?->hasRole('ADMIN'));
    }

    public function rules(): array
    {
        return [
            'worker_id' => ['required', 'exists:worker_profiles,id'],
            'assignment_role' => ['required', 'string', 'max:100'],
            'fee_amount' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
