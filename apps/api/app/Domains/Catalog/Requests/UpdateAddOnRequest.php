<?php

namespace App\Domains\Catalog\Requests;

use App\Domains\Catalog\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('add_ons.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'exists:services,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description_internal' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', new Enum(CatalogStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
