<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Enums\DpType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['service_id', 'name', 'description_internal', 'base_price', 'currency', 'default_dp_type', 'default_dp_value', 'status', 'sort_order'])]
class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'base_price' => 'integer',
        'default_dp_type' => DpType::class,
        'default_dp_value' => 'decimal:2',
        'status' => CatalogStatus::class,
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Package $package) {
            if (empty($package->public_id)) {
                $package->public_id = (string) Str::ulid();
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
