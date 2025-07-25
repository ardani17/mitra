<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseApprovalController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\BillingBatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/documentation', function () {
        return view('documentation');
    })->name('documentation');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Company routes
    Route::resource('companies', CompanyController::class);
    
    // Project routes
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::get('/projects-export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects-template', [ProjectController::class, 'downloadTemplate'])->name('projects.template');
    Route::get('/projects-import', [ProjectController::class, 'importForm'])->name('projects.import.form');
    Route::post('/projects-import', [ProjectController::class, 'import'])->name('projects.import');
    
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
    
    // Document routes
    Route::get('/documents', [App\Http\Controllers\ProjectDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [App\Http\Controllers\ProjectDocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [App\Http\Controllers\ProjectDocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/download', [App\Http\Controllers\ProjectDocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [App\Http\Controllers\ProjectDocumentController::class, 'destroy'])->name('documents.destroy');
});

require __DIR__.'/auth.php';
