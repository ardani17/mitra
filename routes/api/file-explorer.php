<?php

use App\Http\Controllers\Api\FileExplorerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| File Explorer API Routes
|--------------------------------------------------------------------------
|
| API routes for the file explorer and document management system
|
*/

Route::prefix('file-explorer')->middleware(['web', 'auth'])->group(function () {
    
    Route::prefix('project/{project}')->group(function () {
        
        // Folder structure and navigation
        Route::get('/folders', [FileExplorerController::class, 'getFolderStructure'])
            ->name('api.projects.folders');
        
        Route::get('/folders/contents', [FileExplorerController::class, 'getFolderContents'])
            ->name('api.projects.folders.contents');
        
        Route::post('/folders/create', [FileExplorerController::class, 'createFolder'])
            ->name('api.projects.folders.create');
        
        Route::put('/folders/{folderPath}/rename', [FileExplorerController::class, 'renameFolder'])
            ->where('folderPath', '.*')
            ->name('api.projects.folders.rename');
        
        Route::delete('/folders/{folderPath}', [FileExplorerController::class, 'deleteFolder'])
            ->where('folderPath', '.*')
            ->name('api.projects.folders.delete');
        
        // Download folder as ZIP
        Route::post('/folders/download-zip', [FileExplorerController::class, 'downloadFolderAsZip'])
            ->name('api.projects.folders.download-zip');
        
        // Document operations
        Route::post('/documents/upload', [FileExplorerController::class, 'uploadDocument'])
            ->name('api.projects.documents.upload');
        
        // Download by path (for files from folder structure) - using GET with query param
        Route::get('/documents/download-by-path', [FileExplorerController::class, 'downloadDocumentByPath'])
            ->name('api.projects.documents.download-by-path');
        
        // Delete by path (for files from folder structure)
        Route::post('/documents/delete-by-path', [FileExplorerController::class, 'deleteDocumentByPath'])
            ->name('api.projects.documents.delete-by-path');
        
        Route::get('/documents/{document}/download', function ($projectId, $documentId) {
            $project = \App\Models\Project::findOrFail($projectId);
            $document = \App\Models\ProjectDocument::findOrFail($documentId);
            
            // Check if document belongs to project
            if ($document->project_id !== $project->id) {
                abort(404, 'Document not found in this project');
            }
            
            // Check permission
            if (auth()->check() && !auth()->user()->can('view', $project)) {
                abort(403);
            }
            
            // Check if file exists
            if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($document->file_path)) {
                abort(404, 'File not found');
            }
            
            return \Illuminate\Support\Facades\Storage::disk('local')->download(
                $document->file_path,
                $document->original_name ?? $document->name
            );
        })->name('api.projects.documents.download');
        
        Route::delete('/documents/{document}', [FileExplorerController::class, 'deleteDocument'])
            ->name('api.projects.documents.delete');
        
        Route::put('/documents/{document}/move', [FileExplorerController::class, 'moveDocument'])
            ->name('api.projects.documents.move');
        
        Route::put('/documents/{document}/rename', [FileExplorerController::class, 'renameDocument'])
            ->name('api.projects.documents.rename');
        
        // Sync operations
        Route::post('/sync', [FileExplorerController::class, 'syncProject'])
            ->name('api.projects.sync');
        
        Route::get('/sync/status', [FileExplorerController::class, 'getSyncStatus'])
            ->name('api.projects.sync.status');
        
        // Storage-Database sync operations
        Route::get('/check-sync', [FileExplorerController::class, 'checkSyncStatus'])
            ->name('api.projects.check-sync');
        
        Route::post('/sync-storage-db', [FileExplorerController::class, 'performStorageDatabaseSync'])
            ->name('api.projects.sync-storage-db');
    });
});

// Additional document routes that don't require project context
Route::prefix('documents')->group(function () {
    
    // Download document
    Route::get('/{document}/download', function ($documentId) {
        $document = \App\Models\ProjectDocument::findOrFail($documentId);
        
        // Check permission
        if (!auth()->user()->can('view', $document->project)) {
            abort(403);
        }
        
        if (!\Illuminate\Support\Facades\Storage::exists($document->file_path)) {
            abort(404, 'File not found');
        }
        
        return \Illuminate\Support\Facades\Storage::download(
            $document->file_path,
            $document->name
        );
    })->name('api.documents.download');
    
    // Preview document
    Route::get('/{document}/preview', function ($documentId) {
        $document = \App\Models\ProjectDocument::findOrFail($documentId);
        
        // Check permission
        if (!auth()->user()->can('view', $document->project)) {
            abort(403);
        }
        
        if (!\Illuminate\Support\Facades\Storage::exists($document->file_path)) {
            abort(404, 'File not found');
        }
        
        $mimeType = \Illuminate\Support\Facades\Storage::mimeType($document->file_path);
        
        // For images and PDFs, return inline
        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
            return response()->file(
                storage_path('app/' . $document->file_path),
                ['Content-Type' => $mimeType]
            );
        }
        
        // For other files, download
        return \Illuminate\Support\Facades\Storage::download(
            $document->file_path,
            $document->name
        );
    })->name('api.documents.preview');
});