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
        Schema::create('project_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            // Termin information
            $table->integer('termin_number');
            $table->integer('total_termin');
            $table->string('termin_name')->nullable(); // e.g., "Termin 1 - Down Payment"
            
            // Amount and percentage
            $table->decimal('percentage', 5, 2); // e.g., 30.00 for 30%
            $table->decimal('amount', 15, 2);
            
            // Dates
            $table->date('due_date');
            $table->date('created_date')->default(now());
            
            // Status
            $table->enum('status', ['pending', 'billed', 'paid', 'overdue'])->default('pending');
            
            // Reference to actual billing when created
            $table->foreignId('billing_id')->nullable()->constrained('project_billings')->onDelete('set null');
            
            // Notes and description
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['project_id', 'termin_number']);
            $table->index(['project_id', 'status']);
            $table->index(['due_date', 'status']);
            
            // Unique constraint to prevent duplicate termin numbers per project
            $table->unique(['project_id', 'termin_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_payment_schedules');
    }
};