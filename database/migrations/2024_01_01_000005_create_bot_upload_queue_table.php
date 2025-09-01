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
        Schema::create('bot_upload_queue', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id');
            $table->string('telegram_username')->nullable();
            $table->bigInteger('chat_id');
            $table->string('telegram_file_id');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size');
            $table->string('file_type'); // document, photo, video
            $table->string('bot_api_path')->nullable();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('target_folder')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index(['telegram_user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_upload_queue');
    }
};