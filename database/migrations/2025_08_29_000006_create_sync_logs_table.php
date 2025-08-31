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
        if (Schema::hasTable('sync_logs')) {
            // Table already exists, skip creation
            return;
        }
        
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->enum('sync_type', ['upload', 'download', 'delete', 'rename', 'move']);
            $table->enum('sync_status', ['pending', 'in_progress', 'completed', 'failed']);
            $table->string('source_path', 500)->nullable();
            $table->string('destination_path', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['project_id', 'sync_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};