<?php

namespace App\Domains\Billing\Requests;

use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Invoice::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'invoice_type' => ['required', new Enum(InvoiceType::class)],
            'due_at' => ['nullable', 'date'],
            'terms' => ['nullable', 'string'],
            'notes_internal' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
