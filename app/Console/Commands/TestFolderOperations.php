<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestFolderOperations extends Command
{
    protected $signature = 'test:folders';
    protected $description = 'Test folder operations';
    
    private $disk = 'local'; // Eksplisit gunakan disk 'local' untuk konsistensi

    public function handle()
    {
        $this->info("Testing Folder Operations");
        $this->info("=========================");
        
        $testProject = "test-project";
        $basePath = "proyek/{$testProject}";
        
        // Test 1: Create folder
        $this->info("\nTest 1: Creating test folder...");
        $testFolder = "{$basePath}/test-folder-" . time();
        
        if (Storage::disk($this->disk)->makeDirectory($testFolder)) {
            $this->info("✓ Folder created: {$testFolder}");
            
            // Verify it exists
            if (Storage::disk($this->disk)->exists($testFolder)) {
                $this->info("✓ Folder exists in filesystem");
            } else {
                $this->error("✗ Folder not found in filesystem");
            }
        } else {
            $this->error("✗ Failed to create folder");
        }
        
        // Test 2: Create subfolder
        $this->info("\nTest 2: Creating subfolder...");
        $subFolder = "{$testFolder}/subfolder";
        
        if (Storage::disk($this->disk)->makeDirectory($subFolder)) {
            $this->info("✓ Subfolder created: {$subFolder}");
        } else {
            $this->error("✗ Failed to create subfolder");
        }
        
        // Test 3: Rename folder (move)
        $this->info("\nTest 3: Renaming folder...");
        $newName = "{$basePath}/renamed-folder-" . time();
        
        if (Storage::disk($this->disk)->exists($testFolder) && Storage::disk($this->disk)->move($testFolder, $newName)) {
            $this->info("✓ Folder renamed from {$testFolder} to {$newName}");
            
            // Verify old path doesn't exist
            if (!Storage::disk($this->disk)->exists($testFolder)) {
                $this->info("✓ Old folder path removed");
            }
            
            // Verify new path exists
            if (Storage::disk($this->disk)->exists($newName)) {
                $this->info("✓ New folder path exists");
            }
        } else {
            $this->error("✗ Failed to rename folder");
        }
        
        // Test 4: Delete folder
        $this->info("\nTest 4: Deleting folder...");
        
        if (Storage::disk($this->disk)->deleteDirectory($newName)) {
            $this->info("✓ Folder deleted: {$newName}");
            
            // Verify it's gone
            if (!Storage::disk($this->disk)->exists($newName)) {
                $this->info("✓ Folder successfully removed from filesystem");
            }
        } else {
            $this->error("✗ Failed to delete folder");
        }
        
        $this->info("\n=========================");
        $this->info("Tests completed!");
        $this->info("Using disk: " . $this->disk);
        
        return 0;
    }
}