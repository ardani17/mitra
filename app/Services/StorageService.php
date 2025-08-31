<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class StorageService
{
    private $basePath;
    private $disk = 'local'; // Eksplisit gunakan disk 'local' untuk konsistensi
    
    public function __construct()
    {
        $this->basePath = storage_path('app/proyek');
    }
    
    /**
     * Create project folder structure
     */
    public function createProjectFolder(Project $project): string
    {
        $projectSlug = Str::slug($project->name);
        $projectPath = "{$this->basePath}/{$projectSlug}";
        
        // Create main folders
        $folders = [
            'dokumen/kontrak',
            'dokumen/teknis',
            'dokumen/keuangan',
            'dokumen/laporan',
            'dokumen/lainnya',
            'gambar',
            'video'
        ];
        
        foreach ($folders as $folder) {
            $fullPath = "{$projectPath}/{$folder}";
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
        }
        
        return $projectPath;
    }
    
    /**
     * Store document with duplicate validation
     */
    public function storeDocument(UploadedFile $file, Project $project, string $category = 'lainnya'): array
    {
        $projectPath = $this->getProjectPath($project);
        
        // Use category as-is if it contains slash (already a path)
        // Otherwise use the mapping
        if (strpos($category, '/') !== false) {
            $categoryPath = $category;
        } else {
            $categoryPath = $this->getCategoryPath($category);
        }
        
        $fullPath = "{$projectPath}/{$categoryPath}";
        
        // Get original filename
        $originalName = $file->getClientOriginalName();
        $fileName = $originalName;
        
        // Get file info before moving
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $checksum = hash_file('sha256', $file->getRealPath());
        
        // Handle duplicate filenames by adding number suffix
        $counter = 1;
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        while ($this->fileExists($fullPath, $fileName)) {
            $fileName = "{$nameWithoutExt}_{$counter}.{$extension}";
            $counter++;
        }
        
        // Create directory if not exists
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
        
        // Store file using Laravel's storage with explicit disk
        $relativePath = "proyek/" . Str::slug($project->name) . "/{$categoryPath}";
        
        try {
            // Use explicit disk for consistency
            $storedPath = $file->storeAs($relativePath, $fileName, $this->disk);
            $actualFilePath = storage_path("app/{$storedPath}");
            
            // If file doesn't exist after Laravel storage, try direct copy
            if (!File::exists($actualFilePath)) {
                // Use copy instead of move to preserve the temp file
                $tempPath = $file->getRealPath();
                $destinationPath = "{$fullPath}/{$fileName}";
                
                if (copy($tempPath, $destinationPath)) {
                    $storedPath = str_replace(storage_path('app/'), '', $destinationPath);
                    $storedPath = str_replace('\\', '/', $storedPath);
                    $actualFilePath = $destinationPath;
                } else {
                    throw new \Exception("Failed to copy file to destination.");
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('File storage failed', [
                'error' => $e->getMessage(),
                'project' => $project->id,
                'category' => $categoryPath,
                'file' => $fileName,
                'full_path' => $fullPath
            ]);
            throw new \Exception("Failed to save file: " . $e->getMessage());
        }
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $storedPath,
            'storage_path' => $actualFilePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'checksum' => $checksum
        ];
    }
    
    /**
     * Check if file exists in folder
     */
    public function fileExists(string $folderPath, string $fileName): bool
    {
        return File::exists("{$folderPath}/{$fileName}");
    }
    
    /**
     * Get project path
     */
    public function getProjectPath(Project $project): string
    {
        return "{$this->basePath}/" . Str::slug($project->name);
    }
    
    /**
     * Get category path mapping
     */
    private function getCategoryPath(string $category): string
    {
        $mapping = [
            'contract' => 'dokumen/kontrak',
            'technical' => 'dokumen/teknis',
            'financial' => 'dokumen/keuangan',
            'report' => 'dokumen/laporan',
            'other' => 'dokumen/lainnya',
            'image' => 'gambar',
            'video' => 'video',
            // Add direct folder mappings - these should map to dokumen subfolder
            'dokumen' => 'dokumen',
            'teknis' => 'dokumen/teknis',
            'keuangan' => 'dokumen/keuangan',
            'laporan' => 'dokumen/laporan',
            'foto' => 'foto',
            'lainnya' => 'dokumen/lainnya',
            // Add new mappings for root folders
            'kontrak' => 'dokumen/kontrak',
            'tes' => 'tes'
        ];
        
        return $mapping[$category] ?? $category;
    }
    
    /**
     * Delete document file
     */
    public function deleteDocument(ProjectDocument $document): bool
    {
        // Delete from storage using explicit disk
        if (Storage::disk($this->disk)->exists($document->file_path)) {
            return Storage::disk($this->disk)->delete($document->file_path);
        }
        
        // Also try to delete from absolute path if exists
        if ($document->storage_path && File::exists($document->storage_path)) {
            return File::delete($document->storage_path);
        }
        
        return false;
    }
    
    /**
     * Move document to different folder
     */
    public function moveDocument(ProjectDocument $document, string $newCategory): bool
    {
        $oldPath = $document->file_path;
        $newCategoryPath = $this->getCategoryPath($newCategory);
        
        $projectSlug = Str::slug($document->project->name);
        $newPath = "proyek/{$projectSlug}/{$newCategoryPath}/" . basename($oldPath);
        
        // Check for duplicates in destination using explicit disk
        if (Storage::disk($this->disk)->exists($newPath)) {
            throw new \Exception("File dengan nama yang sama sudah ada di folder tujuan.");
        }
        
        // Create destination directory if not exists
        $newFullPath = storage_path("app/{$newPath}");
        $newDir = dirname($newFullPath);
        if (!File::exists($newDir)) {
            File::makeDirectory($newDir, 0755, true);
        }
        
        if (Storage::disk($this->disk)->move($oldPath, $newPath)) {
            $document->update([
                'file_path' => $newPath,
                'storage_path' => $newFullPath,
                'document_type' => $newCategory,
                'sync_status' => 'out_of_sync' // Mark for re-sync
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Rename document file
     */
    public function renameDocument(ProjectDocument $document, string $newName): bool
    {
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $newFileName = $newName . '.' . $extension;
        
        // Get current directory
        $currentDir = dirname($document->file_path);
        $newPath = $currentDir . '/' . $newFileName;
        
        // Check for duplicates using explicit disk
        if (Storage::disk($this->disk)->exists($newPath)) {
            throw new \Exception("File dengan nama '{$newFileName}' sudah ada.");
        }
        
        // Rename file using explicit disk
        if (Storage::disk($this->disk)->move($document->file_path, $newPath)) {
            $document->update([
                'name' => $newName,
                'original_name' => $newFileName,
                'file_path' => $newPath,
                'storage_path' => storage_path("app/{$newPath}"),
                'sync_status' => 'out_of_sync' // Mark for re-sync
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get folder structure for project
     */
    public function getFolderStructure(Project $project): array
    {
        $projectPath = $this->getProjectPath($project);
        
        if (!File::exists($projectPath)) {
            return [];
        }
        
        return $this->scanDirectory($projectPath);
    }
    
    /**
     * Recursively scan directory
     */
    private function scanDirectory(string $path, string $relativePath = ''): array
    {
        $items = [];
        $files = File::files($path);
        $directories = File::directories($path);
        
        // Add directories
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            $dirRelativePath = $relativePath ? "{$relativePath}/{$dirName}" : $dirName;
            
            $items[] = [
                'name' => $dirName,
                'type' => 'folder',
                'path' => $dirRelativePath,
                'children' => $this->scanDirectory($dir, $dirRelativePath)
            ];
        }
        
        // Add files
        foreach ($files as $file) {
            $fileName = $file->getFilename();
            $fileRelativePath = $relativePath ? "{$relativePath}/{$fileName}" : $fileName;
            
            $items[] = [
                'name' => $fileName,
                'type' => 'file',
                'path' => $fileRelativePath,
                'size' => $file->getSize(),
                'extension' => $file->getExtension(),
                'modified' => $file->getMTime()
            ];
        }
        
        return $items;
    }
    
    /**
     * Get storage statistics for project
     */
    public function getProjectStorageStats(Project $project): array
    {
        $projectPath = $this->getProjectPath($project);
        
        if (!File::exists($projectPath)) {
            return [
                'total_size' => 0,
                'file_count' => 0,
                'folder_count' => 0,
                'by_category' => []
            ];
        }
        
        $totalSize = 0;
        $fileCount = 0;
        $folderCount = 0;
        $byCategory = [];
        
        // Calculate stats
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $folderCount++;
            } else {
                $fileCount++;
                $size = $item->getSize();
                $totalSize += $size;
                
                // Categorize by folder
                $relativePath = str_replace($projectPath . '/', '', $item->getPathname());
                $category = explode('/', $relativePath)[0] ?? 'root';
                
                if (!isset($byCategory[$category])) {
                    $byCategory[$category] = [
                        'count' => 0,
                        'size' => 0
                    ];
                }
                
                $byCategory[$category]['count']++;
                $byCategory[$category]['size'] += $size;
            }
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'file_count' => $fileCount,
            'folder_count' => $folderCount,
            'by_category' => $byCategory
        ];
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
     * Clean up orphaned files (files without database records)
     */
    public function cleanupOrphanedFiles(Project $project): array
    {
        $projectPath = $this->getProjectPath($project);
        $cleaned = [];
        
        if (!File::exists($projectPath)) {
            return $cleaned;
        }
        
        // Get all files in project folder
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            
            $relativePath = str_replace(storage_path('app/'), '', $file->getPathname());
            
            // Check if file exists in database
            $exists = ProjectDocument::where('project_id', $project->id)
                ->where(function($query) use ($relativePath, $file) {
                    $query->where('file_path', $relativePath)
                        ->orWhere('storage_path', $file->getPathname());
                })
                ->exists();
            
            if (!$exists) {
                // File is orphaned, delete it
                File::delete($file->getPathname());
                $cleaned[] = $relativePath;
            }
        }
        
        return $cleaned;
    }
}