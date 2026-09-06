<?php

namespace App\Domains\Workers\Controllers\Admin;

use App\Domains\Workers\Models\WorkerProfile;
use App\Domains\Workers\Requests\StoreWorkerProfileRequest;
use App\Domains\Workers\Resources\WorkerProfileResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WorkerProfileController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', WorkerProfile::class);

        $workers = WorkerProfile::with('user')
            ->when($request->filled('profession'), fn ($q) => $q->where('profession', $request->input('profession')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return WorkerProfileResource::collection($workers);
    }

    public function store(StoreWorkerProfileRequest $request): JsonResponse
    {
        $worker = WorkerProfile::create($request->validated());

        return (new WorkerProfileResource($worker->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WorkerProfile $worker): WorkerProfileResource
    {
        Gate::authorize('view', $worker);

        return new WorkerProfileResource($worker->load(['user', 'assignments.project']));
    }

    public function update(StoreWorkerProfileRequest $request, WorkerProfile $worker): WorkerProfileResource
    {
        Gate::authorize('update', $worker);

        $worker->update($request->validated());

        return new WorkerProfileResource($worker->load('user'));
    }

    public function destroy(WorkerProfile $worker): JsonResponse
    {
        Gate::authorize('delete', $worker);

        $worker->delete();

        return response()->json(['message' => 'Worker profile deactivated.']);
    }
}
