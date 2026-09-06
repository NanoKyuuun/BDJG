<?php

namespace App\Domains\Commercial\Requests;

use App\Domains\Catalog\Enums\DpType;
use App\Domains\Commercial\Enums\QuotationItemType;
use App\Domains\Commercial\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Quotation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'inquiry_id' => ['nullable', 'exists:inquiries,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'service_name_snapshot' => ['nullable', 'string', 'max:255'],
            'package_name_snapshot' => ['nullable', 'string', 'max:255'],
            'dp_type' => ['nullable', new Enum(DpType::class)],
            'dp_value' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'terms' => ['nullable', 'string'],
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
