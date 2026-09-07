<?php

namespace App\Domains\Orders\Models;

use App\Domains\Orders\Enums\OrderAttachmentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'service_order_id',
    'uploaded_by_user_id',
    'filename',
    'original_name',
    'storage_key',
    'disk',
    'mime_type',
    'size_bytes',
    'attachment_type',
])]
class ServiceOrderAttachment extends Model
{
    use HasFactory;

    protected $casts = [
        'attachment_type' => OrderAttachmentType::class,
        'size_bytes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceOrderAttachment $att) {
            if (empty($att->public_id)) {
                $att->public_id = (string) Str::ulid();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
