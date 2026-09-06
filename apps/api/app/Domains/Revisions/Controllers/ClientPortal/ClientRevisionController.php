<?php

namespace App\Domains\Revisions\Controllers\ClientPortal;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Domains\Revisions\Actions\AddRevisionCommentAction;
use App\Domains\Revisions\Actions\CreateRevisionRoundAction;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Resources\RevisionCommentResource;
use App\Domains\Revisions\Resources\RevisionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientRevisionController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $client = $project->client;
        if (! $client || ! $client->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        $revisions = Revision::where('project_id', $project->id)
            ->with(['mediaAsset', 'requestedBy', 'resolvedBy', 'comments.author'])
            ->latest('round_number')
            ->paginate($request->integer('per_page', 25));

        return RevisionResource::collection($revisions);
    }

    public function store(Request $request, Project $project, CreateRevisionRoundAction $action): JsonResponse
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'media_asset_id' => 'nullable|exists:media_assets,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $mediaAsset = ! empty($validated['media_asset_id'])
            ? MediaAsset::findOrFail($validated['media_asset_id'])
            : null;

        $revision = $action->execute(
            project: $project,
            actor: $request->user(),
            mediaAsset: $mediaAsset,
            title: $validated['title'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return (new RevisionResource($revision->load(['mediaAsset', 'requestedBy'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Revision $revision): RevisionResource
    {
        Gate::authorize('view', $revision);

        return new RevisionResource($revision->load(['mediaAsset', 'requestedBy', 'resolvedBy', 'comments.author']));
    }

    public function addComment(Request $request, Revision $revision, AddRevisionCommentAction $action): JsonResponse
    {
        Gate::authorize('view', $revision);

        $validated = $request->validate([
            'comment' => 'required|string',
            'timecode_seconds' => 'nullable|numeric|min:0',
            'frame_number' => 'nullable|integer|min:0',
            'coordinates' => 'nullable|array',
        ]);

        $comment = $action->execute(
            revision: $revision,
            author: $request->user(),
            commentText: $validated['comment'],
            timecodeSeconds: isset($validated['timecode_seconds']) ? (float) $validated['timecode_seconds'] : null,
            frameNumber: $validated['frame_number'] ?? null,
            coordinates: $validated['coordinates'] ?? null
        );

        return (new RevisionCommentResource($comment->load('author')))
            ->response()
            ->setStatusCode(201);
    }
}
