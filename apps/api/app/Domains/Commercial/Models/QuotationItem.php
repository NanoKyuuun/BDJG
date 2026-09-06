<?php

namespace App\Domains\Commercial\Models;

use App\Domains\Commercial\Enums\QuotationItemType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quotation_version_id',
    'type',
    'name',
    'description',
    'quantity',
    'unit_price',
    'line_total',
    'sort_order',
])]
class QuotationItem extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => QuotationItemType::class,
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'line_total' => 'integer',
        'sort_order' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class, 'quotation_version_id');
    }
}
