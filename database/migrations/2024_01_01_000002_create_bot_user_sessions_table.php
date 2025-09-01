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
        Schema::create('bot_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id')->unique();
            $table->string('telegram_username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->bigInteger('chat_id');
            $table->foreignId('current_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('current_folder')->nullable();
            $table->string('last_command')->nullable();
            $table->json('session_data')->nullable();
            $table->string('state')->default('idle'); // idle, searching, uploading, etc
            $table->timestamp('last_activity_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('telegram_user_id');
            $table->index('chat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_user_sessions');
    }
};