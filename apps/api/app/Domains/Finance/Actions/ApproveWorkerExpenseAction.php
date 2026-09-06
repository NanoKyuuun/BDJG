<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Finance\Models\WorkerExpense;
use App\Models\User;
use InvalidArgumentException;

class ApproveWorkerExpenseAction
{
    public function execute(WorkerExpense $expense, User $actor): WorkerExpense
    {
        if ($expense->status === ExpenseStatus::Approved) {
            return $expense;
        }

        if ($expense->status->isFinal()) {
            throw new InvalidArgumentException("Cannot approve expense with status {$expense->status->value}.");
        }

        $oldStatus = $expense->status;
        $expense->status = ExpenseStatus::Approved;
        $expense->approved_by_user_id = $actor->id;
        $expense->approved_at = now();
        $expense->save();

        AuditLogger::log(
            action: 'WORKER_EXPENSE_APPROVED',
            description: "Worker expense #{$expense->id} ('{$expense->title}') approved by {$actor->name}",
            auditable: $expense,
            oldValues: ['status' => $oldStatus->value],
            newValues: ['status' => ExpenseStatus::Approved->value]
        );

        return $expense;
    }
}
