<?php

namespace App\Domains\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $this->user()?->can('update', $invoice) ?? false;
    }

    public function rules(): array
    {
        return [
            'due_at' => ['nullable', 'date'],
            'terms' => ['nullable', 'string'],
            'notes_internal' => ['nullable', 'string'],
        ];
    }
}
