<?php

namespace App\Domains\Catalog\Requests;

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Enums\DpType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('packages.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'description_internal' => ['nullable', 'string'],
            'base_price' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'default_dp_type' => ['nullable', new Enum(DpType::class)],
            'default_dp_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', new Enum(CatalogStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
