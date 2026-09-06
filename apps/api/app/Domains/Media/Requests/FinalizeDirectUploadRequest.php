<?php

namespace App\Domains\Media\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeDirectUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'pending_upload_public_id' => ['required', 'string', 'exists:pending_uploads,public_id'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
