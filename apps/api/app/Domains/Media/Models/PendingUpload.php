<?php

namespace App\Domains\Media\Models;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\UploadStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'project_id',
    'user_id',
    'filename',
    'original_name',
    'storage_key',
    'disk',
    'mime_type',
    'size_bytes',
    'category',
    'visibility',
    'status',
    'expires_at',
])]
class PendingUpload extends Model
{
    use HasFactory;

    protected $casts = [
        'category' => MediaCategory::class,
        'visibility' => FileVisibility::class,
        'status' => UploadStatus::class,
        'size_bytes' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PendingUpload $upload) {
            if (empty($upload->public_id)) {
                $upload->public_id = (string) Str::ulid();
            }
            if (empty($upload->disk)) {
                $upload->disk = 'media';
            }
            if (empty($upload->status)) {
                $upload->status = UploadStatus::Pending;
            }
            if (empty($upload->expires_at)) {
                $upload->expires_at = now()->addHours(2);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
