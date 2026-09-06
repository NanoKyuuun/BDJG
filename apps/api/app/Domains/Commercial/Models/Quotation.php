<?php

namespace App\Domains\Commercial\Models;

use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'quotation_number',
    'client_id',
    'inquiry_id',
    'status',
    'current_version_id',
    'accepted_version_id',
    'sent_at',
    'viewed_at',
    'accepted_at',
    'declined_at',
    'decline_reason',
    'revision_request_notes',
    'expires_at',
    'created_by',
    'notes_internal',
])]
class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => QuotationStatus::class,
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'expires_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->public_id)) {
                $quotation->public_id = (string) Str::ulid();
            }

            if (empty($quotation->quotation_number)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $quotation->quotation_number = sprintf('QT-%s-%03d', $year, $count);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuotationVersion::class)->orderBy('version_number', 'asc');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class, 'current_version_id');
    }

    public function acceptedVersion(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class, 'accepted_version_id');
    }
}
