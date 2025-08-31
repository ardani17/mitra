<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectFolder;
use App\Services\StorageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectObserver
{
    private $storageService;
    
    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }
    
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        try {
            // Create main project folder
            $projectSlug = Str::slug($project->name);
            $mainPath = "proyek/{$projectSlug}";
            
            // Create physical directories
            $this->storageService->createProjectFolder($project);
            
            // Create database records for folder structure
            $mainFolder = ProjectFolder::create([
                'project_id' => $project->id,
                'folder_name' => $projectSlug,
                'folder_path' => $mainPath,
                'parent_id' => null,
                'folder_type' => 'root',
                'sync_status' => 'pending',
                'metadata' => json_encode([
                    'created_by' => auth()->id() ?? 'system',
                    'created_at' => now()->toIso8601String()
                ])
            ]);
            
            // Define folder structure
            $folders = [
                'dokumen' => [
                    'type' => 'category',
                    'subfolders' => [
                        'kontrak' => ['type' => 'subcategory'],
                        'perizinan' => ['type' => 'subcategory'],
                        'legal' => ['type' => 'subcategory']
                    ]
                ],
                'teknis' => [
                    'type' => 'category',
                    'subfolders' => [
                        'desain' => ['type' => 'subcategory'],
                        'spesifikasi' => ['type' => 'subcategory'],
                        'gambar' => ['type' => 'subcategory']
                    ]
                ],
                'keuangan' => [
                    'type' => 'category',
                    'subfolders' => [
                        'invoice' => ['type' => 'subcategory'],
                        'pembayaran' => ['type' => 'subcategory'],
                        'laporan' => ['type' => 'subcategory']
                    ]
                ],
                'laporan' => [
                    'type' => 'category',
                    'subfolders' => [
                        'progress' => ['type' => 'subcategory'],
                        'mingguan' => ['type' => 'subcategory'],
                        'bulanan' => ['type' => 'subcategory']
                    ]
                ],
                'foto' => [
                    'type' => 'category',
                    'subfolders' => [
                        'sebelum' => ['type' => 'subcategory'],
                        'progress' => ['type' => 'subcategory'],
                        'selesai' => ['type' => 'subcategory']
                    ]
                ],
                'lainnya' => [
                    'type' => 'category',
                    'subfolders' => []
                ]
            ];
            
            // Create folder records in database
            foreach ($folders as $folderName => $folderConfig) {
                $categoryPath = "{$mainPath}/{$folderName}";
                
                $categoryFolder = ProjectFolder::create([
                    'project_id' => $project->id,
                    'folder_name' => $folderName,
                    'folder_path' => $categoryPath,
                    'parent_id' => $mainFolder->id,
                    'folder_type' => $folderConfig['type'],
                    'sync_status' => 'pending',
                    'metadata' => json_encode([
                        'created_by' => 'system',
                        'created_at' => now()->toIso8601String()
                    ])
                ]);
                
                // Create subfolders
                foreach ($folderConfig['subfolders'] as $subfolderName => $subfolderConfig) {
                    $subfolderPath = "{$categoryPath}/{$subfolderName}";
                    
                    ProjectFolder::create([
                        'project_id' => $project->id,
                        'folder_name' => $subfolderName,
                        'folder_path' => $subfolderPath,
                        'parent_id' => $categoryFolder->id,
                        'folder_type' => $subfolderConfig['type'],
                        'sync_status' => 'pending',
                        'metadata' => json_encode([
                            'created_by' => 'system',
                            'created_at' => now()->toIso8601String()
                        ])
                    ]);
                }
            }
            
            Log::info('Project folders created successfully', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'main_path' => $mainPath
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create project folders', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            // Don't throw exception to prevent project creation from failing
            // Folders can be created manually later if needed
        }
    }
    
    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Check if project name has changed
        if ($project->isDirty('name')) {
            $oldName = $project->getOriginal('name');
            $newName = $project->name;
            
            $oldSlug = Str::slug($oldName);
            $newSlug = Str::slug($newName);
            
            // Update folder paths in database
            $mainFolder = ProjectFolder::where('project_id', $project->id)
                ->whereNull('parent_id')
                ->first();
            
            if ($mainFolder) {
                // Update all folder paths
                $folders = ProjectFolder::where('project_id', $project->id)->get();
                
                foreach ($folders as $folder) {
                    $newPath = str_replace("proyek/{$oldSlug}", "proyek/{$newSlug}", $folder->folder_path);
                    $folder->update(['folder_path' => $newPath]);
                }
                
                // Rename physical directory
                $oldPath = storage_path("app/proyek/{$oldSlug}");
                $newPath = storage_path("app/proyek/{$newSlug}");
                
                if (file_exists($oldPath) && !file_exists($newPath)) {
                    rename($oldPath, $newPath);
                    
                    // Update document paths
                    $project->documents()->each(function ($document) use ($oldSlug, $newSlug) {
                        $newFilePath = str_replace("proyek/{$oldSlug}", "proyek/{$newSlug}", $document->file_path);
                        $newStoragePath = str_replace("proyek/{$oldSlug}", "proyek/{$newSlug}", $document->storage_path);
                        
                        $document->update([
                            'file_path' => $newFilePath,
                            'storage_path' => $newStoragePath,
                            'sync_status' => 'out_of_sync' // Mark as out of sync due to path change
                        ]);
                    });
                    
                    Log::info('Project folders renamed', [
                        'project_id' => $project->id,
                        'old_name' => $oldName,
                        'new_name' => $newName
                    ]);
                }
            }
        }
    }
    
    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        // Note: We don't delete physical files automatically for safety
        // Files can be manually deleted or archived
        
        Log::info('Project deleted, files preserved', [
            'project_id' => $project->id,
            'project_name' => $project->name
        ]);
    }
    
    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        // Re-create folder structure if needed
        $mainFolder = ProjectFolder::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->first();
        
        if (!$mainFolder) {
            // Re-create folder structure
            $this->created($project);
        }
    }
    
    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void
    {
        // This is when a project is permanently deleted
        // Still don't delete files automatically for safety
        
        Log::warning('Project force deleted, consider manual file cleanup', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'path' => "proyek/" . Str::slug($project->name)
        ]);
    }
}