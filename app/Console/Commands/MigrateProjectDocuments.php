<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectFolder;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateProjectDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:migrate-documents 
                            {--project= : Specific project ID to migrate}
                            {--dry-run : Run without making actual changes}
                            {--force : Force migration even if folders already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing project documents to new folder structure';

    private $storageService;
    private $dryRun = false;
    private $force = false;
    private $stats = [
        'projects_processed' => 0,
        'documents_migrated' => 0,
        'folders_created' => 0,
        'errors' => 0
    ];

    public function __construct(StorageService $storageService)
    {
        parent::__construct();
        $this->storageService = $storageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->dryRun = $this->option('dry-run');
        $this->force = $this->option('force');
        
        if ($this->dryRun) {
            $this->info('🔍 Running in DRY-RUN mode - no changes will be made');
        }
        
        $projectId = $this->option('project');
        
        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                $this->error("Project with ID {$projectId} not found");
                return 1;
            }
            $this->migrateProject($project);
        } else {
            if (!$this->confirm('This will migrate ALL projects. Continue?')) {
                return 0;
            }
            
            $projects = Project::all();
            $bar = $this->output->createProgressBar($projects->count());
            $bar->start();
            
            foreach ($projects as $project) {
                $this->migrateProject($project);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }
        
        $this->displayStats();
        
        return 0;
    }
    
    private function migrateProject(Project $project)
    {
        $this->info("\n📁 Processing project: {$project->name}");
        
        try {
            DB::beginTransaction();
            
            // Check if folders already exist
            $existingFolders = ProjectFolder::where('project_id', $project->id)->count();
            
            if ($existingFolders > 0 && !$this->force) {
                $this->warn("  ⚠️  Folders already exist for this project. Use --force to recreate.");
                DB::rollback();
                return;
            }
            
            // Create folder structure
            if (!$this->dryRun) {
                if ($existingFolders > 0 && $this->force) {
                    ProjectFolder::where('project_id', $project->id)->delete();
                    $this->info("  🗑️  Removed existing folder records");
                }
                
                $this->createFolderStructure($project);
            }
            
            // Migrate existing documents
            $documents = $project->documents;
            
            if ($documents->count() > 0) {
                $this->info("  📄 Found {$documents->count()} documents to migrate");
                
                foreach ($documents as $document) {
                    $this->migrateDocument($document, $project);
                }
            } else {
                $this->info("  ℹ️  No documents to migrate");
            }
            
            if (!$this->dryRun) {
                DB::commit();
            } else {
                DB::rollback();
            }
            
            $this->stats['projects_processed']++;
            $this->info("  ✅ Project migration completed");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("  ❌ Error migrating project: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }
    
    private function createFolderStructure(Project $project)
    {
        $projectSlug = Str::slug($project->name);
        $mainPath = "proyek/{$projectSlug}";
        
        // Create physical directories
        $this->storageService->createProjectFolder($project);
        
        // Create main folder record
        $mainFolder = ProjectFolder::create([
            'project_id' => $project->id,
            'folder_name' => $projectSlug,
            'folder_path' => $mainPath,
            'parent_id' => null,
            'folder_type' => 'root',
            'sync_status' => 'pending',
            'metadata' => json_encode([
                'created_by' => 'migration',
                'created_at' => now()->toIso8601String()
            ])
        ]);
        
        $this->stats['folders_created']++;
        
        // Define folder structure
        $folders = [
            'dokumen' => ['kontrak', 'perizinan', 'legal'],
            'teknis' => ['desain', 'spesifikasi', 'gambar'],
            'keuangan' => ['invoice', 'pembayaran', 'laporan'],
            'laporan' => ['progress', 'mingguan', 'bulanan'],
            'foto' => ['sebelum', 'progress', 'selesai'],
            'lainnya' => []
        ];
        
        // Create category and subcategory folders
        foreach ($folders as $categoryName => $subfolders) {
            $categoryPath = "{$mainPath}/{$categoryName}";
            
            $categoryFolder = ProjectFolder::create([
                'project_id' => $project->id,
                'folder_name' => $categoryName,
                'folder_path' => $categoryPath,
                'parent_id' => $mainFolder->id,
                'folder_type' => 'category',
                'sync_status' => 'pending',
                'metadata' => json_encode([
                    'created_by' => 'migration',
                    'created_at' => now()->toIso8601String()
                ])
            ]);
            
            $this->stats['folders_created']++;
            
            foreach ($subfolders as $subfolderName) {
                $subfolderPath = "{$categoryPath}/{$subfolderName}";
                
                ProjectFolder::create([
                    'project_id' => $project->id,
                    'folder_name' => $subfolderName,
                    'folder_path' => $subfolderPath,
                    'parent_id' => $categoryFolder->id,
                    'folder_type' => 'subcategory',
                    'sync_status' => 'pending',
                    'metadata' => json_encode([
                        'created_by' => 'migration',
                        'created_at' => now()->toIso8601String()
                    ])
                ]);
                
                $this->stats['folders_created']++;
            }
        }
        
        $this->info("  📁 Created {$this->stats['folders_created']} folder records");
    }
    
    private function migrateDocument(ProjectDocument $document, Project $project)
    {
        try {
            $projectSlug = Str::slug($project->name);
            
            // Determine category based on document type or name
            $category = $this->determineCategory($document);
            
            // Build new path
            $newPath = "proyek/{$projectSlug}/{$category}";
            
            // Get original filename (remove timestamp if present)
            $originalName = $document->name;
            if (preg_match('/^\d+_(.+)$/', $originalName, $matches)) {
                $originalName = $matches[1];
            }
            
            $newFilePath = "{$newPath}/{$originalName}";
            
            if (!$this->dryRun) {
                // Check if old file exists
                if ($document->file_path && Storage::exists($document->file_path)) {
                    // Move file to new location
                    if (!Storage::exists($newFilePath)) {
                        Storage::move($document->file_path, $newFilePath);
                        $this->info("    ➡️  Moved: {$originalName} to {$category}/");
                    } else {
                        $this->warn("    ⚠️  File already exists at destination: {$originalName}");
                    }
                }
                
                // Update document record
                $document->update([
                    'name' => $originalName,
                    'file_path' => $newFilePath,
                    'storage_path' => storage_path("app/{$newFilePath}"),
                    'folder_structure' => json_encode([
                        'category' => $category,
                        'subcategory' => null
                    ]),
                    'sync_status' => 'pending',
                    'checksum' => null // Will be recalculated on next sync
                ]);
                
                $this->stats['documents_migrated']++;
            } else {
                $this->info("    [DRY-RUN] Would move: {$originalName} to {$category}/");
            }
            
        } catch (\Exception $e) {
            $this->error("    ❌ Error migrating document {$document->id}: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }
    
    private function determineCategory(ProjectDocument $document): string
    {
        $name = strtolower($document->name);
        $type = strtolower($document->type ?? '');
        
        // Check for document types
        if (str_contains($name, 'kontrak') || str_contains($name, 'contract')) {
            return 'dokumen/kontrak';
        }
        if (str_contains($name, 'izin') || str_contains($name, 'permit')) {
            return 'dokumen/perizinan';
        }
        if (str_contains($name, 'legal') || str_contains($name, 'hukum')) {
            return 'dokumen/legal';
        }
        
        // Check for technical documents
        if (str_contains($name, 'desain') || str_contains($name, 'design')) {
            return 'teknis/desain';
        }
        if (str_contains($name, 'spec') || str_contains($name, 'spesifikasi')) {
            return 'teknis/spesifikasi';
        }
        if (str_contains($name, 'gambar') || str_contains($name, 'drawing')) {
            return 'teknis/gambar';
        }
        
        // Check for financial documents
        if (str_contains($name, 'invoice') || str_contains($name, 'faktur')) {
            return 'keuangan/invoice';
        }
        if (str_contains($name, 'payment') || str_contains($name, 'pembayaran')) {
            return 'keuangan/pembayaran';
        }
        if (str_contains($name, 'laporan') && str_contains($name, 'keuangan')) {
            return 'keuangan/laporan';
        }
        
        // Check for reports
        if (str_contains($name, 'progress')) {
            return 'laporan/progress';
        }
        if (str_contains($name, 'mingguan') || str_contains($name, 'weekly')) {
            return 'laporan/mingguan';
        }
        if (str_contains($name, 'bulanan') || str_contains($name, 'monthly')) {
            return 'laporan/bulanan';
        }
        
        // Check for photos
        if (in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'bmp']) || 
            str_contains($name, 'foto') || str_contains($name, 'photo')) {
            
            if (str_contains($name, 'sebelum') || str_contains($name, 'before')) {
                return 'foto/sebelum';
            }
            if (str_contains($name, 'progress')) {
                return 'foto/progress';
            }
            if (str_contains($name, 'selesai') || str_contains($name, 'complete')) {
                return 'foto/selesai';
            }
            
            return 'foto/progress'; // Default for photos
        }
        
        // Default category
        return 'lainnya';
    }
    
    private function displayStats()
    {
        $this->newLine(2);
        $this->info('📊 Migration Statistics:');
        $this->info('------------------------');
        $this->info("Projects processed: {$this->stats['projects_processed']}");
        $this->info("Documents migrated: {$this->stats['documents_migrated']}");
        $this->info("Folders created: {$this->stats['folders_created']}");
        
        if ($this->stats['errors'] > 0) {
            $this->error("Errors encountered: {$this->stats['errors']}");
        }
        
        if ($this->dryRun) {
            $this->newLine();
            $this->warn('This was a DRY-RUN. No actual changes were made.');
            $this->info('Run without --dry-run to apply changes.');
        }
    }
}