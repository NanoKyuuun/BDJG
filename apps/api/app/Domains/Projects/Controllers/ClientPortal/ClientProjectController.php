<?php

namespace App\Domains\Projects\Controllers\ClientPortal;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Resources\ClientProjectResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $clientIds = $user->clients()->pluck('clients.id');

        $projects = Project::whereIn('client_id', $clientIds)
            ->with('schedules')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ClientProjectResource::collection($projects);
    }

    public function show(Project $project): ClientProjectResource
    {
        Gate::authorize('view', $project);

        return new ClientProjectResource($project->load('schedules'));
    }
}
