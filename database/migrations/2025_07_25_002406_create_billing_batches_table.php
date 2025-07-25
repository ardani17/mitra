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
        Schema::create('billing_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->string('invoice_number')->nullable();
            $table->string('tax_invoice_number')->nullable();
            $table->string('sp_number')->nullable();
            
            // Financial calculations
            $table->decimal('total_base_amount', 15, 2)->default(0);
            $table->decimal('pph_rate', 5, 2)->default(2.00); // PPh rate in percentage
            $table->decimal('pph_amount', 15, 2)->default(0);
            $table->decimal('ppn_rate', 5, 2)->default(11.00); // PPN rate in percentage
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('total_billing_amount', 15, 2)->default(0); // base + ppn
            $table->decimal('total_received_amount', 15, 2)->default(0); // billing - pph
            
            // Status and workflow
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
            ])->default('draft');
            
            // Important dates
            $table->date('billing_date');
            $table->datetime('sent_date')->nullable();
            $table->datetime('area_verification_date')->nullable();
            $table->datetime('area_revision_date')->nullable();
            $table->datetime('regional_verification_date')->nullable();
            $table->datetime('regional_revision_date')->nullable();
            $table->datetime('payment_entry_date')->nullable();
            $table->datetime('paid_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('billing_date');
            $table->index('batch_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_batches');
    }
};
