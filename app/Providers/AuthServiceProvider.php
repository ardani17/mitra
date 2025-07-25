<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\ProjectDocument;
use App\Models\ProjectTimeline;
use App\Models\BillingBatch;
use App\Policies\ProjectPolicy;
use App\Policies\ProjectExpensePolicy;
use App\Policies\BillingPolicy;
use App\Policies\BillingBatchPolicy;
use App\Policies\ProjectDocumentPolicy;
use App\Policies\ProjectTimelinePolicy;
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
        ProjectBilling::class => BillingPolicy::class,
        BillingBatch::class => BillingBatchPolicy::class,
        ProjectDocument::class => ProjectDocumentPolicy::class,
        ProjectTimeline::class => ProjectTimelinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
