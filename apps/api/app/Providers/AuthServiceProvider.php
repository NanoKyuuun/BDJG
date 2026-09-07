<?php

namespace App\Providers;

use App\Domains\Billing\Models\Invoice as DomainInvoice;
use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client as DomainClient;
use App\Domains\Commercial\Models\Quotation as DomainQuotation;
use App\Domains\CRM\Models\Inquiry as DomainInquiry;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Delivery\Policies\DeliveryPackagePolicy;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Finance\Policies\WorkerExpensePolicy;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Policies\MediaAssetPolicy;
use App\Domains\Payments\Models\PaymentTransaction as DomainPaymentTransaction;
use App\Domains\Projects\Models\Project as DomainProject;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Policies\RevisionPolicy;
use App\Domains\Schedules\Models\Schedule as DomainSchedule;
use App\Domains\Tasks\Models\Task as DomainTask;
use App\Domains\Workers\Models\ProjectAssignment as DomainProjectAssignment;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\Quotation;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use App\Policies\AddOnPolicy;
use App\Policies\ClientPolicy;
use App\Policies\InquiryPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PackagePolicy;
use App\Policies\PaymentTransactionPolicy;
use App\Policies\ProjectAssignmentPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ServicePolicy;
use App\Policies\TaskPolicy;
use App\Policies\WorkerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Catalog
        Service::class => ServicePolicy::class,
        Package::class => PackagePolicy::class,
        AddOn::class => AddOnPolicy::class,

        // Orders
        \App\Domains\Orders\Models\ServiceOrder::class => \App\Domains\Orders\Policies\ServiceOrderPolicy::class,

        // Clients
        DomainClient::class => ClientPolicy::class,
        Client::class => ClientPolicy::class,

        // CRM & Commercial
        DomainInquiry::class => InquiryPolicy::class,
        Inquiry::class => InquiryPolicy::class,
        DomainQuotation::class => QuotationPolicy::class,
        Quotation::class => QuotationPolicy::class,

        // Billing & Payments
        DomainInvoice::class => InvoicePolicy::class,
        Invoice::class => InvoicePolicy::class,
        DomainPaymentTransaction::class => PaymentTransactionPolicy::class,
        PaymentTransaction::class => PaymentTransactionPolicy::class,

        // Projects
        DomainProject::class => ProjectPolicy::class,
        Project::class => ProjectPolicy::class,

        // Workers & Assignments
        WorkerProfile::class => WorkerPolicy::class,
        DomainProjectAssignment::class => ProjectAssignmentPolicy::class,
        ProjectAssignment::class => ProjectAssignmentPolicy::class,

        // Tasks & Schedules
        DomainTask::class => TaskPolicy::class,
        Task::class => TaskPolicy::class,
        DomainSchedule::class => SchedulePolicy::class,
        Schedule::class => SchedulePolicy::class,

        // Revisions, Media, Delivery & Finance
        Revision::class => RevisionPolicy::class,
        MediaAsset::class => MediaAssetPolicy::class,
        DeliveryPackage::class => DeliveryPackagePolicy::class,
        WorkerExpense::class => WorkerExpensePolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('OWNER') ? true : null;
        });
    }
}
