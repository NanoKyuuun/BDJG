<?php

namespace App\Domains\Delivery\Controllers\Admin;

use App\Domains\Delivery\Actions\CreateDeliveryPackageAction;
use App\Domains\Delivery\Actions\GenerateDeliveryDownloadUrlAction;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Delivery\Resources\DeliveryPackageResource;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class DeliveryPackageController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $packages = DeliveryPackage::where('project_id', $project->id)
            ->with(['mediaAssets', 'releasedBy'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return DeliveryPackageResource::collection($packages);
    }

    public function store(Request $request, Project $project, CreateDeliveryPackageAction $action): JsonResponse
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'media_asset_ids' => 'required|array|min:1',
            'media_asset_ids.*' => 'integer|exists:media_assets,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'expiry_days' => 'nullable|integer|min:1|max:365',
        ]);

        try {
            $package = $action->execute(
                project: $project,
                actor: $request->user(),
                mediaAssetIds: $validated['media_asset_ids'],
                title: $validated['title'],
                notes: $validated['notes'] ?? null,
                expiryDays: $validated['expiry_days'] ?? 30
            );

            return (new DeliveryPackageResource($package->load(['mediaAssets', 'releasedBy'])))
                ->response()
                ->setStatusCode(201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(DeliveryPackage $deliveryPackage): DeliveryPackageResource
    {
        Gate::authorize('view', $deliveryPackage);

        return new DeliveryPackageResource($deliveryPackage->load(['mediaAssets', 'releasedBy']));
    }

    public function downloadUrl(
        DeliveryPackage $deliveryPackage,
        GenerateDeliveryDownloadUrlAction $action,
        Request $request
    ): JsonResponse {
        Gate::authorize('download', $deliveryPackage);

        try {
            $result = $action->execute($deliveryPackage, $request->user(), 120);

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(DeliveryPackage $deliveryPackage): JsonResponse
    {
        Gate::authorize('delete', $deliveryPackage);

        $deliveryPackage->delete();

        return response()->json(['message' => 'Delivery package deleted successfully.']);
    }
}
