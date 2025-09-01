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
        Schema::create('bot_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('reason')->nullable()->comment('Reason for requesting access');
            $table->json('additional_info')->nullable()->comment('Additional information provided during registration');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->bigInteger('reviewed_by')->nullable()->comment('ID of the admin who reviewed the request');
            $table->text('review_note')->nullable()->comment('Note from reviewer');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status');
            $table->index('telegram_id');
            $table->index(['status', 'requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_registration_requests');
    }
};
