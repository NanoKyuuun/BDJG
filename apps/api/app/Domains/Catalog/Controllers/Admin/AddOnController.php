<?php

namespace App\Domains\Catalog\Controllers\Admin;

use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Requests\StoreAddOnRequest;
use App\Domains\Catalog\Requests\UpdateAddOnRequest;
use App\Domains\Catalog\Resources\AddOnResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AddOnController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', AddOn::class);

        $addOns = AddOn::with('service')
            ->when($request->filled('service_id'), fn ($q) => $q->where('service_id', $request->input('service_id')))
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 25));

        return AddOnResource::collection($addOns);
    }

    public function store(StoreAddOnRequest $request): JsonResponse
    {
        $addOn = AddOn::create($request->validated());

        return (new AddOnResource($addOn->load('service')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(AddOn $addOn): AddOnResource
    {
        Gate::authorize('view', $addOn);

        return new AddOnResource($addOn->load('service'));
    }

    public function update(UpdateAddOnRequest $request, AddOn $addOn): AddOnResource
    {
        $addOn->update($request->validated());

        return new AddOnResource($addOn->load('service'));
    }

    public function destroy(AddOn $addOn): JsonResponse
    {
        Gate::authorize('delete', $addOn);

        $addOn->delete();

        return response()->json(['message' => 'Add-on deleted successfully']);
    }
}
