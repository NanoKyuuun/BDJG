<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'delivery_package_id',
    'media_asset_id',
])]
class DeliveryPackageItem extends Model
{
    use HasFactory;

    public function package(): BelongsTo
    {
        return $this->belongsTo(DeliveryPackage::class, 'delivery_package_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }
}
