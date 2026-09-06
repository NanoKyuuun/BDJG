<?php

namespace App\Domains\CRM\Requests;

use App\Domains\CRM\Enums\InquirySource;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Inquiry::class) ?? false;
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
            'client_id' => ['nullable', 'exists:clients,id'],
            'preferred_date' => ['nullable', 'date'],
            'alternative_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'project_brief' => ['required', 'string'],
            'reference_links' => ['nullable', 'array'],
            'reference_links.*' => ['url'],
            'estimated_budget' => ['nullable', 'integer', 'min:0'],
            'source' => ['nullable', new Enum(InquirySource::class)],
            'status' => ['nullable', new Enum(InquiryStatus::class)],
            'assigned_admin_id' => ['nullable', 'exists:users,id'],
            'notes_internal' => ['nullable', 'string'],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['exists:add_ons,id'],
        ];
    }
}
