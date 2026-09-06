<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CalculateProjectProfitAction
{
    public function execute(Project $project, User $actor): array
    {
        // Strictly Owner and Admin only — absolute leak prevention
        if (! $actor->hasRole(['OWNER', 'ADMIN'])) {
            throw new AuthorizationException('Financial profit and margin data is restricted to Studio Leadership.');
        }

        // 1. Total Invoiced and Total Paid Revenue
        $totalInvoiced = (int) $project->invoices()->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Paid])->sum('amount');
        $totalPaidRevenue = (int) $project->invoices()->where('status', InvoiceStatus::Paid)->sum('paid_amount');

        // 2. Total Approved/Paid Worker Expenses
        $totalApprovedExpenses = (int) $project->expenses()
            ->whereIn('status', [ExpenseStatus::Approved, ExpenseStatus::Paid])
            ->sum('amount');

        // 3. Worker Costs (from quotation/assigned packages if any)
        $totalWorkerCosts = 0;

        // 4. Gross Margin & Profit
        $grossProfit = $totalPaidRevenue - ($totalApprovedExpenses + $totalWorkerCosts);
        $marginPercentage = $totalPaidRevenue > 0
            ? round(($grossProfit / $totalPaidRevenue) * 100, 2)
            : 0.0;

        return [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'total_invoiced' => $totalInvoiced,
            'total_paid_revenue' => $totalPaidRevenue,
            'total_approved_expenses' => $totalApprovedExpenses,
            'total_worker_costs' => $totalWorkerCosts,
            'gross_profit' => $grossProfit,
            'margin_percentage' => $marginPercentage,
            'currency' => 'IDR',
        ];
    }
}
