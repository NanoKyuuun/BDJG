<?php

namespace App\Domains\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $this->user()?->can('update', $inquiry) ?? false;
    }

    public function rules(): array
    {
        return [
            'assigned_admin_id' => ['required', 'exists:users,id'],
        ];
    }
}
