<?php

namespace App\Domains\Orders\Models;

use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_order_id',
    'from_status',
    'to_status',
    'actor_user_id',
    'reason',
    'payload',
])]
class ServiceOrderStatusLog extends Model
{
    use HasFactory;

    protected $casts = [
        'from_status' => ServiceOrderStatus::class,
        'to_status' => ServiceOrderStatus::class,
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
