<?php

namespace App\Domains\CRM\Models;

use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client;
use App\Domains\CRM\Enums\InquirySource;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'client_name',
    'email',
    'phone',
    'company_or_institution',
    'service_id',
    'package_id',
    'client_id',
    'preferred_date',
    'alternative_date',
    'location',
    'project_brief',
    'reference_links',
    'estimated_budget',
    'source',
    'status',
    'assigned_admin_id',
    'lost_reason',
    'notes_internal',
])]
class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => InquiryStatus::class,
        'source' => InquirySource::class,
        'reference_links' => 'array',
        'preferred_date' => 'date',
        'alternative_date' => 'date',
        'estimated_budget' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Inquiry $inquiry) {
            if (empty($inquiry->public_id)) {
                $inquiry->public_id = (string) Str::ulid();
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function addOns(): BelongsToMany
    {
        return $this->belongsToMany(AddOn::class, 'inquiry_add_ons')->withTimestamps();
    }
}
