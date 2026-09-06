<?php

namespace App\Domains\Revisions\Models;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Domains\Revisions\Enums\RevisionRoundStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'project_id',
    'media_asset_id',
    'round_number',
    'title',
    'requested_by_user_id',
    'status',
    'notes',
    'resolved_at',
    'resolved_by_user_id',
])]
class Revision extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => RevisionRoundStatus::class,
        'round_number' => 'integer',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Revision $revision) {
            if (empty($revision->public_id)) {
                $revision->public_id = (string) Str::ulid();
            }
            if (empty($revision->status)) {
                $revision->status = RevisionRoundStatus::Open;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RevisionComment::class)->orderBy('timecode_seconds')->latest();
    }
}
