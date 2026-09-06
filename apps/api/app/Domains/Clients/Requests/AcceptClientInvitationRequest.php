<?php

namespace App\Domains\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptClientInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Guest user setting password
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
