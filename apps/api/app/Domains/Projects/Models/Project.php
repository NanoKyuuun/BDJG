<?php

namespace App\Domains\Projects\Models;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Models\QuotationVersion;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Schedules\Models\Schedule;
use App\Domains\Tasks\Models\Task;
use App\Domains\Workers\Models\ProjectAssignment;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'project_number',
    'client_id',
    'quotation_id',
    'accepted_quotation_version_id',
    'name',
    'service_name_snapshot',
    'package_name_snapshot',
    'contract_value',
    'status',
    'start_date',
    'shoot_date',
    'deadline',
    'location',
    'brief',
    'assigned_admin_id',
    'activated_at',
    'completed_at',
    'notes_internal',
    'created_by',
])]
class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => ProjectStatus::class,
        'contract_value' => 'integer',
        'start_date' => 'date',
        'shoot_date' => 'date',
        'deadline' => 'date',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->public_id)) {
                $project->public_id = (string) Str::ulid();
            }

            if (empty($project->project_number)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $project->project_number = sprintf('PRJ-%s-%03d', $year, $count);
            }

            if (empty($project->activated_at)) {
                $project->activated_at = now();
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

    public function acceptedQuotationVersion(): BelongsTo
    {
        return $this->belongsTo(QuotationVersion::class, 'accepted_quotation_version_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(WorkerProfile::class, 'project_assignments', 'project_id', 'worker_id')
            ->withPivot(['assignment_role', 'fee_amount', 'is_active', 'assigned_at', 'removed_at'])
            ->withTimestamps();
    }

    public function activeWorkers(): BelongsToMany
    {
        return $this->workers()->wherePivot('is_active', true);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(\App\Domains\Media\Models\MediaAsset::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(\App\Domains\Finance\Models\WorkerExpense::class);
    }
}
