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
        Schema::create('bot_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('bot_name');
            $table->string('bot_token');
            $table->string('bot_username')->nullable();
            $table->string('server_host')->default('localhost');
            $table->integer('server_port')->default(8081);
            
            // Path configurations
            $table->string('bot_api_base_path', 500)->default('/var/lib/telegram-bot-api');
            $table->string('bot_api_temp_path', 500)->nullable();
            $table->string('bot_api_documents_path', 500)->nullable();
            $table->string('bot_api_photos_path', 500)->nullable();
            $table->string('bot_api_videos_path', 500)->nullable();
            
            // Laravel paths
            $table->string('laravel_storage_path', 500)->default('storage/app/proyek');
            
            // Other configs
            $table->boolean('use_local_server')->default(true);
            $table->string('webhook_url')->nullable();
            $table->integer('max_file_size_mb')->default(2000);
            $table->json('allowed_users')->nullable();
            $table->boolean('auto_cleanup')->default(true);
            $table->integer('cleanup_after_hours')->default(24);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_configurations');
    }
};