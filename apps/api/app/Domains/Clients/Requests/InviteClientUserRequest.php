<?php

namespace App\Domains\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteClientUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('clients.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
