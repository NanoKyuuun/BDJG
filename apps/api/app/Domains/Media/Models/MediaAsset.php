<?php

namespace App\Domains\Media\Models;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'project_id',
    'uploaded_by_user_id',
    'pending_upload_id',
    'filename',
    'original_name',
    'storage_key',
    'disk',
    'mime_type',
    'size_bytes',
    'category',
    'visibility',
    'version_number',
    'processing_status',
    'metadata',
    'released_by_user_id',
    'released_at',
])]
class MediaAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'category' => MediaCategory::class,
        'visibility' => FileVisibility::class,
        'processing_status' => ProcessingStatus::class,
        'size_bytes' => 'integer',
        'version_number' => 'integer',
        'metadata' => 'array',
        'released_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaAsset $asset) {
            if (empty($asset->public_id)) {
                $asset->public_id = (string) Str::ulid();
            }
            if (empty($asset->disk)) {
                $asset->disk = 'media';
            }
            if (empty($asset->processing_status)) {
                $asset->processing_status = ProcessingStatus::Ready;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }

    public function pendingUpload(): BelongsTo
    {
        return $this->belongsTo(PendingUpload::class);
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->isVideo();
    }

    public function getIsImageAttribute(): bool
    {
        return $this->isImage();
    }
}
