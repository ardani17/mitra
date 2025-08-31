<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectFolder;
use App\Services\StorageService;
use App\Services\SyncService;
use App\Services\StorageDatabaseSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileExplorerController extends Controller
{
    private $storageService;
    private $syncService;
    private $storageDatabaseSyncService;
    private $disk = 'local'; // Eksplisit gunakan disk 'local' untuk konsistensi
    
    public function __construct(
        StorageService $storageService,
        SyncService $syncService,
        StorageDatabaseSyncService $storageDatabaseSyncService
    )
    {
        $this->storageService = $storageService;
        $this->syncService = $syncService;
        $this->storageDatabaseSyncService = $storageDatabaseSyncService;
    }
    
    /**
     * Get folder structure for a project (reads from real filesystem)
     */
    public function getFolderStructure(Project $project): JsonResponse
    {
        try {
            $projectSlug = Str::slug($project->name);
            $projectPath = storage_path("app/proyek/{$projectSlug}");
            
            // Check if project folder exists
            if (!file_exists($projectPath)) {
                // Create base project folder if not exists
                mkdir($projectPath, 0755, true);
            }
            
            // Read real folder structure from filesystem
            $structure = $this->readFolderStructure($projectPath, $projectSlug, $project->id);
            
            // Get sync status
            $syncStatus = $this->syncService->checkSyncStatus($project);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'folders' => $structure,
                    'sync_status' => $syncStatus,
                    'project' => [
                        'id' => $project->id,
                        'name' => $project->name,
                        'slug' => $projectSlug
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting folder structure', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get folder structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Read folder structure from filesystem
     */
    private function readFolderStructure($path, $projectSlug, $projectId, $parentPath = '')
    {
        $name = basename($path);
        $relativePath = $parentPath ? "{$parentPath}/{$name}" : "proyek/{$projectSlug}";
        
        // Build folder object
        $folder = [
            'id' => md5($relativePath), // Generate consistent ID from path
            'name' => $name,
            'path' => $relativePath,
            'type' => $parentPath ? 'folder' : 'root',
            'sync_status' => 'pending',
            'children' => [],
            'documents' => []
        ];
        
        // Read subdirectories
        if (is_dir($path)) {
            $items = scandir($path);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $itemPath = $path . '/' . $item;
                
                if (is_dir($itemPath)) {
                    // Recursively read subdirectory
                    $folder['children'][] = $this->readFolderStructure(
                        $itemPath,
                        $projectSlug,
                        $projectId,
                        $relativePath
                    );
                } else {
                    // Add file as document
                    $folder['documents'][] = [
                        'id' => md5($itemPath),
                        'name' => $item,
                        'type' => pathinfo($item, PATHINFO_EXTENSION),
                        'size' => filesize($itemPath),
                        'size_formatted' => $this->formatBytes(filesize($itemPath)),
                        'sync_status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s', filectime($itemPath)),
                        'path' => "{$relativePath}/{$item}"
                    ];
                }
            }
        }
        
        return $folder;
    }
    
    /**
     * Get documents in a specific folder
     */
    public function getFolderContents(Project $project, Request $request): JsonResponse
    {
        try {
            $folderPath = $request->input('path', '');
            
            // Build query
            $query = ProjectDocument::where('project_id', $project->id);
            
            if ($folderPath) {
                $query->where('file_path', 'like', "%{$folderPath}%");
            }
            
            $documents = $query->get()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'size' => $doc->file_size,
                    'size_formatted' => $this->formatBytes($doc->file_size),
                    'path' => $doc->file_path,
                    'sync_status' => $doc->sync_status,
                    'last_sync_at' => $doc->last_sync_at,
                    'created_at' => $doc->created_at,
                    'updated_at' => $doc->updated_at,
                    'download_url' => "/api/file-explorer/project/{$project->id}/documents/{$doc->id}/download",
                    'preview_url' => "/api/file-explorer/project/{$project->id}/documents/{$doc->id}/preview",
                    'can_preview' => $this->canPreview($doc)
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'documents' => $documents,
                    'path' => $folderPath,
                    'count' => $documents->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting folder contents', [
                'project_id' => $project->id,
                'path' => $request->input('path'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get folder contents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload document to specific folder
     */
    public function uploadDocument(Request $request, Project $project): JsonResponse
    {
        // Support both single and multiple file uploads
        $rules = [
            'folder' => 'required|string',
            'description' => 'nullable|string|max:500'
        ];
        
        // Check if multiple files or single file
        if ($request->hasFile('files')) {
            $rules['files'] = 'required|array';
            $rules['files.*'] = 'file|max:2097152'; // 2GB max per file (2048MB)
        } else {
            $rules['file'] = 'required|file|max:2097152'; // 2GB max (2048MB)
        }
        
        $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $folder = $request->input('folder');
            $description = $request->input('description');
            $uploadedDocuments = [];
            
            // Handle multiple files
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                foreach ($files as $file) {
                    $document = $this->processFileUpload($file, $project, $folder, $description);
                    $uploadedDocuments[] = $document;
                }
            }
            // Handle single file (backward compatibility)
            else if ($request->hasFile('file')) {
                $file = $request->file('file');
                $document = $this->processFileUpload($file, $project, $folder, $description);
                $uploadedDocuments[] = $document;
            }
            
            DB::commit();
            
            // Return appropriate response based on upload type
            if (count($uploadedDocuments) === 1) {
                // Single file upload response (backward compatibility)
                $document = $uploadedDocuments[0];
                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'data' => [
                        'id' => $document->id,
                        'name' => $document->name,
                        'type' => $document->type,
                        'size' => $document->file_size,
                        'size_formatted' => $this->formatBytes($document->file_size),
                        'path' => $document->file_path,
                        'sync_status' => $document->sync_status,
                        'created_at' => $document->created_at,
                        'download_url' => "/api/file-explorer/project/{$project->id}/documents/{$document->id}/download"
                    ]
                ]);
            } else {
                // Multiple files upload response
                return response()->json([
                    'success' => true,
                    'message' => count($uploadedDocuments) . ' documents uploaded successfully',
                    'data' => [
                        'count' => count($uploadedDocuments),
                        'documents' => array_map(function($doc) use ($project) {
                            return [
                                'id' => $doc->id,
                                'name' => $doc->name,
                                'type' => $doc->type,
                                'size' => $doc->file_size,
                                'size_formatted' => $this->formatBytes($doc->file_size),
                                'path' => $doc->file_path,
                                'sync_status' => $doc->sync_status,
                                'created_at' => $doc->created_at,
                                'download_url' => "/api/file-explorer/project/{$project->id}/documents/{$doc->id}/download"
                            ];
                        }, $uploadedDocuments)
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error uploading document', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process individual file upload
     */
    private function processFileUpload($file, Project $project, string $folder, ?string $description = null): ProjectDocument
    {
        // Store document using StorageService
        $fileData = $this->storageService->storeDocument($file, $project, $folder);
        
        // Get file extension
        $extension = pathinfo($fileData['file_name'], PATHINFO_EXTENSION);
        
        // Create document record
        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'name' => $fileData['file_name'],
            'original_name' => $fileData['original_name'],
            'type' => $extension,
            'file_type' => $this->getFileType($extension),
            'file_path' => $fileData['file_path'],
            'storage_path' => $fileData['storage_path'],
            'file_size' => $fileData['file_size'],
            'description' => $description,
            'uploaded_by' => auth()->id() ?? 1,
            'folder_structure' => json_encode([
                'category' => $folder,
                'subcategory' => null
            ]),
            'sync_status' => 'pending',
            'checksum' => $fileData['checksum'] ?? null
        ]);
        
        return $document;
    }
    
    /**
     * Delete document
     */
    public function deleteDocument(Project $project, $documentId): JsonResponse
    {
        try {
            // Try to find document by ID or by path
            $document = null;
            
            // First try as numeric ID
            if (is_numeric($documentId)) {
                $document = ProjectDocument::find($documentId);
            }
            
            // If not found and documentId looks like a hash, try to find by file path
            if (!$document && strlen($documentId) == 32) { // MD5 hash length
                // This might be a hash ID from the folder structure
                // We need to find the document by matching the file
                // For now, return an error
                return response()->json([
                    'success' => false,
                    'message' => 'Please use the document database ID for deletion'
                ], 400);
            }
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }
            
            // Check if document belongs to project
            if ($document->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found in this project'
                ], 404);
            }
            
            // Delete physical file using explicit disk
            if ($document->file_path && Storage::disk($this->disk)->exists($document->file_path)) {
                Storage::disk($this->disk)->delete($document->file_path);
            }
            
            // Delete database record
            $document->delete();
            
            Log::info('Document deleted', [
                'document_id' => $document->id,
                'project_id' => $project->id,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting document', [
                'document_id' => $documentId,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete document by path
     */
    public function deleteDocumentByPath(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'path' => 'required|string'
        ]);
        
        try {
            $filePath = $request->input('path');
            
            // Find document by file path
            $document = ProjectDocument::where('project_id', $project->id)
                ->where('file_path', $filePath)
                ->first();
            
            if (!$document) {
                // Try to find by name in the current folder
                $fileName = basename($filePath);
                $document = ProjectDocument::where('project_id', $project->id)
                    ->where('name', $fileName)
                    ->orWhere('original_name', $fileName)
                    ->first();
            }
            
            if (!$document) {
                // If still not found, just delete the physical file if it exists
                if (Storage::disk($this->disk)->exists($filePath)) {
                    Storage::disk($this->disk)->delete($filePath);
                    return response()->json([
                        'success' => true,
                        'message' => 'File deleted from storage'
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }
            
            // Delete physical file
            if ($document->file_path && Storage::disk($this->disk)->exists($document->file_path)) {
                Storage::disk($this->disk)->delete($document->file_path);
            }
            
            // Delete database record
            $document->delete();
            
            Log::info('Document deleted by path', [
                'path' => $filePath,
                'document_id' => $document->id,
                'project_id' => $project->id,
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting document by path', [
                'path' => $request->input('path'),
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download document by path
     */
    public function downloadDocumentByPath(Request $request, Project $project): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        // For GET request, get path from query parameter
        $filePath = $request->query('path');
        
        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'Path parameter is required'
            ], 400);
        }
        
        try {
            
            // Check if file exists
            if (!Storage::disk($this->disk)->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            
            // Get file name
            $fileName = basename($filePath);
            
            // Try to find document record for original name
            $document = ProjectDocument::where('project_id', $project->id)
                ->where('file_path', $filePath)
                ->first();
            
            if ($document) {
                $fileName = $document->original_name ?? $document->name;
            }
            
            return Storage::disk($this->disk)->download($filePath, $fileName);
            
        } catch (\Exception $e) {
            Log::error('Error downloading document by path', [
                'path' => $request->input('path'),
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Move document to different folder
     */
    public function moveDocument(Request $request, Project $project, ProjectDocument $document): JsonResponse
    {
        $request->validate([
            'destination' => 'required|string'
        ]);
        
        try {
            // Check if document belongs to project
            if ($document->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found in this project'
                ], 404);
            }
            
            $destination = $request->input('destination');
            $projectSlug = Str::slug($project->name);
            $newPath = "proyek/{$projectSlug}/{$destination}/{$document->name}";
            
            // Check if file already exists at destination using explicit disk
            if (Storage::disk($this->disk)->exists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A file with the same name already exists in the destination folder'
                ], 400);
            }
            
            // Move physical file using explicit disk
            if ($document->file_path && Storage::disk($this->disk)->exists($document->file_path)) {
                Storage::disk($this->disk)->move($document->file_path, $newPath);
            }
            
            // Update document record
            $document->update([
                'file_path' => $newPath,
                'storage_path' => storage_path("app/{$newPath}"),
                'folder_structure' => json_encode([
                    'category' => explode('/', $destination)[0] ?? $destination,
                    'subcategory' => explode('/', $destination)[1] ?? null
                ]),
                'sync_status' => 'out_of_sync'
            ]);
            
            Log::info('Document moved', [
                'document_id' => $document->id,
                'from' => $document->getOriginal('file_path'),
                'to' => $newPath
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document moved successfully',
                'data' => [
                    'new_path' => $newPath
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error moving document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to move document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Rename document
     */
    public function renameDocument(Request $request, Project $project, ProjectDocument $document): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        
        try {
            // Check if document belongs to project
            if ($document->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found in this project'
                ], 404);
            }
            
            $newName = $request->input('name');
            $extension = pathinfo($document->name, PATHINFO_EXTENSION);
            
            // Ensure extension is preserved
            if (!str_ends_with($newName, ".{$extension}")) {
                $newName .= ".{$extension}";
            }
            
            // Build new path
            $directory = dirname($document->file_path);
            $newPath = "{$directory}/{$newName}";
            
            // Check if file with new name already exists using explicit disk
            if (Storage::disk($this->disk)->exists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A file with this name already exists'
                ], 400);
            }
            
            // Rename physical file using explicit disk
            if ($document->file_path && Storage::disk($this->disk)->exists($document->file_path)) {
                Storage::disk($this->disk)->move($document->file_path, $newPath);
            }
            
            // Update document record
            $oldName = $document->name;
            $document->update([
                'name' => $newName,
                'file_path' => $newPath,
                'storage_path' => storage_path("app/{$newPath}"),
                'sync_status' => 'out_of_sync'
            ]);
            
            Log::info('Document renamed', [
                'document_id' => $document->id,
                'old_name' => $oldName,
                'new_name' => $newName
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document renamed successfully',
                'data' => [
                    'name' => $newName,
                    'path' => $newPath
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error renaming document', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to rename document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync project to cloud
     */
    public function syncProject(Project $project): JsonResponse
    {
        try {
            $result = $this->syncService->syncProject($project);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'duration' => $result['duration'],
                    'log_id' => $result['log_id']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error syncing project', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync project',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get sync status for project
     */
    public function getSyncStatus(Project $project): JsonResponse
    {
        try {
            $status = $this->syncService->checkSyncStatus($project);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting sync status', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create new folder
     */
    public function createFolder(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\_]+$/',
            'parent_path' => 'nullable|string'
        ]);
        
        try {
            $folderName = $request->input('name');
            $parentPath = $request->input('parent_path', '');
            
            $projectSlug = Str::slug($project->name);
            $basePath = "proyek/{$projectSlug}";
            
            // Build the full path
            if ($parentPath) {
                // Remove the base path if it's already included in parent_path
                if (strpos($parentPath, $basePath) === 0) {
                    $fullPath = "{$parentPath}/{$folderName}";
                } else {
                    $fullPath = "{$basePath}/{$parentPath}/{$folderName}";
                }
            } else {
                $fullPath = "{$basePath}/{$folderName}";
            }
            
            // Check if folder already exists using explicit disk
            if (Storage::disk($this->disk)->exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder already exists'
                ], 400);
            }
            
            // Create physical directory using explicit disk
            $created = Storage::disk($this->disk)->makeDirectory($fullPath);
            
            if (!$created) {
                throw new \Exception('Failed to create directory in filesystem');
            }
            
            // Find parent folder if exists
            $parentFolder = null;
            if ($parentPath) {
                $parentFolder = ProjectFolder::where('project_id', $project->id)
                    ->where('folder_path', "{$basePath}/{$parentPath}")
                    ->first();
            } else {
                $parentFolder = ProjectFolder::where('project_id', $project->id)
                    ->whereNull('parent_id')
                    ->first();
            }
            
            // Create folder record
            $folder = ProjectFolder::create([
                'project_id' => $project->id,
                'folder_name' => $folderName,
                'folder_path' => $fullPath,
                'parent_id' => $parentFolder ? $parentFolder->id : null,
                'folder_type' => 'custom',
                'sync_status' => 'pending',
                'metadata' => json_encode([
                    'created_by' => auth()->id(),
                    'created_at' => now()->toIso8601String()
                ])
            ]);
            
            Log::info('Folder created', [
                'folder_id' => $folder->id,
                'project_id' => $project->id,
                'path' => $fullPath
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully',
                'data' => [
                    'id' => $folder->id,
                    'name' => $folder->folder_name,
                    'path' => $folder->folder_path
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating folder', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Rename folder
     */
    public function renameFolder(Request $request, Project $project, $folderPath): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\_]+$/'
        ]);
        
        try {
            $newName = $request->input('name');
            $projectSlug = Str::slug($project->name);
            
            // Decode the folder path if it's URL encoded
            $folderPath = urldecode($folderPath);
            
            // The folderPath comes as relative path from frontend (e.g., "dokumen/laporan")
            // We need to build the full storage path
            $oldPath = "proyek/{$projectSlug}/{$folderPath}";
            
            // Check if folder exists using explicit disk
            if (!Storage::disk($this->disk)->exists($oldPath)) {
                // Try without the proyek prefix in case the path already includes it
                $oldPath = $folderPath;
                if (!Storage::disk($this->disk)->exists($oldPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Folder not found'
                    ], 404);
                }
            }
            
            // Build new path
            $parentPath = dirname($oldPath);
            $newPath = $parentPath === '.' ? $newName : "{$parentPath}/{$newName}";
            
            // Check if new folder name already exists using explicit disk
            if (Storage::disk($this->disk)->exists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A folder with this name already exists'
                ], 400);
            }
            
            // Move folder in filesystem using explicit disk
            Storage::disk($this->disk)->move($oldPath, $newPath);
            
            // Update database records for folders
            $oldPathPattern = $oldPath . '%';
            $folders = ProjectFolder::where('project_id', $project->id)
                ->where('folder_path', 'like', $oldPathPattern)
                ->get();
            
            foreach ($folders as $folder) {
                $updatedPath = str_replace($oldPath, $newPath, $folder->folder_path);
                $folder->update([
                    'folder_path' => $updatedPath,
                    'sync_status' => 'out_of_sync'
                ]);
                
                // Update folder name if it's the renamed folder
                if ($folder->folder_path === $newPath) {
                    $folder->update(['folder_name' => $newName]);
                }
            }
            
            // Update document paths
            $documents = ProjectDocument::where('project_id', $project->id)
                ->where('file_path', 'like', $oldPathPattern)
                ->get();
            
            foreach ($documents as $document) {
                $updatedPath = str_replace($oldPath, $newPath, $document->file_path);
                $document->update([
                    'file_path' => $updatedPath,
                    'storage_path' => storage_path("app/{$updatedPath}"),
                    'sync_status' => 'out_of_sync'
                ]);
            }
            
            Log::info('Folder renamed', [
                'project_id' => $project->id,
                'old_path' => $oldPath,
                'new_path' => $newPath,
                'renamed_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Folder renamed successfully',
                'data' => [
                    'old_path' => $folderPath,
                    'new_path' => basename($newPath),
                    'name' => $newName
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error renaming folder', [
                'project_id' => $project->id,
                'folder_path' => $folderPath,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to rename folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete folder
     */
    public function deleteFolder(Request $request, Project $project, $folderPath): JsonResponse
    {
        $request->validate([
            'force' => 'nullable|boolean'
        ]);
        
        try {
            $force = $request->input('force', false);
            $projectSlug = Str::slug($project->name);
            
            // Decode the folder path if it's URL encoded
            $folderPath = urldecode($folderPath);
            
            // The folderPath comes as relative path from frontend (e.g., "dokumen/laporan")
            // We need to build the full storage path
            $fullPath = "proyek/{$projectSlug}/{$folderPath}";
            
            // Check if folder exists using explicit disk
            if (!Storage::disk($this->disk)->exists($fullPath)) {
                // Try without the proyek prefix in case the path already includes it
                $fullPath = $folderPath;
                if (!Storage::disk($this->disk)->exists($fullPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Folder not found'
                    ], 404);
                }
            }
            
            // Check if folder has contents using explicit disk
            $files = Storage::disk($this->disk)->files($fullPath);
            $directories = Storage::disk($this->disk)->directories($fullPath);
            $hasContents = count($files) > 0 || count($directories) > 0;
            
            if ($hasContents && !$force) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder is not empty. Use force=true to delete with contents.',
                    'data' => [
                        'files_count' => count($files),
                        'folders_count' => count($directories)
                    ]
                ], 400);
            }
            
            // Delete documents from database
            $documents = ProjectDocument::where('project_id', $project->id)
                ->where('file_path', 'like', $fullPath . '%')
                ->get();
            
            foreach ($documents as $document) {
                $document->delete();
            }
            
            // Delete folder records from database
            $folders = ProjectFolder::where('project_id', $project->id)
                ->where('folder_path', 'like', $fullPath . '%')
                ->get();
            
            foreach ($folders as $folder) {
                $folder->delete();
            }
            
            // Delete folder from filesystem using explicit disk
            Storage::disk($this->disk)->deleteDirectory($fullPath);
            
            Log::info('Folder deleted', [
                'project_id' => $project->id,
                'folder_path' => $fullPath,
                'force' => $force,
                'deleted_documents' => $documents->count(),
                'deleted_folders' => $folders->count(),
                'deleted_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Folder deleted successfully',
                'data' => [
                    'path' => $folderPath,
                    'deleted_documents' => $documents->count(),
                    'deleted_folders' => $folders->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting folder', [
                'project_id' => $project->id,
                'folder_path' => $folderPath,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Build folder tree structure
     */
    private function buildFolderTree($folder)
    {
        if (!$folder) {
            return null;
        }
        
        $tree = [
            'id' => $folder->id,
            'name' => $folder->folder_name,
            'path' => $folder->folder_path,
            'type' => $folder->folder_type,
            'sync_status' => $folder->sync_status,
            'children' => [],
            'documents' => []
        ];
        
        // Add child folders
        if ($folder->children) {
            foreach ($folder->children as $child) {
                $tree['children'][] = $this->buildFolderTree($child);
            }
        }
        
        // Add documents
        if ($folder->documents) {
            foreach ($folder->documents as $doc) {
                $tree['documents'][] = [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'size' => $doc->file_size,
                    'size_formatted' => $this->formatBytes($doc->file_size),
                    'sync_status' => $doc->sync_status,
                    'created_at' => $doc->created_at
                ];
            }
        }
        
        return $tree;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Check if document can be previewed
     */
    private function canPreview(ProjectDocument $document): bool
    {
        $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'doc', 'docx', 'xls', 'xlsx'];
        return in_array(strtolower($document->type), $previewableTypes);
    }
    
    /**
     * Get preview URL for document
     */
    private function getPreviewUrl(ProjectDocument $document): ?string
    {
        if (!$this->canPreview($document)) {
            return null;
        }
        
        return "/api/file-explorer/project/{$document->project_id}/documents/{$document->id}/preview";
    }
    
    /**
     * Check storage-database synchronization status
     */
    public function checkSyncStatus(Project $project): JsonResponse
    {
        try {
            $syncStatus = $this->storageDatabaseSyncService->checkSyncStatus($project);
            
            return response()->json([
                'success' => true,
                'data' => $syncStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking sync status', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check sync status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Perform storage-database synchronization
     */
    public function performStorageDatabaseSync(Project $project, Request $request): JsonResponse
    {
        try {
            $options = [
                'soft_delete' => $request->input('soft_delete', false)
            ];
            
            $result = $this->storageDatabaseSyncService->performSync($project, $options);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['results']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => $result['results']
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Error performing storage-database sync', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform synchronization',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get file type based on extension
     */
    private function getFileType($extension): string
    {
        $extension = strtolower($extension);
        
        $typeMap = [
            // Documents
            'pdf' => 'document',
            'doc' => 'document',
            'docx' => 'document',
            'txt' => 'document',
            'rtf' => 'document',
            'odt' => 'document',
            
            // Spreadsheets
            'xls' => 'spreadsheet',
            'xlsx' => 'spreadsheet',
            'csv' => 'spreadsheet',
            'ods' => 'spreadsheet',
            
            // Presentations
            'ppt' => 'presentation',
            'pptx' => 'presentation',
            'odp' => 'presentation',
            
            // Images
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'gif' => 'image',
            'bmp' => 'image',
            'svg' => 'image',
            'webp' => 'image',
            
            // Videos
            'mp4' => 'video',
            'avi' => 'video',
            'mov' => 'video',
            'wmv' => 'video',
            'flv' => 'video',
            'mkv' => 'video',
            
            // Archives
            'zip' => 'archive',
            'rar' => 'archive',
            '7z' => 'archive',
            'tar' => 'archive',
            'gz' => 'archive',
            
            // Code
            'php' => 'code',
            'js' => 'code',
            'css' => 'code',
            'html' => 'code',
            'json' => 'code',
            'xml' => 'code',
            'sql' => 'code',
            'py' => 'code',
            'java' => 'code',
            'c' => 'code',
            'cpp' => 'code',
            'h' => 'code',
            'sh' => 'code',
            'yml' => 'code',
            'yaml' => 'code',
        ];
        
        return $typeMap[$extension] ?? 'other';
    }
}