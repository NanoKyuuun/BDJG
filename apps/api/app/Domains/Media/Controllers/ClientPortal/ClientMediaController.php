<?php

namespace App\Domains\Media\Controllers\ClientPortal;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Resources\MediaAssetResource;
use App\Domains\Media\Services\MediaStorageService;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientMediaController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $user = $request->user();
        $client = $project->client;

        if (! $client || ! $client->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        // Clients can ONLY see CLIENT_SHARED, CLIENT_PREVIEW, FINAL_RELEASED, PUBLIC
        $assets = MediaAsset::where('project_id', $project->id)
            ->whereIn('visibility', [
                FileVisibility::ClientShared,
                FileVisibility::ClientPreview,
                FileVisibility::FinalReleased,
                FileVisibility::Public,
            ])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->latest()
            ->paginate($request->integer('per_page', 50));

        return MediaAssetResource::collection($assets);
    }

    public function signedUrl(MediaAsset $mediaAsset, MediaStorageService $storageService): JsonResponse
    {
        Gate::authorize('view', $mediaAsset);

        $url = $storageService->generateSignedViewUrl($mediaAsset, 120);

        return response()->json([
            'url' => $url,
            'filename' => $mediaAsset->filename,
            'mime_type' => $mediaAsset->mime_type,
            'size_bytes' => $mediaAsset->size_bytes,
        ]);
    }
}
