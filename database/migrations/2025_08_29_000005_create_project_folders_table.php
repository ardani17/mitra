<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table already exists (for existing deployments)
        if (Schema::hasTable('project_folders')) {
            // Table already exists, skip creation
            return;
        }
        
        Schema::create('project_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('folder_name');
            $table->string('folder_path', 500);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('folder_type', ['root', 'category', 'subcategory', 'custom'])
                  ->default('custom');
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'out_of_sync'])
                  ->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Foreign key for parent folder
            $table->foreign('parent_id')->references('id')->on('project_folders')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['project_id', 'folder_path']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_folders');
    }
};