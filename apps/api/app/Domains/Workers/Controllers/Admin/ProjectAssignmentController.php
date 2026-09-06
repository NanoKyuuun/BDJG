<?php

namespace App\Domains\Workers\Controllers\Admin;

use App\Domains\Projects\Models\Project;
use App\Domains\Workers\Models\ProjectAssignment;
use App\Domains\Workers\Requests\AssignWorkerRequest;
use App\Domains\Workers\Resources\ProjectAssignmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProjectAssignmentController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $assignments = $project->assignments()->with('worker.user')->get();

        return ProjectAssignmentResource::collection($assignments);
    }

    public function assign(AssignWorkerRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('update', $project);

        $assignment = ProjectAssignment::updateOrCreate(
            [
                'project_id' => $project->id,
                'worker_id' => $request->integer('worker_id'),
                'assignment_role' => $request->input('assignment_role'),
            ],
            [
                'fee_amount' => $request->integer('fee_amount', 0),
                'is_active' => true,
                'assigned_at' => now(),
                'removed_at' => null,
                'assigned_by' => $request->user()->id,
            ]
        );

        return (new ProjectAssignmentResource($assignment->load('worker.user')))
            ->response()
            ->setStatusCode(201);
    }

    public function remove(Project $project, ProjectAssignment $assignment): JsonResponse
    {
        Gate::authorize('update', $project);

        if ($assignment->project_id !== $project->id) {
            return response()->json(['message' => 'Assignment does not belong to this project.'], 404);
        }

        $assignment->update([
            'is_active' => false,
            'removed_at' => now(),
        ]);

        return response()->json(['message' => 'Worker assignment removed successfully.']);
    }
}
