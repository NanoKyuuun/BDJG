<?php

namespace App\Domains\Finance\Controllers\WorkerPortal;

use App\Domains\Finance\Actions\SubmitWorkerExpenseAction;
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Finance\Resources\WorkerExpenseResource;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class WorkerExpenseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $worker = $request->user()->workerProfile;
        if (! $worker) {
            abort(403, 'User does not have a worker profile.');
        }

        $expenses = WorkerExpense::where('worker_profile_id', $worker->id)
            ->with(['project'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return WorkerExpenseResource::collection($expenses);
    }

    public function store(Request $request, SubmitWorkerExpenseAction $action): JsonResponse
    {
        $worker = $request->user()->workerProfile;
        if (! $worker) {
            abort(403, 'User does not have a worker profile.');
        }

        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|integer|min:1000',
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'receipt_storage_key' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        try {
            $expense = $action->execute(
                project: $project,
                worker: $worker,
                actor: $request->user(),
                title: $validated['title'],
                amount: $validated['amount'],
                category: ExpenseCategory::from($validated['category']),
                receiptStorageKey: $validated['receipt_storage_key'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return (new WorkerExpenseResource($expense->load(['project'])))
                ->response()
                ->setStatusCode(201);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function show(WorkerExpense $expense): WorkerExpenseResource
    {
        Gate::authorize('view', $expense);

        return new WorkerExpenseResource($expense->load(['project', 'approvedBy']));
    }
}
