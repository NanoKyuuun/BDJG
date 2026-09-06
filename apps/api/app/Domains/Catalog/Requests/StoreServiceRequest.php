<?php

namespace App\Domains\Catalog\Requests;

use App\Domains\Catalog\Enums\CatalogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('services.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:services,slug'],
            'description_internal' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(CatalogStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
