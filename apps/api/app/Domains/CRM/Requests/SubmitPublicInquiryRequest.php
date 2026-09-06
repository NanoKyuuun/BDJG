<?php

namespace App\Domains\CRM\Requests;

use App\Domains\CRM\Enums\InquirySource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SubmitPublicInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest public submission
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_or_institution' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', 'exists:services,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'preferred_date' => ['nullable', 'date'],
            'alternative_date' => ['nullable', 'date', 'after_or_equal:preferred_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'project_brief' => ['required', 'string'],
            'reference_links' => ['nullable', 'array'],
            'reference_links.*' => ['url'],
            'estimated_budget' => ['nullable', 'integer', 'min:0'],
            'source' => ['nullable', new Enum(InquirySource::class)],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['exists:add_ons,id'],
        ];
    }
}
