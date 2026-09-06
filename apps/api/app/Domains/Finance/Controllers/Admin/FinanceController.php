<?php

namespace App\Domains\Finance\Controllers\Admin;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Finance\Actions\ApproveWorkerExpenseAction;
use App\Domains\Finance\Actions\CalculateProjectProfitAction;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Finance\Resources\WorkerExpenseResource;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class FinanceController extends Controller
{
    public function expenses(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', WorkerExpense::class);

        $expenses = WorkerExpense::with(['worker.user', 'project', 'approvedBy'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->input('project_id')))
            ->when($request->filled('worker_id'), fn ($q) => $q->where('worker_profile_id', $request->input('worker_id')))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return WorkerExpenseResource::collection($expenses);
    }

    public function approveExpense(
        WorkerExpense $expense,
        ApproveWorkerExpenseAction $action,
        Request $request
    ): JsonResponse {
        Gate::authorize('approve', $expense);

        try {
            $approved = $action->execute($expense, $request->user());

            return (new WorkerExpenseResource($approved->load(['worker.user', 'project', 'approvedBy'])))
                ->response()
                ->setStatusCode(200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rejectExpense(WorkerExpense $expense, Request $request): JsonResponse
    {
        Gate::authorize('reject', $expense);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $expense->status;
        $expense->status = ExpenseStatus::Rejected;
        $expense->rejection_reason = $validated['rejection_reason'];
        $expense->save();

        AuditLogger::log(
            action: 'WORKER_EXPENSE_REJECTED',
            description: "Worker expense #{$expense->id} ('{$expense->title}') rejected: {$validated['rejection_reason']}",
            auditable: $expense,
            oldValues: ['status' => $oldStatus->value],
            newValues: ['status' => ExpenseStatus::Rejected->value]
        );

        return (new WorkerExpenseResource($expense->load(['worker.user', 'project', 'approvedBy'])))
            ->response()
            ->setStatusCode(200);
    }

    public function projectProfitSummary(
        Project $project,
        CalculateProjectProfitAction $action,
        Request $request
    ): JsonResponse {
        Gate::authorize('view', $project);

        $summary = $action->execute($project, $request->user());

        return response()->json(['data' => $summary]);
    }

    public function overallSummary(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole(['OWNER', 'ADMIN'])) {
            abort(403, 'Financial summary is restricted.');
        }

        $totalInvoiced = (int) Invoice::whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Paid])->sum('amount');
        $totalCollected = (int) Invoice::where('status', InvoiceStatus::Paid)->sum('paid_amount');
        $totalExpenses = (int) WorkerExpense::whereIn('status', [ExpenseStatus::Approved, ExpenseStatus::Paid])->sum('amount');
        $netOperatingProfit = $totalCollected - $totalExpenses;

        return response()->json([
            'data' => [
                'total_invoiced' => $totalInvoiced,
                'total_collected' => $totalCollected,
                'total_approved_expenses' => $totalExpenses,
                'net_operating_profit' => $netOperatingProfit,
                'collection_rate_percentage' => $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 2) : 0,
                'currency' => 'IDR',
            ],
        ]);
    }
}
