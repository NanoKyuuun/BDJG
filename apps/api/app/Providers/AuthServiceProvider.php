<?php

namespace App\Providers;

use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
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
        Service::class => ServicePolicy::class,
        Package::class => PackagePolicy::class,
        AddOn::class => AddOnPolicy::class,
        Client::class => ClientPolicy::class,
        Inquiry::class => InquiryPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Invoice::class => InvoicePolicy::class,
        PaymentTransaction::class => PaymentTransactionPolicy::class,
        Project::class => ProjectPolicy::class,
        WorkerProfile::class => WorkerPolicy::class,
        User::class => WorkerPolicy::class,
        ProjectAssignment::class => ProjectAssignmentPolicy::class,
        Task::class => TaskPolicy::class,
        Schedule::class => SchedulePolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('OWNER') ? true : null;
        });
    }
}
