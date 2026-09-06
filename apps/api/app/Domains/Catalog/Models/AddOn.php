<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\CatalogStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['service_id', 'name', 'description_internal', 'price', 'currency', 'status', 'sort_order'])]
class AddOn extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'price' => 'integer',
        'status' => CatalogStatus::class,
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (AddOn $addOn) {
            if (empty($addOn->public_id)) {
                $addOn->public_id = (string) Str::ulid();
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
