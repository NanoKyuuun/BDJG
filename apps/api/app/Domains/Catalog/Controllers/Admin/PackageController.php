<?php

namespace App\Domains\Catalog\Controllers\Admin;

use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Requests\StorePackageRequest;
use App\Domains\Catalog\Requests\UpdatePackageRequest;
use App\Domains\Catalog\Resources\PackageResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Package::class);

        $packages = Package::with('service')
            ->when($request->filled('service_id'), fn ($q) => $q->where('service_id', $request->input('service_id')))
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 25));

        return PackageResource::collection($packages);
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = Package::create($request->validated());

        return (new PackageResource($package->load('service')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Package $package): PackageResource
    {
        Gate::authorize('view', $package);

        return new PackageResource($package->load('service'));
    }

    public function update(UpdatePackageRequest $request, Package $package): PackageResource
    {
        $package->update($request->validated());

        return new PackageResource($package->load('service'));
    }

    public function destroy(Package $package): JsonResponse
    {
        Gate::authorize('delete', $package);

        $package->delete();

        return response()->json(['message' => 'Package deleted successfully']);
    }
}
