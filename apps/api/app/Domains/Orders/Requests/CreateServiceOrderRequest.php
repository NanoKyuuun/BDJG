<?php

namespace App\Domains\Orders\Requests;

use App\Domains\Orders\Enums\BriefCategory;
use App\Domains\Orders\Enums\ServiceOrderSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'source' => ['nullable', new Enum(ServiceOrderSource::class)],
            'event_name' => ['nullable', 'string', 'max:255'],
            'brief_category' => ['nullable', new Enum(BriefCategory::class)],
        ];
    }
}
