<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectBilling;
use App\Models\Employee;
use App\Models\DailySalary;
use App\Models\SalaryRelease;
use App\Models\ExpenseModificationApproval;
use App\Policies\ProjectPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\BillingPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\DailySalaryPolicy;
use App\Policies\SalaryReleasePolicy;
use App\Policies\ExpenseModificationPolicy;
use App\Observers\ProjectBillingObserver;
use App\Observers\ProjectExpenseObserver;
use App\Observers\SalaryReleaseObserver;

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
        // Set default pagination views
        Paginator::defaultView('vendor.pagination.responsive-tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-responsive-tailwind');
        
        // Register policies
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(ProjectExpense::class, ExpensePolicy::class);
        Gate::policy(ProjectBilling::class, BillingPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(DailySalary::class, DailySalaryPolicy::class);
        Gate::policy(SalaryRelease::class, SalaryReleasePolicy::class);
        Gate::policy(ExpenseModificationApproval::class, ExpenseModificationPolicy::class);
        
        // Register observers
        ProjectBilling::observe(ProjectBillingObserver::class);
        ProjectExpense::observe(ProjectExpenseObserver::class);
        SalaryRelease::observe(SalaryReleaseObserver::class);
        
        // Ensure roles are loaded for authenticated users in views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                if (!$user->relationLoaded('roles')) {
                    $user->load('roles');
                }
            }
        });
    }
}
