<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectFolder;
use Illuminate\Support\Facades\Storage;

class CheckProjectDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:check-documents 
                            {project_id : The ID of the project to check}
                            {--show-files : Show all files in database}
                            {--show-folders : Show all folders in database}
                            {--check-storage : Check if files exist in storage}
                            {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check project documents and folders in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->argument('project_id');
        $project = Project::find($projectId);
        
        if (!$project) {
            $this->error("Project with ID {$projectId} not found!");
            return 1;
        }
        
        $this->info("========================================");
        $this->info("Project: {$project->name}");
        $this->info("Code: {$project->code}");
        $this->info("ID: {$project->id}");
        $this->info("========================================\n");
        
        // Show summary
        $this->showSummary($project);
        
        // Show folders if requested
        if ($this->option('show-folders')) {
            $this->showFolders($project);
        }
        
        // Show files if requested
        if ($this->option('show-files')) {
            $this->showFiles($project);
        }
        
        // Check storage if requested
        if ($this->option('check-storage')) {
            $this->checkStorage($project);
        }
        
        return 0;
    }
    
    private function showSummary(Project $project)
    {
        $documentsCount = ProjectDocument::where('project_id', $project->id)->count();
        $foldersCount = ProjectFolder::where('project_id', $project->id)->count();
        $totalSize = ProjectDocument::where('project_id', $project->id)->sum('file_size');
        
        $this->info("📊 SUMMARY");
        $this->info("├─ Total Documents: {$documentsCount}");
        $this->info("├─ Total Folders: {$foldersCount}");
        $this->info("└─ Total Size: " . $this->formatBytes($totalSize));
        $this->info("");
        
        // Document types breakdown
        $types = ProjectDocument::where('project_id', $project->id)
            ->selectRaw('document_type, count(*) as count')
            ->groupBy('document_type')
            ->get();
            
        if ($types->count() > 0) {
            $this->info("📁 DOCUMENT TYPES");
            foreach ($types as $type) {
                $typeName = $type->document_type ?: 'unknown';
                $this->info("├─ {$typeName}: {$type->count} files");
            }
            $this->info("");
        }
    }
    
    private function showFolders(Project $project)
    {
        $this->info("📂 FOLDERS IN DATABASE");
        $this->info("----------------------------------------");
        
        $folders = ProjectFolder::where('project_id', $project->id)
            ->orderBy('folder_path')
            ->get();
            
        if ($folders->isEmpty()) {
            $this->warn("No folders found in database");
            return;
        }
        
        $table = [];
        foreach ($folders as $folder) {
            $row = [
                'ID' => $folder->id,
                'Name' => $folder->folder_name,
                'Path' => $folder->folder_path,
                'Type' => $folder->folder_type,
                'Parent ID' => $folder->parent_id ?: '-',
            ];
            
            if ($this->option('detailed')) {
                $row['Created'] = $folder->created_at->format('Y-m-d H:i:s');
                $row['Sync Status'] = $folder->sync_status ?: 'unknown';
            }
            
            $table[] = $row;
        }
        
        $this->table(array_keys($table[0]), $table);
        $this->info("");
    }
    
    private function showFiles(Project $project)
    {
        $this->info("📄 FILES IN DATABASE");
        $this->info("----------------------------------------");
        
        $documents = ProjectDocument::where('project_id', $project->id)
            ->orderBy('file_path')
            ->get();
            
        if ($documents->isEmpty()) {
            $this->warn("No documents found in database");
            return;
        }
        
        $table = [];
        foreach ($documents as $doc) {
            $row = [
                'ID' => $doc->id,
                'Name' => $doc->name,
                'Size' => $this->formatBytes($doc->file_size),
                'Type' => $doc->document_type,
            ];
            
            if ($this->option('detailed')) {
                $row['Path'] = $doc->file_path;
                $row['File Type'] = $doc->file_type;
                $row['Uploaded By'] = $doc->uploaded_by;
                $row['Created'] = $doc->created_at->format('Y-m-d H:i:s');
                $row['Sync Status'] = $doc->sync_status ?: 'unknown';
            } else {
                // Show truncated path for non-detailed view
                $path = $doc->file_path;
                if (strlen($path) > 50) {
                    $path = '...' . substr($path, -47);
                }
                $row['Path'] = $path;
            }
            
            $table[] = $row;
        }
        
        $this->table(array_keys($table[0]), $table);
        $this->info("");
    }
    
    private function checkStorage(Project $project)
    {
        $this->info("🔍 STORAGE CHECK");
        $this->info("----------------------------------------");
        
        $documents = ProjectDocument::where('project_id', $project->id)->get();
        
        $existingCount = 0;
        $missingCount = 0;
        $missingFiles = [];
        
        foreach ($documents as $doc) {
            if (Storage::disk('local')->exists($doc->file_path)) {
                $existingCount++;
            } else {
                $missingCount++;
                $missingFiles[] = [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'path' => $doc->file_path
                ];
            }
        }
        
        $this->info("✅ Files exist in storage: {$existingCount}");
        
        if ($missingCount > 0) {
            $this->error("❌ Files missing from storage: {$missingCount}");
            
            if ($this->option('detailed')) {
                $this->info("\nMissing files:");
                foreach ($missingFiles as $file) {
                    $this->error("  - ID: {$file['id']}, Name: {$file['name']}");
                    $this->error("    Path: {$file['path']}");
                }
            }
        }
        
        // Check for orphaned files in storage
        $this->info("\n📦 ORPHANED FILES CHECK");
        $this->info("----------------------------------------");
        
        $projectSlug = \Str::slug($project->name);
        $storagePath = "proyek/{$projectSlug}";
        
        if (Storage::disk('local')->exists($storagePath)) {
            $allFiles = Storage::disk('local')->allFiles($storagePath);
            $dbPaths = $documents->pluck('file_path')->toArray();
            
            $orphanedFiles = [];
            foreach ($allFiles as $file) {
                if (!in_array($file, $dbPaths)) {
                    $orphanedFiles[] = $file;
                }
            }
            
            if (count($orphanedFiles) > 0) {
                $this->warn("⚠️  Files in storage but not in database: " . count($orphanedFiles));
                
                if ($this->option('detailed')) {
                    $this->info("\nOrphaned files:");
                    foreach ($orphanedFiles as $file) {
                        $size = Storage::disk('local')->size($file);
                        $this->warn("  - {$file} (" . $this->formatBytes($size) . ")");
                    }
                }
            } else {
                $this->info("✅ No orphaned files found");
            }
        } else {
            $this->warn("Storage folder not found: {$storagePath}");
        }
        
        $this->info("");
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $index = floor($base);
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[$index];
    }
}