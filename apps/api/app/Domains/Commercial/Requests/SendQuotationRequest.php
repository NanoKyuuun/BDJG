<?php

namespace App\Domains\Commercial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        return $this->user()?->can('update', $quotation) ?? false;
    }

    public function rules(): array
    {
        return [
            'expires_at' => ['nullable', 'date', 'after:today'],
            'notes_internal' => ['nullable', 'string'],
        ];
    }
}
