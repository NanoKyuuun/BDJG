<?php

namespace App\Domains\Projects\Controllers\WorkerPortal;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Resources\WorkerProjectResource;
use App\Domains\Schedules\Models\Schedule;
use App\Domains\Schedules\Resources\ScheduleResource;
use App\Domains\Tasks\Enums\TaskStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Requests\UpdateTaskStatusRequest;
use App\Domains\Tasks\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WorkerProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $worker = $user->workerProfile;

        if (! $worker) {
            return WorkerProjectResource::collection(collect());
        }

        $projects = $worker->activeProjects()
            ->with(['tasks' => fn ($q) => $q->where('assigned_worker_id', $worker->id), 'schedules'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return WorkerProjectResource::collection($projects);
    }

    public function show(Project $project): WorkerProjectResource
    {
        Gate::authorize('view', $project);

        $worker = request()->user()->workerProfile;

        return new WorkerProjectResource($project->load([
            'tasks' => fn ($q) => $q->where('assigned_worker_id', $worker?->id),
            'schedules',
        ]));
    }

    public function tasks(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $worker = $user->workerProfile;

        if (! $worker) {
            return TaskResource::collection(collect());
        }

        $tasks = Task::where('assigned_worker_id', $worker->id)
            ->with('project')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('due_at', 'asc')
            ->paginate($request->integer('per_page', 25));

        return TaskResource::collection($tasks);
    }

    public function updateTaskStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource|JsonResponse
    {
        Gate::authorize('update', $task);

        $statusInput = $request->validated('status');
        $newStatus = $statusInput instanceof TaskStatus ? $statusInput : TaskStatus::from($statusInput);

        if (! $task->status->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "Cannot transition task from {$task->status->value} to {$newStatus->value}.",
            ], 422);
        }

        $task->status = $newStatus;
        if ($newStatus === TaskStatus::Done) {
            $task->completed_at = now();
        }
        $task->save();

        return new TaskResource($task->load('project'));
    }

    public function schedules(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $worker = $user->workerProfile;

        if (! $worker) {
            return ScheduleResource::collection(collect());
        }

        $projectIds = $worker->activeProjects()->pluck('projects.id');

        $schedules = Schedule::whereIn('project_id', $projectIds)
            ->with('project')
            ->orderBy('start_time', 'asc')
            ->paginate($request->integer('per_page', 25));

        return ScheduleResource::collection($schedules);
    }
}
