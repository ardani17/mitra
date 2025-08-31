<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\SyncService;
use App\Services\RcloneService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProjectToCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:sync 
                            {--project= : Specific project ID to sync}
                            {--document= : Specific document ID to sync}
                            {--all : Sync all projects}
                            {--retry-failed : Retry all failed syncs}
                            {--verify : Verify sync integrity}
                            {--stats : Show sync statistics}
                            {--test : Test rclone connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually sync project documents to cloud storage using rclone';

    private $syncService;
    private $rcloneService;

    public function __construct(SyncService $syncService, RcloneService $rcloneService)
    {
        parent::__construct();
        $this->syncService = $syncService;
        $this->rcloneService = $rcloneService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Test rclone connection
        if ($this->option('test')) {
            return $this->testConnection();
        }
        
        // Show sync statistics
        if ($this->option('stats')) {
            return $this->showStatistics();
        }
        
        // Check if rclone is available
        if (!$this->rcloneService->isAvailable()) {
            $this->error('❌ Rclone is not available or not configured properly.');
            $this->info('Please ensure rclone is installed and configured with your cloud storage.');
            $this->info('Run: rclone config');
            return 1;
        }
        
        // Retry failed syncs
        if ($this->option('retry-failed')) {
            return $this->retryFailedSyncs();
        }
        
        // Verify sync integrity
        if ($this->option('verify')) {
            $projectId = $this->option('project');
            if (!$projectId) {
                $this->error('Please specify a project ID with --project option');
                return 1;
            }
            return $this->verifySyncIntegrity($projectId);
        }
        
        // Sync specific document
        if ($documentId = $this->option('document')) {
            return $this->syncDocument($documentId);
        }
        
        // Sync specific project
        if ($projectId = $this->option('project')) {
            return $this->syncProject($projectId);
        }
        
        // Sync all projects
        if ($this->option('all')) {
            return $this->syncAllProjects();
        }
        
        // No option specified
        $this->info('Please specify what to sync:');
        $this->info('  --project=ID    Sync specific project');
        $this->info('  --document=ID   Sync specific document');
        $this->info('  --all           Sync all projects');
        $this->info('  --retry-failed  Retry all failed syncs');
        $this->info('  --verify        Verify sync integrity');
        $this->info('  --stats         Show sync statistics');
        $this->info('  --test          Test rclone connection');
        
        return 0;
    }
    
    private function testConnection(): int
    {
        $this->info('🔍 Testing rclone connection...');

        $isAvailable = $this->rcloneService->isAvailable();

        if ($isAvailable) {
            $this->info('✅ Rclone is available and executable!');

            // Test the actual connection to remote
            $connectionTest = $this->rcloneService->testConnection();

            if ($connectionTest) {
                $this->info('✅ Rclone connection to remote successful!');
                $this->info('Remote: ' . $this->rcloneService->remoteName);
                return 0;
            } else {
                $this->error('❌ Rclone connection to remote failed!');
                $this->error('Please check your rclone configuration.');
                $this->info('Run: rclone config');
                return 1;
            }
        } else {
            $this->error('❌ Rclone is not available!');
            $this->error('Please ensure rclone is installed and accessible.');
            $this->info('Current path: ' . $this->rcloneService->binaryPath);
            return 1;
        }
    }
    
    private function showStatistics(): int
    {
        $this->info('📊 Sync Statistics');
        $this->info('==================');
        
        $stats = $this->syncService->getSyncStatistics();
        
        // Project statistics
        $this->newLine();
        $this->info('📁 Projects:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Projects', $stats['projects']['total']],
                ['Synced Projects', $stats['projects']['synced']],
                ['Sync Percentage', $stats['projects']['percentage'] . '%']
            ]
        );
        
        // Document statistics
        $this->newLine();
        $this->info('📄 Documents:');
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Total', $stats['documents']['total'], '100%'],
                ['Synced', $stats['documents']['synced'], $stats['documents']['percentage'] . '%'],
                ['Pending', $stats['documents']['pending'], 
                    $stats['documents']['total'] > 0 
                        ? round(($stats['documents']['pending'] / $stats['documents']['total']) * 100, 2) . '%'
                        : '0%'],
                ['Failed', $stats['documents']['failed'],
                    $stats['documents']['total'] > 0 
                        ? round(($stats['documents']['failed'] / $stats['documents']['total']) * 100, 2) . '%'
                        : '0%']
            ]
        );
        
        // Storage statistics
        $this->newLine();
        $this->info('💾 Storage:');
        $this->table(
            ['Location', 'Size', 'Files'],
            [
                ['Local (Synced)', $stats['storage']['local_size_formatted'], $stats['documents']['synced']],
                ['Remote', $stats['storage']['remote_size_formatted'], $stats['storage']['remote_file_count']]
            ]
        );
        
        // Recent syncs
        if ($stats['recent_syncs']->count() > 0) {
            $this->newLine();
            $this->info('🕐 Recent Sync Operations:');
            
            $recentData = $stats['recent_syncs']->map(function($log) {
                return [
                    $log->created_at->format('Y-m-d H:i'),
                    $log->syncable_type === 'App\Models\Project' ? 'Project' : 'Document',
                    $log->syncable_id,
                    $log->status,
                    $log->formatted_duration ?? '-'
                ];
            })->toArray();
            
            $this->table(
                ['Date', 'Type', 'ID', 'Status', 'Duration'],
                $recentData
            );
        }
        
        return 0;
    }
    
    private function syncDocument($documentId): int
    {
        $document = ProjectDocument::find($documentId);
        
        if (!$document) {
            $this->error("Document with ID {$documentId} not found");
            return 1;
        }
        
        $this->info("📄 Syncing document: {$document->name}");
        $this->info("Project: {$document->project->name}");
        
        $bar = $this->output->createProgressBar(1);
        $bar->start();
        
        $success = $this->syncService->syncDocument($document);
        
        $bar->finish();
        $this->newLine(2);
        
        if ($success) {
            $this->info('✅ Document synced successfully!');
            return 0;
        } else {
            $this->error('❌ Document sync failed!');
            $document->refresh();
            if ($document->sync_error) {
                $this->error('Error: ' . $document->sync_error);
            }
            return 1;
        }
    }
    
    private function syncProject($projectId): int
    {
        $project = Project::find($projectId);
        
        if (!$project) {
            $this->error("Project with ID {$projectId} not found");
            return 1;
        }
        
        $this->info("📁 Syncing project: {$project->name}");
        
        $documentCount = $project->documents->count();
        
        if ($documentCount === 0) {
            $this->warn('No documents to sync in this project');
            return 0;
        }
        
        $this->info("Documents to sync: {$documentCount}");
        
        if (!$this->confirm('Do you want to proceed with the sync?')) {
            return 0;
        }
        
        $this->info('⏳ Starting sync process...');
        
        $startTime = microtime(true);
        $result = $this->syncService->syncProject($project);
        $duration = microtime(true) - $startTime;
        
        $this->newLine();
        
        if ($result['success']) {
            $this->info('✅ Project sync completed successfully!');
            $this->info('Duration: ' . round($duration, 2) . ' seconds');
            
            // Show sync status
            $status = $this->syncService->checkSyncStatus($project);
            $this->table(
                ['Status', 'Count'],
                [
                    ['Synced', $status['stats']['synced']],
                    ['Failed', $status['stats']['failed']],
                    ['Pending', $status['stats']['pending']]
                ]
            );
            
            return 0;
        } else {
            $this->error('❌ Project sync failed!');
            $this->error('Error: ' . $result['message']);
            return 1;
        }
    }
    
    private function syncAllProjects(): int
    {
        $projects = Project::has('documents')->get();
        
        if ($projects->count() === 0) {
            $this->warn('No projects with documents to sync');
            return 0;
        }
        
        $this->info("Found {$projects->count()} projects to sync");
        
        if (!$this->confirm('This will sync ALL projects. Continue?')) {
            return 0;
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($projects as $project) {
            $this->newLine();
            $this->info("📁 Syncing project: {$project->name}");
            
            $result = $this->syncService->syncProject($project);
            
            if ($result['success']) {
                $successCount++;
                $this->info('  ✅ Success');
            } else {
                $failCount++;
                $this->error('  ❌ Failed: ' . $result['message']);
            }
        }
        
        $this->newLine(2);
        $this->info('📊 Sync Summary:');
        $this->info("  Successful: {$successCount}");
        
        if ($failCount > 0) {
            $this->error("  Failed: {$failCount}");
            return 1;
        }
        
        return 0;
    }
    
    private function retryFailedSyncs(): int
    {
        $this->info('🔄 Retrying failed syncs...');
        
        $projectId = $this->option('project');
        $project = null;
        
        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                $this->error("Project with ID {$projectId} not found");
                return 1;
            }
            $this->info("Project: {$project->name}");
        } else {
            $this->info("Retrying all failed syncs across all projects");
        }
        
        $results = $this->syncService->retryFailedSyncs($project);
        
        $this->newLine();
        $this->info('📊 Retry Results:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Failed', $results['total']],
                ['Successfully Synced', $results['success']],
                ['Still Failed', $results['failed']]
            ]
        );
        
        if ($results['failed'] > 0) {
            $this->warn('Some documents still failed to sync. Check logs for details.');
            return 1;
        }
        
        return 0;
    }
    
    private function verifySyncIntegrity($projectId): int
    {
        $project = Project::find($projectId);
        
        if (!$project) {
            $this->error("Project with ID {$projectId} not found");
            return 1;
        }
        
        $this->info("🔍 Verifying sync integrity for: {$project->name}");
        $this->info('Checking remote files and comparing with local...');
        
        $bar = $this->output->createProgressBar($project->documents->count());
        $bar->start();
        
        $result = $this->syncService->verifySyncIntegrity($project);
        
        $bar->finish();
        $this->newLine(2);
        
        if (!$result['has_issues']) {
            $this->info('✅ All synced files are verified and intact!');
            return 0;
        } else {
            $this->warn("⚠️  Found {$result['issue_count']} integrity issues:");
            
            $this->table(
                ['Document ID', 'Name', 'Issue'],
                collect($result['issues'])->map(function($issue) {
                    return [
                        $issue['document_id'],
                        $issue['name'],
                        $issue['issue']
                    ];
                })->toArray()
            );
            
            if ($this->confirm('Do you want to re-sync these documents?')) {
                foreach ($result['issues'] as $issue) {
                    $document = ProjectDocument::find($issue['document_id']);
                    if ($document) {
                        $this->info("Re-syncing: {$document->name}");
                        $this->syncService->syncDocument($document);
                    }
                }
                $this->info('✅ Re-sync completed!');
            }
            
            return 1;
        }
    }
}