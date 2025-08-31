<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists before trying to modify it
        if (!Schema::hasTable('project_documents')) {
            Log::error('Migration Error: project_documents table does not exist yet!');
            Log::error('This migration (update_project_documents_for_storage_system) is running before the table creation');
            throw new \Exception('Cannot modify project_documents table - it does not exist. Check migration order!');
        }
        
        // Check if columns already exist (for existing deployments that may have run old migration)
        if (Schema::hasColumn('project_documents', 'storage_path')) {
            Log::info('Storage system columns already exist in project_documents table, skipping...');
            return;
        }
        
        Log::info('Adding storage system columns to project_documents table...');
        
        Schema::table('project_documents', function (Blueprint $table) {
            // Add new columns for storage system
            $table->string('storage_path', 500)->nullable()->after('file_path');
            $table->string('rclone_path', 500)->nullable()->after('storage_path');
            $table->enum('sync_status', ['pending', 'syncing', 'synced', 'failed', 'out_of_sync'])
                  ->default('pending')
                  ->after('rclone_path');
            $table->text('sync_error')->nullable()->after('sync_status');
            $table->timestamp('last_sync_at')->nullable()->after('sync_error');
            $table->string('checksum', 64)->nullable()->after('last_sync_at');
            $table->json('folder_structure')->nullable()->after('checksum');
            
            // Add indexes for better performance
            $table->index('sync_status');
            $table->index('last_sync_at');
        });
        
        Log::info('Successfully updated project_documents table with storage system columns');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if columns exist before trying to drop them
        if (!Schema::hasColumn('project_documents', 'storage_path')) {
            return;
        }
        
        Schema::table('project_documents', function (Blueprint $table) {
            // Drop indexes first if they exist
            if (Schema::hasIndex('project_documents', 'project_documents_sync_status_index')) {
                $table->dropIndex(['sync_status']);
            }
            if (Schema::hasIndex('project_documents', 'project_documents_last_sync_at_index')) {
                $table->dropIndex(['last_sync_at']);
            }
            
            // Drop columns
            $table->dropColumn([
                'storage_path',
                'rclone_path',
                'sync_status',
                'sync_error',
                'last_sync_at',
                'checksum',
                'folder_structure'
            ]);
        });
    }
    
    /**
     * Helper method to check if an index exists
     */
    private function hasIndex($table, $indexName): bool
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        
        $sql = "SELECT COUNT(*) as count FROM information_schema.statistics 
                WHERE table_schema = ? AND table_name = ? AND index_name = ?";
        
        $result = $connection->select($sql, [$dbName, $table, $indexName]);
        
        return $result[0]->count > 0;
    }
};