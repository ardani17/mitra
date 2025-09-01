<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectBillingController;
use App\Http\Controllers\ProjectPaymentScheduleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Telegram Bot Webhook (no auth required for webhook)
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook')
    ->withoutMiddleware(['web', 'auth']);

// Include File Explorer API routes
require __DIR__ . '/api/file-explorer.php';

// API routes untuk Project Billing dan Payment Schedule
Route::middleware(['web', 'auth'])->group(function () {
    // Project Billing API endpoints
    Route::get('/project-billings/search', [ProjectBillingController::class, 'search'])->name('api.project-billings.search');
    Route::get('/project-billings/stats', [ProjectBillingController::class, 'getStats'])->name('api.project-billings.stats');
    Route::get('/projects/{project}/billings', [ProjectBillingController::class, 'getProjectBillings'])->name('api.projects.billings');
    
    // Payment Schedule API endpoints
    Route::get('/payment-schedules/search', [ProjectPaymentScheduleController::class, 'search'])->name('api.payment-schedules.search');
    Route::get('/project-payment-schedules/stats', [ProjectPaymentScheduleController::class, 'getStats'])->name('api.project-payment-schedules.stats');
    Route::get('/payment-schedules/upcoming', [ProjectPaymentScheduleController::class, 'getUpcoming'])->name('api.payment-schedules.upcoming');
    Route::get('/payment-schedules/overdue', [ProjectPaymentScheduleController::class, 'getOverdue'])->name('api.payment-schedules.overdue');
    Route::get('/projects/{project}/schedules', [ProjectPaymentScheduleController::class, 'getProjectSchedules'])->name('api.projects.schedules');
    Route::get('/projects/{project}/payment-schedules', [ProjectPaymentScheduleController::class, 'getProjectSchedules'])->name('api.projects.payment-schedules');
    
    // Project API endpoints (yang sudah ada di web.php tapi lebih baik dipindahkan ke api.php)
    Route::get('/projects/locations/search', [ProjectController::class, 'searchLocations'])->name('api.projects.locations.search');
    Route::get('/projects/locations/popular', [ProjectController::class, 'getPopularLocations'])->name('api.projects.locations.popular');
    Route::get('/projects/clients/search', [ProjectController::class, 'searchClients'])->name('api.projects.clients.search');
    Route::get('/projects/clients/popular', [ProjectController::class, 'getPopularClients'])->name('api.projects.clients.popular');
});