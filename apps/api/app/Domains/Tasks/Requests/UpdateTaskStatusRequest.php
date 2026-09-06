<?php

namespace App\Domains\Tasks\Requests;

use App\Domains\Tasks\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policed by TaskPolicy
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(TaskStatus::class)],
        ];
    }
}
