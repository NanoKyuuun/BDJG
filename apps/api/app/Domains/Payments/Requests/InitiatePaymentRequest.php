<?php

namespace App\Domains\Payments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $this->user()?->can('view', $invoice) ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', 'string', 'max:50'],
            'expiry_minutes' => ['nullable', 'integer', 'min:15', 'max:43200'],
        ];
    }
}
