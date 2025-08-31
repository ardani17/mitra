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
        // Debug: Check if table exists before trying to modify it
        if (!Schema::hasTable('project_documents')) {
            Log::error('Migration Error: project_documents table does not exist yet!');
            Log::error('This migration (2025_01_29_000001) is running before the table creation (2025_07_24_093843)');
            throw new \Exception('Cannot modify project_documents table - it does not exist. Check migration order!');
        }
        
        Log::info('project_documents table exists, proceeding with modifications...');
        
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
        Schema::table('project_documents', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['last_sync_at']);
            
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
};