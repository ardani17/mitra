<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseApprovalController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\BillingBatchController;
use App\Http\Controllers\ProjectBillingController;
use App\Http\Controllers\BillingDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeWorkScheduleController;
use App\Http\Controllers\EmployeeCustomOffDayController;
use App\Http\Controllers\DailySalaryController;
use App\Http\Controllers\SalaryReleaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard API routes
Route::middleware('auth')->group(function () {
    Route::get('/api/dashboard/analytics', [DashboardController::class, 'analytics'])->name('api.dashboard.analytics');
    Route::get('/api/dashboard/years', [DashboardController::class, 'getAvailableYears'])->name('api.dashboard.years');
    Route::get('/api/dashboard/project-types', [DashboardController::class, 'getProjectTypes'])->name('api.dashboard.project-types');
    Route::get('/api/dashboard/billing-status', [DashboardController::class, 'getBillingStatus'])->name('api.dashboard.billing-status');
    Route::get('/api/dashboard/locations', [DashboardController::class, 'getLocations'])->name('api.dashboard.locations');
    Route::get('/api/dashboard/clients', [DashboardController::class, 'getClients'])->name('api.dashboard.clients');
});

Route::middleware('auth')->group(function () {
    Route::get('/documentation', function () {
        return view('documentation');
    })->name('documentation');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/company', [ProfileController::class, 'updateCompany'])->name('profile.company.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management routes (Only for Direktur)
    Route::middleware('role:direktur')->group(function () {
        Route::resource('users', UserController::class);
        
        // Settings routes (Only for Direktur)
        Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::patch('/settings/director-bypass', [App\Http\Controllers\SettingController::class, 'updateDirectorBypass'])->name('settings.update-director-bypass');
        Route::patch('/settings/notification', [App\Http\Controllers\SettingController::class, 'updateNotificationSetting'])->name('settings.update-notification');
        Route::patch('/settings/threshold', [App\Http\Controllers\SettingController::class, 'updateHighAmountThreshold'])->name('settings.update-threshold');
        Route::patch('/settings/salary-cutoff', [App\Http\Controllers\SettingController::class, 'updateSalaryCutoff'])->name('settings.update-salary-cutoff');
        Route::patch('/settings/reset-default', [App\Http\Controllers\SettingController::class, 'resetToDefault'])->name('settings.reset-default');
        Route::get('/api/settings', [App\Http\Controllers\SettingController::class, 'getSettings'])->name('api.settings');
        
        // System Statistics routes (Only for Direktur)
        Route::get('/system-statistics', [App\Http\Controllers\SystemStatisticsController::class, 'index'])->name('system-statistics.index');
        Route::get('/api/system-statistics/metrics', [App\Http\Controllers\SystemStatisticsController::class, 'metrics'])->name('api.system-statistics.metrics');
        Route::get('/system-statistics/export', [App\Http\Controllers\SystemStatisticsController::class, 'export'])->name('system-statistics.export');
        Route::post('/api/system-statistics/clear-cache', [App\Http\Controllers\SystemStatisticsController::class, 'clearCache'])->name('api.system-statistics.clear-cache');
        
        // Debug route for system statistics
        Route::get('/system-statistics/debug', function() {
            $debugService = new \App\Services\SystemStatisticsDebugService();
            return response()->json([
                'system_access' => $debugService->debugSystemAccess(),
                'cpu_methods' => $debugService->testCpuMethods(),
                'memory_methods' => $debugService->testMemoryMethods(),
            ]);
        })->name('system-statistics.debug');
    });
    
    // Company routes
    Route::resource('companies', CompanyController::class);
    
    // Project routes
    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{id}/confirm-delete', [ProjectController::class, 'confirmDelete'])->name('projects.confirm-delete');
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::get('/projects-export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects-template', [ProjectController::class, 'downloadTemplate'])->name('projects.template');
    Route::get('/projects-import', [ProjectController::class, 'importForm'])->name('projects.import.form');
    Route::post('/projects-import', [ProjectController::class, 'import'])->name('projects.import');
    Route::post('/projects-import-confirm', [ProjectController::class, 'importConfirm'])->name('projects.import.confirm');
    
    // API routes untuk autocomplete lokasi
    Route::get('/api/projects/locations/search', [ProjectController::class, 'searchLocations'])->name('api.projects.locations.search');
    Route::get('/api/projects/locations/popular', [ProjectController::class, 'getPopularLocations'])->name('api.projects.locations.popular');
    
    // API routes untuk autocomplete client
    Route::get('/api/projects/clients/search', [ProjectController::class, 'searchClients'])->name('api.projects.clients.search');
    Route::get('/api/projects/clients/popular', [ProjectController::class, 'getPopularClients'])->name('api.projects.clients.popular');
    
    // API routes untuk autocomplete proyek
    Route::get('/api/projects/search', [ProjectController::class, 'searchProjects'])->name('api.projects.search');
    Route::get('/api/projects/popular', [ProjectController::class, 'getPopularProjects'])->name('api.projects.popular');
    
    // Expense routes
    Route::resource('expenses', ExpenseController::class);
    Route::post('/expenses/{id}/submit', [ExpenseController::class, 'submitForApproval'])->name('expenses.submit');
    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::get('/expenses-export', [ExpenseController::class, 'export'])->name('expenses.export');
    
    // Expense Approval routes
    Route::get('/expense-approvals', [ExpenseApprovalController::class, 'index'])->name('expense-approvals.index');
    Route::get('/expense-approvals/{approval}', [ExpenseApprovalController::class, 'show'])->name('expense-approvals.show');
    Route::post('/expense-approvals/{approval}/process', [ExpenseApprovalController::class, 'process'])->name('expense-approvals.process');
    
    // Timeline routes
    Route::resource('timelines', TimelineController::class);
    Route::post('/timelines/{id}/status', [TimelineController::class, 'updateStatus'])->name('timelines.update-status');
    
    // Billing Batch routes (New Primary Billing System)
    Route::resource('billing-batches', BillingBatchController::class);
    Route::post('/billing-batches/{billingBatch}/update-status', [BillingBatchController::class, 'updateStatus'])->name('billing-batches.update-status');
    Route::post('/billing-batches/{billingBatch}/upload-document', [BillingBatchController::class, 'uploadDocument'])->name('billing-batches.upload-document');
    Route::delete('/billing-batches/{billingBatch}/documents/{document}', [BillingBatchController::class, 'deleteDocument'])->name('billing-batches.delete-document');
    Route::get('/billing-batches/{billingBatch}/confirm-delete', [BillingBatchController::class, 'confirmDelete'])->name('billing-batches.confirm-delete');
    
    // Project Billing routes (Per-Project Billing with Termin Support)
    Route::resource('project-billings', ProjectBillingController::class);
    Route::get('/projects/{project}/manage-schedule', [ProjectBillingController::class, 'manageSchedule'])->name('project-billings.manage-schedule');
    Route::post('/projects/{project}/store-schedule', [ProjectBillingController::class, 'storeSchedule'])->name('project-billings.store-schedule');
    Route::get('/api/projects/{project}/schedules', [ProjectBillingController::class, 'getProjectSchedules'])->name('api.project-billings.schedules');
    Route::get('/api/projects/{project}/billings', [ProjectBillingController::class, 'getProjectBillings'])->name('api.project-billings.project-billings');
    
    // Termin Payment routes
    Route::post('/schedules/{schedule}/create-billing', [ProjectBillingController::class, 'createTerminPayment'])->name('project-billings.create-termin');
    Route::post('/projects/{project}/generate-schedule', [ProjectBillingController::class, 'generatePaymentSchedule'])->name('project-billings.generate-schedule');
    Route::patch('/project-billings/{projectBilling}/update-termin-status', [ProjectBillingController::class, 'updateTerminStatus'])->name('project-billings.update-termin-status');
    Route::post('/project-billings/bulk-update-termin', [ProjectBillingController::class, 'bulkUpdateTermin'])->name('project-billings.bulk-update-termin');
    
    // Project Payment Schedule routes - Commented out until controller is created
    // Route::resource('project-payment-schedules', ProjectPaymentScheduleController::class);
    // Route::post('project-payment-schedules/bulk-create', [ProjectPaymentScheduleController::class, 'bulkCreateSchedule'])->name('project-payment-schedules.bulk-create');
    // Route::patch('project-payment-schedules/adjust/{project}', [ProjectPaymentScheduleController::class, 'adjustSchedule'])->name('project-payment-schedules.adjust');
    // Route::get('project-payment-schedules/export', [ProjectPaymentScheduleController::class, 'export'])->name('project-payment-schedules.export');
    
    // Billing Dashboard
    Route::get('/billing-dashboard', [BillingDashboardController::class, 'index'])->name('billing-dashboard.index');
    Route::get('/billing-dashboard/export', [BillingDashboardController::class, 'export'])->name('billing-dashboard.export');
    Route::get('/api/billing-dashboard/data', [BillingDashboardController::class, 'getData'])->name('api.billing-dashboard.data');
    Route::post('/api/billing-dashboard/clear-cache', [BillingDashboardController::class, 'clearCache'])->name('api.billing-dashboard.clear-cache');
    Route::post('/api/billing-dashboard/preferences', [BillingDashboardController::class, 'savePreferences'])->name('api.billing-dashboard.save-preferences');
    Route::get('/api/billing-dashboard/preferences', [BillingDashboardController::class, 'getPreferences'])->name('api.billing-dashboard.get-preferences');
    
    // Redirect old billing routes to new batch system
    Route::get('/penagihan', function() {
        return redirect()->route('billing-batches.index');
    })->name('penagihan.index');
    Route::get('/penagihan/create', function() {
        return redirect()->route('billing-batches.create');
    })->name('penagihan.create');
    
    // Redirect old billings routes to new batch system
    Route::get('/billings', function() {
        return redirect()->route('billing-batches.index');
    })->name('billings.index');
    Route::get('/billings/create', function() {
        return redirect()->route('billing-batches.create');
    })->name('billings.create');
    Route::get('/billings/{id}', function($id) {
        return redirect()->route('billing-batches.index');
    })->name('billings.show');
    Route::get('/billings/{id}/edit', function($id) {
        return redirect()->route('billing-batches.index');
    })->name('billings.edit');
    
    // Report routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability');
    Route::get('/reports/export-financial', [ReportController::class, 'exportFinancial'])->name('reports.export.financial');
    
    // Activity Report routes
    Route::get('/reports/activities', [App\Http\Controllers\ActivityReportController::class, 'index'])->name('reports.activities');
    Route::get('/reports/activities/{id}', [App\Http\Controllers\ActivityReportController::class, 'show'])->name('reports.activities.show');
    Route::get('/api/activities/recent', [App\Http\Controllers\ActivityReportController::class, 'recent'])->name('api.activities.recent');
    Route::get('/api/activities/statistics', [App\Http\Controllers\ActivityReportController::class, 'statistics'])->name('api.activities.statistics');
    
    // Document routes
    Route::get('/documents', [App\Http\Controllers\ProjectDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [App\Http\Controllers\ProjectDocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [App\Http\Controllers\ProjectDocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/download', [App\Http\Controllers\ProjectDocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [App\Http\Controllers\ProjectDocumentController::class, 'destroy'])->name('documents.destroy');
    
    // Excel Export/Import routes
    Route::prefix('excel')->name('excel.')->group(function () {
        Route::get('/', [App\Http\Controllers\ExcelController::class, 'index'])->name('index');
        Route::get('/export/{type}', [App\Http\Controllers\ExcelController::class, 'export'])->name('export');
        Route::post('/import/{type}', [App\Http\Controllers\ExcelController::class, 'import'])->name('import');
        Route::get('/template/{type}', [App\Http\Controllers\ExcelController::class, 'downloadTemplate'])->name('template');
        Route::get('/import-logs', [App\Http\Controllers\ExcelController::class, 'importLogs'])->name('import-logs');
        Route::get('/import-logs/{id}', [App\Http\Controllers\ExcelController::class, 'importLogDetail'])->name('import-log-detail');
    });

    // Financial Management Routes (Only for Finance Manager and Direktur)
    Route::middleware('role:direktur,finance_manager')->prefix('finance')->name('finance.')->group(function () {
        // Finance Dashboard
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [FinanceDashboardController::class, 'export'])->name('dashboard.export');
        
        // Cashflow Journal
        Route::resource('cashflow', CashflowController::class);
        Route::get('/cashflow-income', [CashflowController::class, 'income'])->name('cashflow.income');
        Route::get('/cashflow-expense', [CashflowController::class, 'expense'])->name('cashflow.expense');
        Route::post('/cashflow/bulk-action', [CashflowController::class, 'bulkAction'])->name('cashflow.bulk-action');
        Route::get('/cashflow-export', [CashflowController::class, 'export'])->name('cashflow.export');
        Route::post('/cashflow-import', [CashflowController::class, 'import'])->name('cashflow.import');
        Route::get('/cashflow-template', [CashflowController::class, 'downloadTemplate'])->name('cashflow.template');
        
        // Cashflow Category Management
        Route::resource('cashflow-categories', App\Http\Controllers\CashflowCategoryController::class);
        Route::post('/cashflow-categories/{cashflowCategory}/toggle', [App\Http\Controllers\CashflowCategoryController::class, 'toggle'])->name('cashflow-categories.toggle');
        Route::post('/cashflow-categories/bulk-update', [App\Http\Controllers\CashflowCategoryController::class, 'bulkUpdate'])->name('cashflow-categories.bulk-update');
        Route::get('/cashflow-categories-export', [App\Http\Controllers\CashflowCategoryController::class, 'export'])->name('cashflow-categories.export');
        Route::post('/cashflow-categories-import', [App\Http\Controllers\CashflowCategoryController::class, 'import'])->name('cashflow-categories.import');
        Route::get('/cashflow-categories-template', [App\Http\Controllers\CashflowCategoryController::class, 'downloadTemplate'])->name('cashflow-categories.template');
        
        // API endpoints for finance dashboard
        Route::get('/api/dashboard-data', [FinanceDashboardController::class, 'getDashboardData'])->name('api.dashboard-data');
        Route::get('/api/cashflow-chart', [FinanceDashboardController::class, 'getCashflowChart'])->name('api.cashflow-chart');
        Route::get('/dashboard/summary', [FinanceDashboardController::class, 'getSummary'])->name('dashboard.summary');
        Route::get('/dashboard/categories', [FinanceDashboardController::class, 'getCategories'])->name('dashboard.categories');
        
        // Financial Reports
        Route::get('/reports/cashflow', [FinanceDashboardController::class, 'cashflowReport'])->name('reports.cashflow');
        Route::get('/reports/balance-summary', [FinanceDashboardController::class, 'balanceSummary'])->name('reports.balance-summary');
        
        // Employee Management
        Route::get('/employees-dashboard', [EmployeeController::class, 'dashboard'])->name('employees.dashboard');
        Route::resource('employees', EmployeeController::class);
        Route::get('/employees/{employee}/salary-summary', [EmployeeController::class, 'salarySummary'])->name('employees.salary-summary');
        Route::get('/employees-export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::get('/employees-analytics', [EmployeeController::class, 'analytics'])->name('employees.analytics');
        Route::get('/employees-reports', [EmployeeController::class, 'reports'])->name('employees.reports');
        
        // Employee Documents
        Route::get('/employees/{employee}/documents', [EmployeeController::class, 'documents'])->name('employees.documents');
        Route::post('/employees/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('employees.documents.upload');
        Route::delete('/employees/{employee}/documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('employees.documents.delete');
        
        // Employee Off Days Management (Simplified - no work schedules)
        Route::get('/employees/{employee}/custom-off-days', [EmployeeCustomOffDayController::class, 'index'])->name('employees.custom-off-days.index');
        Route::get('/employees/{employee}/custom-off-days/create', [EmployeeCustomOffDayController::class, 'create'])->name('employees.custom-off-days.create');
        Route::post('/employees/{employee}/custom-off-days', [EmployeeCustomOffDayController::class, 'store'])->name('employees.custom-off-days.store');
        Route::get('/employees/{employee}/custom-off-days/{customOffDay}', [EmployeeCustomOffDayController::class, 'show'])->name('employees.custom-off-days.show');
        Route::get('/employees/{employee}/custom-off-days/{customOffDay}/edit', [EmployeeCustomOffDayController::class, 'edit'])->name('employees.custom-off-days.edit');
        Route::put('/employees/{employee}/custom-off-days/{customOffDay}', [EmployeeCustomOffDayController::class, 'update'])->name('employees.custom-off-days.update');
        Route::delete('/employees/{employee}/custom-off-days/{customOffDay}', [EmployeeCustomOffDayController::class, 'destroy'])->name('employees.custom-off-days.destroy');
        Route::get('/employees/{employee}/custom-off-days-calendar', [EmployeeCustomOffDayController::class, 'calendar'])->name('employees.custom-off-days.calendar');
        Route::post('/employees/{employee}/custom-off-days/quick-add', [EmployeeCustomOffDayController::class, 'quickAdd'])->name('employees.custom-off-days.quick-add');
        Route::post('/employees/{employee}/custom-off-days/quick-remove', [EmployeeCustomOffDayController::class, 'quickRemove'])->name('employees.custom-off-days.quick-remove');
        Route::delete('/employees/{employee}/custom-off-days-bulk', [EmployeeCustomOffDayController::class, 'bulkDelete'])->name('employees.custom-off-days.bulk-delete');
        
        // Attendance Status Management
        Route::post('/employees/{employee}/attendance-status', [EmployeeCustomOffDayController::class, 'updateAttendanceStatus'])->name('employees.attendance-status.update');
        Route::get('/employees/{employee}/attendance-status', [EmployeeCustomOffDayController::class, 'getAttendanceStatus'])->name('employees.attendance-status.get');
        Route::delete('/employees/{employee}/attendance-status', [EmployeeCustomOffDayController::class, 'deleteAttendanceStatus'])->name('employees.attendance-status.delete');
        
        // Employee Daily Salary Management (moved from separate menu)
        Route::post('/employees/{employee}/daily-salaries', [DailySalaryController::class, 'store'])->name('employees.daily-salaries.store');
        Route::put('/employees/{employee}/daily-salaries/{dailySalary}', [DailySalaryController::class, 'update'])->name('employees.daily-salaries.update');
        Route::delete('/employees/{employee}/daily-salaries/{dailySalary}', [DailySalaryController::class, 'destroy'])->name('employees.daily-salaries.destroy');
        Route::get('/employees/{employee}/daily-salaries/{dailySalary}', [DailySalaryController::class, 'show'])->name('employees.daily-salaries.show');
        
        // Employee Salary Release Management (moved from separate menu)
        Route::post('/employees/{employee}/salary-releases', [SalaryReleaseController::class, 'store'])->name('employees.salary-releases.store');
        Route::post('/employees/{employee}/salary-releases/{salaryRelease}/release', [SalaryReleaseController::class, 'release'])->name('employees.salary-releases.release');
        Route::post('/employees/{employee}/salary-releases/{salaryRelease}/mark-as-paid', [SalaryReleaseController::class, 'markAsPaid'])->name('employees.salary-releases.mark-as-paid');
        Route::delete('/employees/{employee}/salary-releases/{salaryRelease}', [SalaryReleaseController::class, 'destroy'])->name('employees.salary-releases.destroy');
        
        // API endpoints
        Route::get('/api/employees/{employee}/rate', [DailySalaryController::class, 'getEmployeeRate'])->name('api.employees.rate');
        Route::get('/api/employees/{employee}/unreleased-salaries', [SalaryReleaseController::class, 'getUnreleasedSalaries'])->name('api.employees.unreleased-salaries');
        Route::get('/api/employees/{employee}/salary-releases', [SalaryReleaseController::class, 'getEmployeeSalaryReleases'])->name('api.employees.salary-releases');
        
        
        // Redirect old routes to employee management
        Route::get('/daily-salaries', function() {
            return redirect()->route('finance.employees.index')->with('info', 'Pengelolaan gaji harian telah dipindahkan ke halaman detail karyawan.');
        })->name('daily-salaries.index');
        
        Route::get('/daily-salaries/create', function() {
            return redirect()->route('finance.employees.index')->with('info', 'Silakan pilih karyawan terlebih dahulu untuk menambah gaji harian.');
        })->name('daily-salaries.create');
        
        Route::get('/salary-releases', function() {
            return redirect()->route('finance.employees.index')->with('info', 'Pengelolaan rilis gaji telah dipindahkan ke halaman detail karyawan.');
        })->name('salary-releases.index');
        
        Route::get('/salary-releases/create', function() {
            return redirect()->route('finance.employees.index')->with('info', 'Silakan pilih karyawan terlebih dahulu untuk membuat rilis gaji.');
        })->name('salary-releases.create');
        
        // Keep some existing routes for backward compatibility
        Route::resource('daily-salaries', DailySalaryController::class)->except(['index', 'create']);
        Route::get('/daily-salaries-calendar', [DailySalaryController::class, 'calendar'])->name('daily-salaries.calendar');
        Route::post('/daily-salaries/bulk-confirm', [DailySalaryController::class, 'bulkConfirm'])->name('daily-salaries.bulk-confirm');
        
        Route::resource('salary-releases', SalaryReleaseController::class)->except(['index', 'create', 'destroy']);
        Route::delete('/salary-releases/{salary_release}', [SalaryReleaseController::class, 'destroyDirect'])->name('salary-releases.destroy');
        Route::get('/salary-releases/{salary_release}/print', [SalaryReleaseController::class, 'print'])->name('salary-releases.print');
        Route::post('/salary-releases/{salaryRelease}/release', [SalaryReleaseController::class, 'release'])->name('salary-releases.release');
        Route::post('/salary-releases/{salaryRelease}/mark-as-paid', [SalaryReleaseController::class, 'markAsPaid'])->name('salary-releases.mark-as-paid');
        Route::post('/salary-releases/{salaryRelease}/revert-to-draft', [SalaryReleaseController::class, 'revertToDraft'])->name('salary-releases.revert-to-draft');
        Route::get('/api/salary-releases/unreleased-salaries', [SalaryReleaseController::class, 'getUnreleasedSalaries'])->name('salary-releases.get-unreleased-salaries');
        
    });
});

// Include expense modification routes
require __DIR__.'/expense-modifications.php';

require __DIR__.'/auth.php';

// Include test routes (only in development)
if (app()->environment('local', 'development')) {
    require __DIR__.'/web-test.php';
    
    // Temporary ZIP download debugging routes
    require __DIR__.'/test-zip.php';
}

// Include download routes
require __DIR__.'/web-download.php';
