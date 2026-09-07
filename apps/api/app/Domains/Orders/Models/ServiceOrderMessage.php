<?php

namespace App\Domains\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'service_order_id',
    'sender_user_id',
    'message',
    'attachments',
    'is_internal_note',
    'read_at',
])]
class ServiceOrderMessage extends Model
{
    use HasFactory;

    protected $casts = [
        'attachments' => 'array',
        'is_internal_note' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceOrderMessage $msg) {
            if (empty($msg->public_id)) {
                $msg->public_id = (string) Str::ulid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
