<?php

namespace App\Domains\Catalog\Controllers\Public;

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Catalog\Resources\AddOnResource;
use App\Domains\Catalog\Resources\PackageResource;
use App\Domains\Catalog\Resources\ServiceResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function services(): AnonymousResourceCollection
    {
        $services = Service::where('status', CatalogStatus::Active)
            ->with([
                'packages' => fn ($q) => $q->where('status', CatalogStatus::Active)->orderBy('sort_order'),
                'addOns' => fn ($q) => $q->where('status', CatalogStatus::Active)->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();

        return ServiceResource::collection($services);
    }

    public function packages(): AnonymousResourceCollection
    {
        $packages = Package::where('status', CatalogStatus::Active)
            ->with('service')
            ->orderBy('sort_order')
            ->get();

        return PackageResource::collection($packages);
    }

    public function addOns(): AnonymousResourceCollection
    {
        $addOns = AddOn::where('status', CatalogStatus::Active)
            ->with('service')
            ->orderBy('sort_order')
            ->get();

        return AddOnResource::collection($addOns);
    }
}
