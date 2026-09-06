<?php

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'invoice_number',
    'client_id',
    'quotation_id',
    'project_id',
    'invoice_type',
    'amount',
    'paid_amount',
    'currency',
    'status',
    'issued_at',
    'due_at',
    'paid_at',
    'voided_at',
    'terms',
    'notes_internal',
    'created_by',
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => InvoiceStatus::class,
        'invoice_type' => InvoiceType::class,
        'amount' => 'integer',
        'paid_amount' => 'integer',
        'issued_at' => 'datetime',
        'due_at' => 'date',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->public_id)) {
                $invoice->public_id = (string) Str::ulid();
            }

            if (empty($invoice->invoice_number)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $invoice->invoice_number = sprintf('INV-%s-%03d', $year, $count);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

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
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order', 'asc');
    }
}
