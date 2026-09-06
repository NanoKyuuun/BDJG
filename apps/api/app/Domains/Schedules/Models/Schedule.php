<?php

namespace App\Domains\Schedules\Models;

use App\Domains\Projects\Models\Project;
use App\Domains\Schedules\Enums\ScheduleType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'project_id',
    'title',
    'schedule_type',
    'start_time',
    'end_time',
    'location',
    'description',
    'created_by',
])]
class Schedule extends Model
{
    use HasFactory;

    protected $casts = [
        'schedule_type' => ScheduleType::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Schedule $schedule) {
            if (empty($schedule->public_id)) {
                $schedule->public_id = (string) Str::ulid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
