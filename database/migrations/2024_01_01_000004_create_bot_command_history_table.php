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
        Schema::create('bot_command_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id');
            $table->string('telegram_username')->nullable();
            $table->bigInteger('chat_id');
            $table->string('command');
            $table->json('parameters')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('result_status'); // success, failed, cancelled
            $table->text('result_message')->nullable();
            $table->integer('execution_time_ms')->nullable();
            $table->timestamps();
            
            $table->index(['telegram_user_id', 'created_at']);
            $table->index('command');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_command_history');
    }
};