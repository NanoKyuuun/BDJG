<?php

namespace App\Domains\Commercial\Models;

use App\Domains\Catalog\Enums\DpType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'quotation_id',
    'version_number',
    'project_name',
    'service_name_snapshot',
    'package_name_snapshot',
    'subtotal',
    'discount',
    'tax',
    'grand_total',
    'dp_type',
    'dp_value',
    'dp_amount',
    'remaining_amount',
    'terms',
    'revision_notes',
    'created_by',
])]
class QuotationVersion extends Model
{
    use HasFactory;

    protected $casts = [
        'version_number' => 'integer',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
        'grand_total' => 'integer',
        'dp_type' => DpType::class,
        'dp_value' => 'decimal:2',
        'dp_amount' => 'integer',
        'remaining_amount' => 'integer',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order', 'asc');
    }
}
