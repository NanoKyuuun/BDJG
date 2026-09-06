<?php

namespace App\Domains\Commercial\Requests;

use App\Domains\Catalog\Enums\DpType;
use App\Domains\Commercial\Enums\QuotationItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateQuotationRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        return $this->user()?->can('update', $quotation) ?? false;
    }

    public function rules(): array
    {
        return [
            'project_name' => ['required', 'string', 'max:255'],
            'service_name_snapshot' => ['nullable', 'string', 'max:255'],
            'package_name_snapshot' => ['nullable', 'string', 'max:255'],
            'dp_type' => ['nullable', new Enum(DpType::class)],
            'dp_value' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'terms' => ['nullable', 'string'],
            'revision_notes' => ['nullable', 'string'],
            'notes_internal' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', new Enum(QuotationItemType::class)],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
