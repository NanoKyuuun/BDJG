<?php

namespace App\Domains\Workers\Models;

use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'worker_id',
    'assignment_role',
    'fee_amount',
    'is_active',
    'assigned_at',
    'removed_at',
    'assigned_by',
])]
class ProjectAssignment extends Model
{
    use HasFactory;

    protected $casts = [
        'fee_amount' => 'integer',
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerProfile::class, 'worker_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
