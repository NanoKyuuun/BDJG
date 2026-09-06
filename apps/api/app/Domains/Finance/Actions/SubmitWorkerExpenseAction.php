<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Projects\Models\Project;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SubmitWorkerExpenseAction
{
    public function execute(
        Project $project,
        WorkerProfile $worker,
        User $actor,
        string $title,
        int $amount,
        ExpenseCategory $category,
        ?string $receiptStorageKey = null,
        ?string $notes = null
    ): WorkerExpense {
        // Validate worker is assigned to the project unless actor is Admin/Owner
        if (! $actor->hasRole(['OWNER', 'ADMIN'])) {
            $isAssigned = $project->assignments()
                ->where('worker_id', $worker->id)
                ->where('is_active', true)
                ->exists();

            if (! $isAssigned) {
                throw new AuthorizationException('Worker is not actively assigned to this project.');
            }
        }

        $expense = WorkerExpense::create([
            'worker_profile_id' => $worker->id,
            'project_id' => $project->id,
            'title' => $title,
            'amount' => $amount,
            'category' => $category,
            'receipt_storage_key' => $receiptStorageKey,
            'status' => ExpenseStatus::Submitted,
            'notes' => $notes,
        ]);

        AuditLogger::log(
            action: 'WORKER_EXPENSE_SUBMITTED',
            description: "Worker expense '{$title}' (IDR " . number_format($amount, 0, ',', '.') . ") submitted by {$worker->user?->name} for Project #{$project->id}",
            auditable: $expense,
            newValues: [
                'title' => $title,
                'amount' => $amount,
                'category' => $category->value,
                'project_id' => $project->id,
            ]
        );

        return $expense;
    }
}
