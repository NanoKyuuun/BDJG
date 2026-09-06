<?php

namespace App\Domains\Delivery\Controllers\ClientPortal;

use App\Domains\Delivery\Actions\GenerateDeliveryDownloadUrlAction;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Delivery\Resources\DeliveryPackageResource;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientDeliveryController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $client = $project->client;
        if (! $client || ! $client->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        $packages = DeliveryPackage::where('project_id', $project->id)
            ->whereIn('status', ['READY', 'DOWNLOADED'])
            ->with(['mediaAssets'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return DeliveryPackageResource::collection($packages);
    }

    public function show(DeliveryPackage $deliveryPackage): DeliveryPackageResource
    {
        Gate::authorize('view', $deliveryPackage);

        return new DeliveryPackageResource($deliveryPackage->load(['mediaAssets']));
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
}
