<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileExplorerController;

// Download ZIP route - using web middleware for form submission
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/projects/{project}/download-folder-zip', [FileExplorerController::class, 'downloadFolderAsZip'])
        ->name('projects.download-folder-zip');
});