<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Policies\ProjectPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\BillingPolicy;
use App\Observers\ProjectBillingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectExpense::class, ExpensePolicy::class);
        Gate::policy(ProjectBilling::class, BillingPolicy::class);
        
        // Register observers
        ProjectBilling::observe(ProjectBillingObserver::class);
    }
}
