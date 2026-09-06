<?php

namespace App\Domains\Tasks\Controllers\Admin;

use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Requests\StoreTaskRequest;
use App\Domains\Tasks\Requests\UpdateTaskStatusRequest;
use App\Domains\Tasks\Resources\TaskResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Task::class);

        $tasks = Task::with(['project', 'assignedWorker.user'])
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->input('project_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('worker_id'), fn ($q) => $q->where('assigned_worker_id', $request->input('worker_id')))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return (new TaskResource($task->load(['project', 'assignedWorker.user'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task->load(['project', 'assignedWorker.user']));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource|JsonResponse
    {
        Gate::authorize('update', $task);

        $newStatus = $request->enum('status', \App\Domains\Tasks\Enums\TaskStatus::class);

        if (! $task->status->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "Cannot transition task status from {$task->status->value} to {$newStatus->value}.",
            ], 422);
        }

        $task->status = $newStatus;
        if ($newStatus === \App\Domains\Tasks\Enums\TaskStatus::Done) {
            $task->completed_at = now();
        }
        $task->save();

        return new TaskResource($task->load(['project', 'assignedWorker.user']));
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }
}
