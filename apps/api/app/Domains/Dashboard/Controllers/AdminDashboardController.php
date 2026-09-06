<?php

namespace App\Domains\Dashboard\Controllers;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Resources\AdminProjectResource;
use App\Domains\Schedules\Enums\ScheduleType;
use App\Domains\Schedules\Models\Schedule;
use App\Domains\Schedules\Resources\ScheduleResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('OWNER') && ! $user->hasRole('ADMIN')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $newInquiriesCount = Inquiry::where('status', InquiryStatus::New)->count();
        $quotationsWaitingCount = Quotation::where('status', QuotationStatus::Sent)->count();
        $quotationsWonCount = Quotation::where('status', QuotationStatus::Accepted)->count();

        $activeProjectsCount = Project::whereNotIn('status', [
            ProjectStatus::Draft,
            ProjectStatus::Completed,
            ProjectStatus::Archived,
            ProjectStatus::Cancelled,
        ])->count();

        $invoicesDueCount = Invoice::whereIn('status', [
            InvoiceStatus::Issued,
            InvoiceStatus::Overdue,
        ])->count();

        $todayShootsCount = Schedule::where('schedule_type', ScheduleType::Shooting)
            ->whereDate('start_time', today())
            ->count();

        // Finance summary (Owner or Admin with finance permission)
        $cashReceived = (int) Invoice::sum('paid_amount');
        $outstandingAmount = (int) Invoice::whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::Overdue])
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
            ->value('total');

        $recentProjects = Project::with(['client', 'assignments.worker.user'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingSchedules = Schedule::with('project')
            ->where('start_time', '>=', now()->subHours(12))
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'summary' => [
                'new_inquiries' => $newInquiriesCount,
                'quotations_waiting' => $quotationsWaitingCount,
                'quotations_won' => $quotationsWonCount,
                'active_projects' => $activeProjectsCount,
                'invoices_due' => $invoicesDueCount,
                'today_shoots' => $todayShootsCount,
            ],
            'finance' => [
                'cash_received' => $cashReceived,
                'outstanding_amount' => $outstandingAmount,
                'currency' => 'IDR',
            ],
            'recent_projects' => AdminProjectResource::collection($recentProjects),
            'upcoming_schedules' => ScheduleResource::collection($upcomingSchedules),
        ]);
    }
}
