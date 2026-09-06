<?php

namespace App\Domains\Media\Controllers\Admin;

use App\Domains\Media\Actions\FinalizeDirectUploadAction;
use App\Domains\Media\Actions\InitiateDirectUploadAction;
use App\Domains\Media\Actions\ReleaseMediaAssetAction;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Requests\FinalizeDirectUploadRequest;
use App\Domains\Media\Requests\InitiateDirectUploadRequest;
use App\Domains\Media\Resources\MediaAssetResource;
use App\Domains\Media\Resources\PendingUploadResource;
use App\Domains\Media\Services\MediaStorageService;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class MediaAssetController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', MediaAsset::class);

        $assets = MediaAsset::where('project_id', $project->id)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('visibility'), fn ($q) => $q->where('visibility', $request->input('visibility')))
            ->latest()
            ->paginate($request->integer('per_page', 50));

        return MediaAssetResource::collection($assets);
    }

    public function uploadIntent(InitiateDirectUploadRequest $request, InitiateDirectUploadAction $action): JsonResponse
    {
        $project = Project::findOrFail($request->validated('project_id'));
        $category = MediaCategory::from($request->validated('category'));
        $visibility = $request->filled('visibility') ? FileVisibility::from($request->validated('visibility')) : null;

        $result = $action->execute(
            project: $project,
            user: $request->user(),
            filename: $request->validated('filename'),
            mimeType: $request->validated('mime_type'),
            sizeBytes: (int) $request->validated('size_bytes'),
            category: $category,
            visibility: $visibility
        );

        $resource = (new PendingUploadResource($result['pending_upload']))->additional([
            'upload_url' => $result['upload_url'],
            'upload_method' => $result['upload_method'],
            'headers' => $result['headers'],
        ]);

        return $resource->response()->setStatusCode(201);
    }

    public function finalizeUpload(FinalizeDirectUploadRequest $request, FinalizeDirectUploadAction $action): JsonResponse
    {
        try {
            $mediaAsset = $action->execute(
                pendingUploadPublicId: $request->validated('pending_upload_public_id'),
                actor: $request->user(),
                metadata: $request->validated('metadata')
            );

            return (new MediaAssetResource($mediaAsset))
                ->response()
                ->setStatusCode(201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(MediaAsset $mediaAsset): MediaAssetResource
    {
        Gate::authorize('view', $mediaAsset);

        return new MediaAssetResource($mediaAsset);
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

    public function release(Request $request, MediaAsset $mediaAsset, ReleaseMediaAssetAction $action): MediaAssetResource|JsonResponse
    {
        Gate::authorize('release', $mediaAsset);

        $visibility = $request->filled('visibility') ? FileVisibility::from($request->input('visibility')) : FileVisibility::ClientPreview;

        try {
            $released = $action->execute($mediaAsset, $request->user(), $visibility);

            return new MediaAssetResource($released);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(MediaAsset $mediaAsset, MediaStorageService $storageService): JsonResponse
    {
        Gate::authorize('delete', $mediaAsset);

        $storageService->deleteFile($mediaAsset->storage_key, $mediaAsset->disk);
        $mediaAsset->delete();

        return response()->json(['message' => 'Media asset deleted successfully.']);
    }
}
