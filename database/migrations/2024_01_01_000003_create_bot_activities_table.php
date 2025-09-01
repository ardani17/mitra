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
        Schema::create('bot_activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id');
            $table->string('telegram_username')->nullable();
            $table->bigInteger('chat_id');
            $table->string('message_type'); // text, command, file, photo, video
            $table->text('message_text')->nullable();
            $table->string('command')->nullable();
            $table->json('command_params')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_path')->nullable();
            $table->string('telegram_file_id')->nullable();
            $table->string('telegram_original_path')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('status'); // success, failed, pending
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->timestamps();
            
            $table->index('telegram_user_id');
            $table->index('chat_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_activities');
    }
};