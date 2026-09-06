<?php

namespace App\Domains\Workers\Requests;

use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreWorkerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('workers.manage') || ($this->user()?->hasRole('OWNER') || $this->user()?->hasRole('ADMIN'));
    }

    public function rules(): array
    {
        $worker = $this->route('worker');
        $workerId = is_object($worker) ? $worker->id : $worker;

        return [
            'user_id' => ['required', 'exists:users,id', Rule::unique('worker_profiles', 'user_id')->ignore($workerId)],
            'profession' => ['required', new Enum(WorkerProfession::class)],
            'skills' => ['nullable', 'array'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', new Enum(WorkerStatus::class)],
            'notes_internal' => ['nullable', 'string'],
        ];
    }
}
