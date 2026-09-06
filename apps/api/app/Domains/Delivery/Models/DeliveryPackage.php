<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Delivery\Enums\DeliveryPackageStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'project_id',
    'title',
    'status',
    'storage_key',
    'disk',
    'total_size_bytes',
    'file_count',
    'download_count',
    'expires_at',
    'released_by_user_id',
    'released_at',
    'notes',
])]
class DeliveryPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => DeliveryPackageStatus::class,
        'total_size_bytes' => 'integer',
        'file_count' => 'integer',
        'download_count' => 'integer',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DeliveryPackage $package) {
            if (empty($package->public_id)) {
                $package->public_id = (string) Str::ulid();
            }
            if (empty($package->status)) {
                $package->status = DeliveryPackageStatus::Preparing;
            }
            if (empty($package->disk)) {
                $package->disk = 'media';
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryPackageItem::class);
    }

    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'delivery_package_items', 'delivery_package_id', 'media_asset_id')
            ->withTimestamps();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
