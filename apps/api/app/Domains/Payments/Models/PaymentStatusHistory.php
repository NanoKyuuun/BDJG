<?php

namespace App\Domains\Payments\Models;

use App\Domains\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payment_transaction_id',
    'from_status',
    'to_status',
    'source',
    'payload',
])]
class PaymentStatusHistory extends Model
{
    use HasFactory;

    protected $casts = [
        'from_status' => PaymentStatus::class,
        'to_status' => PaymentStatus::class,
        'payload' => 'array',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
