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
        Schema::create('billing_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_batch_id')->constrained('billing_batches')->onDelete('cascade');
            $table->enum('status', [
                'draft', 
                'sent', 
                'area_verification', 
                'area_revision',
                'regional_verification', 
                'regional_revision',
                'payment_entry_ho',
                'paid',
                'cancelled'
            ]);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['billing_batch_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_status_logs');
    }
};
