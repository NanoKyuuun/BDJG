<?php

namespace App\Domains\Commercial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRespondQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quotation = $this->route('quotation');

        return $this->user()?->can('view', $quotation) ?? false;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
