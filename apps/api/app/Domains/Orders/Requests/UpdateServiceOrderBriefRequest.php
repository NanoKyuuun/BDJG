<?php

namespace App\Domains\Orders\Requests;

use App\Domains\Orders\Enums\BriefCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateServiceOrderBriefRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue_count' => ['nullable', 'integer', 'min:1', 'max:10'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'location_address' => ['nullable', 'string'],
            'is_outside_base_area' => ['nullable', 'boolean'],
            'event_name' => ['nullable', 'string', 'max:255'],
            'event_category' => ['nullable', new Enum(BriefCategory::class)],
            'couple_session_details' => ['nullable', 'array'],
            'wedding_details' => ['nullable', 'array'],
            'custom_requirements' => ['nullable', 'array'],
            'selected_add_ons' => ['nullable', 'array'],
            'onsite_pic_name' => ['nullable', 'string', 'max:255'],
            'onsite_pic_phone' => ['nullable', 'string', 'max:50'],
            'portfolio_consent' => ['nullable', 'boolean'],
            'additional_notes' => ['nullable', 'string'],
        ];
    }
}
