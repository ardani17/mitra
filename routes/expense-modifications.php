<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseModificationController;

/*
|--------------------------------------------------------------------------
| Expense Modification Routes
|--------------------------------------------------------------------------
|
| These routes handle expense modification approval workflows including
| edit and delete requests for approved expenses.
|
*/

Route::middleware(['auth'])->group(function () {
    
    // Expense Modification Management
    Route::prefix('expense-modifications')->name('expense-modifications.')->group(function () {
        
        // List all modification requests
        Route::get('/', [ExpenseModificationController::class, 'index'])->name('index');
        
        // Show specific modification request
        Route::get('/{modification}', [ExpenseModificationController::class, 'show'])->name('show');
        
        // Approve modification request
        Route::post('/{modification}/approve', [ExpenseModificationController::class, 'approve'])->name('approve');
        
        // Reject modification request
        Route::post('/{modification}/reject', [ExpenseModificationController::class, 'reject'])->name('reject');
        
        // Cancel modification request (by requester)
        Route::post('/{modification}/cancel', [ExpenseModificationController::class, 'cancel'])->name('cancel');
        
        // Bulk approve modifications
        Route::post('/bulk/approve', [ExpenseModificationController::class, 'bulkApprove'])->name('bulk-approve');
        
        // Export modification requests
        Route::get('/export/data', [ExpenseModificationController::class, 'export'])->name('export');
    });
    
    // Expense Modification Request Forms
    Route::prefix('expenses/{expense}')->name('expense-modifications.')->group(function () {
        
        // Edit request form
        Route::get('/request-edit', [ExpenseModificationController::class, 'editForm'])->name('edit-form');
        
        // Submit edit request
        Route::post('/request-edit', [ExpenseModificationController::class, 'requestEdit'])->name('request-edit');
        
        // Delete request form
        Route::get('/request-delete', [ExpenseModificationController::class, 'deleteForm'])->name('delete-form');
        
        // Submit delete request
        Route::post('/request-delete', [ExpenseModificationController::class, 'requestDelete'])->name('request-delete');
        
        // Modification history
        Route::get('/modification-history', [ExpenseModificationController::class, 'history'])->name('history');
    });
});