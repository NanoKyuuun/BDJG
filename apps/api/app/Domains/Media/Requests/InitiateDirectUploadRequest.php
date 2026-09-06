<?php

namespace App\Domains\Media\Requests;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InitiateDirectUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'max:100'],
            'size_bytes' => ['required', 'integer', 'min:1', 'max:107374182400'], // max 100 GB
            'category' => ['required', new Enum(MediaCategory::class)],
            'visibility' => ['nullable', new Enum(FileVisibility::class)],
        ];
    }
}
