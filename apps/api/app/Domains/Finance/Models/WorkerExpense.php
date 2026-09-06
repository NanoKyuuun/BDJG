<?php

namespace App\Domains\Finance\Models;

use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'public_id',
    'worker_profile_id',
    'project_id',
    'title',
    'amount',
    'category',
    'receipt_storage_key',
    'status',
    'approved_by_user_id',
    'approved_at',
    'paid_at',
    'notes',
    'rejection_reason',
])]
class WorkerExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'amount' => 'integer',
        'category' => ExpenseCategory::class,
        'status' => ExpenseStatus::class,
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkerExpense $expense) {
            if (empty($expense->public_id)) {
                $expense->public_id = (string) Str::ulid();
            }
            if (empty($expense->status)) {
                $expense->status = ExpenseStatus::Submitted;
            }
        });
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class, 'worker_profile_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
