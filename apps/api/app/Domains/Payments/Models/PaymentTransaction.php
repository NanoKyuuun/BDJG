<?php

namespace App\Domains\Payments\Models;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentProvider;
use App\Domains\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'invoice_id',
    'provider',
    'merchant_order_id',
    'provider_reference',
    'amount',
    'payment_method',
    'status',
    'payment_url',
    'va_number',
    'qr_string',
    'expires_at',
    'paid_at',
    'raw_payload',
    'raw_response',
])]
class PaymentTransaction extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => PaymentStatus::class,
        'provider' => PaymentProvider::class,
        'amount' => 'integer',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'raw_payload' => 'array',
        'raw_response' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $transaction) {
            if (empty($transaction->public_id)) {
                $transaction->public_id = (string) Str::ulid();
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PaymentStatusHistory::class)->orderBy('created_at', 'asc');
    }
}
