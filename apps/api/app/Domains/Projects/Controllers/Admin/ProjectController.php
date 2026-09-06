<?php

namespace App\Domains\Projects\Controllers\Admin;

use App\Domains\Projects\Actions\ChangeProjectStatusAction;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Requests\StoreProjectRequest;
use App\Domains\Projects\Requests\UpdateProjectStatusRequest;
use App\Domains\Projects\Resources\AdminProjectResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Project::class);

        $projects = Project::with(['client', 'assignments.worker.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->input('client_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('project_number', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('display_name', 'like', $term));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return AdminProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return (new AdminProjectResource($project->load(['client'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): AdminProjectResource
    {
        Gate::authorize('view', $project);

        return new AdminProjectResource($project->load([
            'client',
            'quotation',
            'assignments.worker.user',
            'tasks.assignedWorker.user',
            'schedules',
        ]));
    }

    public function changeStatus(UpdateProjectStatusRequest $request, Project $project, ChangeProjectStatusAction $action): AdminProjectResource|JsonResponse
    {
        Gate::authorize('update', $project);

        try {
            $statusInput = $request->validated('status');
            $targetStatus = $statusInput instanceof ProjectStatus ? $statusInput : ProjectStatus::from($statusInput);

            $updated = $action->execute(
                $project,
                $targetStatus,
                $request->input('notes'),
                $request->user()
            );

            return new AdminProjectResource($updated->load(['client']));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project archived successfully.']);
    }
}
