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
        Schema::table('project_billings', function (Blueprint $table) {
            $table->foreignId('billing_batch_id')->nullable()->after('project_id')->constrained('billing_batches')->onDelete('set null');
            
            // Add fields for individual billing calculations (proportional from batch)
            $table->decimal('base_amount', 15, 2)->default(0)->after('billing_batch_id');
            $table->decimal('pph_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('received_amount', 15, 2)->default(0)->after('pph_amount');
            
            // Index for batch relationship
            $table->index('billing_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_billings', function (Blueprint $table) {
            $table->dropForeign(['billing_batch_id']);
            $table->dropIndex(['billing_batch_id']);
            $table->dropColumn([
                'billing_batch_id',
                'base_amount',
                'pph_amount',
                'received_amount'
            ]);
        });
    }
};
