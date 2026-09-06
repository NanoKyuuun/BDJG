<?php

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\CatalogStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description_internal', 'status', 'sort_order'])]
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => CatalogStatus::class,
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (empty($service->public_id)) {
                $service->public_id = (string) Str::ulid();
            }
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class)->orderBy('sort_order');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class)->orderBy('sort_order');
    }
}
