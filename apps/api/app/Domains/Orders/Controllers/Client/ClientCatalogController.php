<?php

namespace App\Domains\Orders\Controllers\Client;

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Catalog\Resources\PackageResource;
use App\Domains\Catalog\Resources\ServiceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientCatalogController extends Controller
{
    public function services(Request $request): AnonymousResourceCollection
    {
        $services = Service::where('status', CatalogStatus::Active)
            ->with(['packages' => fn ($q) => $q->where('status', CatalogStatus::Active)->orderBy('sort_order', 'asc'), 'addOns' => fn ($q) => $q->where('status', CatalogStatus::Active)])
            ->orderBy('sort_order', 'asc')
            ->get();

        return ServiceResource::collection($services);
    }

    public function showPackage(Package $package): JsonResponse
    {
        if ($package->status !== CatalogStatus::Active) {
            abort(404, 'Package is inactive or archived.');
        }

        $package->loadMissing(['service.addOns']);

        return (new PackageResource($package))->response();
    }
}
