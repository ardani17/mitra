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
        Schema::create('bot_user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Reference to bot_users.id');
            $table->bigInteger('telegram_id')->comment('Telegram user ID for tracking');
            $table->string('action', 100)->comment('Action performed (e.g., login, register, command_executed)');
            $table->json('details')->nullable()->comment('Additional details about the action');
            $table->string('ip_address', 45)->nullable()->comment('IP address if available');
            $table->text('user_agent')->nullable()->comment('User agent if available');
            $table->string('command', 100)->nullable()->comment('Bot command if applicable');
            $table->text('command_params')->nullable()->comment('Command parameters');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->text('error_message')->nullable()->comment('Error message if action failed');
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for better query performance
            $table->index('user_id');
            $table->index('telegram_id');
            $table->index('action');
            $table->index('status');
            $table->index('created_at');
            $table->index(['telegram_id', 'created_at']);
            $table->index(['action', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_user_activity_logs');
    }
};
