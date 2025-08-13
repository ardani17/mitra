<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\ProjectDocument;
use App\Models\ProjectTimeline;
use App\Models\ProjectPaymentSchedule;
use App\Models\BillingBatch;
use App\Models\Employee;
use App\Policies\ProjectPolicy;
use App\Policies\ProjectExpensePolicy;
use App\Policies\BillingPolicy;
use App\Policies\ProjectBillingPolicy;
use App\Policies\ProjectPaymentSchedulePolicy;
use App\Policies\BillingBatchPolicy;
use App\Policies\ProjectDocumentPolicy;
use App\Policies\ProjectTimelinePolicy;
use App\Policies\EmployeePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        ProjectExpense::class => ProjectExpensePolicy::class,
        ProjectBilling::class => ProjectBillingPolicy::class,
        ProjectPaymentSchedule::class => ProjectPaymentSchedulePolicy::class,
        BillingBatch::class => BillingBatchPolicy::class,
        ProjectDocument::class => ProjectDocumentPolicy::class,
        ProjectTimeline::class => ProjectTimelinePolicy::class,
        Employee::class => EmployeePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
