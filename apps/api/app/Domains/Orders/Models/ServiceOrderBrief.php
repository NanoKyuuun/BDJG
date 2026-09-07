<?php

namespace App\Domains\Orders\Models;

use App\Domains\Orders\Enums\BriefCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'service_order_id',
    'event_name',
    'event_category',
    'couple_session_details',
    'wedding_details',
    'custom_requirements',
    'selected_add_ons',
    'onsite_pic_name',
    'onsite_pic_phone',
    'portfolio_consent',
    'additional_notes',
])]
class ServiceOrderBrief extends Model
{
    use HasFactory;

    protected $casts = [
        'event_category' => BriefCategory::class,
        'couple_session_details' => 'array',
        'wedding_details' => 'array',
        'custom_requirements' => 'array',
        'selected_add_ons' => 'array',
        'portfolio_consent' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceOrderBrief $brief) {
            if (empty($brief->public_id)) {
                $brief->public_id = (string) Str::ulid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }
}
