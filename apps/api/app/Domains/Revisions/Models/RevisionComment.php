<?php

namespace App\Domains\Revisions\Models;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Revisions\Enums\CommentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'revision_id',
    'media_asset_id',
    'user_id',
    'timecode_seconds',
    'frame_number',
    'coordinates',
    'comment',
    'status',
    'resolved_by_user_id',
    'resolved_at',
])]
class RevisionComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => CommentStatus::class,
        'timecode_seconds' => 'float',
        'frame_number' => 'integer',
        'coordinates' => 'array',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RevisionComment $comment) {
            if (empty($comment->public_id)) {
                $comment->public_id = (string) Str::ulid();
            }
            if (empty($comment->status)) {
                $comment->status = CommentStatus::Open;
            }
        });
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(Revision::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
