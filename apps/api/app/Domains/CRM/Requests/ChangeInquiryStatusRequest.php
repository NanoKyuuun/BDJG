<?php

namespace App\Domains\CRM\Requests;

use App\Domains\CRM\Enums\InquiryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ChangeInquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $this->user()?->can('update', $inquiry) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(InquiryStatus::class)],
            'lost_reason' => [
                Rule::requiredIf(fn () => $this->input('status') === InquiryStatus::Lost->value),
                'nullable',
                'string',
            ],
            'notes_internal' => ['nullable', 'string'],
        ];
    }
}
