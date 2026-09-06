<?php

namespace App\Domains\Catalog\Requests;

use App\Domains\Catalog\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('services.update') ?? false;
    }

    public function rules(): array
    {
        $service = $this->route('service');
        $serviceId = is_object($service) ? $service->id : $service;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($serviceId)],
            'description_internal' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(CatalogStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
