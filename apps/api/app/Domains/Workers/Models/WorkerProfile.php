<?php

namespace App\Domains\Workers\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
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
    'user_id',
    'profession',
    'skills',
    'phone',
    'status',
    'notes_internal',
])]
class WorkerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'profession' => WorkerProfession::class,
        'status' => WorkerStatus::class,
        'skills' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkerProfile $worker) {
            if (empty($worker->public_id)) {
                $worker->public_id = (string) Str::ulid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class, 'worker_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_assignments', 'worker_id', 'project_id')
            ->withPivot(['assignment_role', 'fee_amount', 'is_active', 'assigned_at', 'removed_at'])
            ->withTimestamps();
    }

    public function activeProjects(): BelongsToMany
    {
        return $this->projects()->wherePivot('is_active', true);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_worker_id');
    }
}
