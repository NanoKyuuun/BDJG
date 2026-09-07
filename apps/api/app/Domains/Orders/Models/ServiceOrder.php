<?php

namespace App\Domains\Orders\Models;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Orders\Enums\ServiceOrderReviewType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'order_number',
    'client_id',
    'service_id',
    'package_id',
    'source',
    'review_type',
    'status',
    'package_snapshot',
    'event_date',
    'start_time',
    'end_time',
    'venue_count',
    'location_name',
    'location_address',
    'is_outside_base_area',
    'slot_hold_until',
    'quotation_id',
    'invoice_id',
    'project_id',
    'submitted_at',
    'reviewed_at',
    'cancelled_at',
    'created_by',
])]
class ServiceOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => ServiceOrderStatus::class,
        'source' => ServiceOrderSource::class,
        'review_type' => ServiceOrderReviewType::class,
        'package_snapshot' => 'array',
        'event_date' => 'date',
        'venue_count' => 'integer',
        'is_outside_base_area' => 'boolean',
        'slot_hold_until' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceOrder $order) {
            if (empty($order->public_id)) {
                $order->public_id = (string) Str::ulid();
            }

            if (empty($order->order_number)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $order->order_number = sprintf('ORD-%s-%03d', $year, $count);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function brief(): HasOne
    {
        return $this->hasOne(ServiceOrderBrief::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceOrderAttachment::class)->latest();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceOrderMessage::class)->oldest();
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServiceOrderStatusLog::class)->latest();
    }
}
