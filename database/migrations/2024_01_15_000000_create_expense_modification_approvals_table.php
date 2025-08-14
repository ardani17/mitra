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
        Schema::create('expense_modification_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('project_expenses')->onDelete('cascade');
            $table->enum('action_type', ['edit', 'delete']);
            $table->foreignId('requested_by')->constrained('users');
            $table->json('original_data')->nullable();
            $table->json('proposed_data')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['expense_id', 'action_type']);
            $table->index(['status', 'created_at']);
            $table->index(['requested_by', 'status']);
            $table->index(['approved_by', 'approved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_modification_approvals');
    }
};