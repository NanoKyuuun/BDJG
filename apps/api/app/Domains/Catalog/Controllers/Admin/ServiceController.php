<?php

namespace App\Domains\Catalog\Controllers\Admin;

use App\Domains\Catalog\Models\Service;
use App\Domains\Catalog\Requests\StoreServiceRequest;
use App\Domains\Catalog\Requests\UpdateServiceRequest;
use App\Domains\Catalog\Resources\ServiceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Service::class);

        $services = Service::with(['packages', 'addOns'])
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 25));

        return ServiceResource::collection($services);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Service $service): ServiceResource
    {
        Gate::authorize('view', $service);

        $service->load(['packages', 'addOns']);

        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request, Service $service): ServiceResource
    {
        $service->update($request->validated());

        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        Gate::authorize('delete', $service);

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}
