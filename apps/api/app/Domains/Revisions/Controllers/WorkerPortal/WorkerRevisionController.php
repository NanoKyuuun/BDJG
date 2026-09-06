<?php

namespace App\Domains\Revisions\Controllers\WorkerPortal;

use App\Domains\Projects\Models\Project;
use App\Domains\Revisions\Actions\AddRevisionCommentAction;
use App\Domains\Revisions\Actions\ResolveRevisionCommentAction;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Models\RevisionComment;
use App\Domains\Revisions\Resources\RevisionCommentResource;
use App\Domains\Revisions\Resources\RevisionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class WorkerRevisionController extends Controller
{
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $worker = $request->user()->workerProfile;
        if (! $worker || ! $project->assignments()->where('worker_id', $worker->id)->where('is_active', true)->exists()) {
            abort(403, 'You are not actively assigned to this project.');
        }

        $revisions = Revision::where('project_id', $project->id)
            ->with(['mediaAsset', 'requestedBy', 'resolvedBy', 'comments.author'])
            ->latest('round_number')
            ->paginate($request->integer('per_page', 25));

        return RevisionResource::collection($revisions);
    }

    public function show(Revision $revision): RevisionResource
    {
        Gate::authorize('view', $revision);

        return new RevisionResource($revision->load(['mediaAsset', 'requestedBy', 'resolvedBy', 'comments.author', 'comments.resolvedBy']));
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

    public function resolveComment(RevisionComment $comment, ResolveRevisionCommentAction $action, Request $request): JsonResponse
    {
        Gate::authorize('resolve', $comment->revision);

        $resolved = $action->execute($comment, $request->user());

        return (new RevisionCommentResource($resolved->load(['author', 'resolvedBy'])))
            ->response()
            ->setStatusCode(200);
    }
}
