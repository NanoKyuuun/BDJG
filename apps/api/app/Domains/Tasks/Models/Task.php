<?php

namespace App\Domains\Tasks\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Enums\TaskStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'project_id',
    'title',
    'description',
    'status',
    'priority',
    'assigned_worker_id',
    'due_at',
    'completed_at',
    'created_by',
])]
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->public_id)) {
                $task->public_id = (string) Str::ulid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedWorker(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class, 'assigned_worker_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
