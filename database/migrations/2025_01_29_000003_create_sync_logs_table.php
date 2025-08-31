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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('syncable_type', 50);
            $table->unsignedBigInteger('syncable_id');
            $table->enum('action', ['upload', 'download', 'delete', 'check']);
            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->string('source_path', 500)->nullable();
            $table->string('destination_path', 500)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->text('rclone_output')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for better performance
            $table->index(['syncable_type', 'syncable_id']);
            $table->index('created_at');
            $table->index('status');
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