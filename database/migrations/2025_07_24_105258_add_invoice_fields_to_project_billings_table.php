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
            // Hapus kolom amount lama
            $table->dropColumn('amount');
            
            // Tambah kolom untuk invoice yang proper
            $table->decimal('nilai_jasa', 15, 2)->default(0)->after('project_id');
            $table->decimal('nilai_material', 15, 2)->default(0)->after('nilai_jasa');
            $table->decimal('subtotal', 15, 2)->default(0)->after('nilai_material');
            $table->decimal('ppn_rate', 5, 2)->default(11.00)->after('subtotal'); // Default 11%
            $table->enum('ppn_calculation', ['round_down', 'round_up', 'normal'])->default('normal')->after('ppn_rate');
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_calculation');
            $table->decimal('total_amount', 15, 2)->default(0)->after('ppn_amount');
            $table->string('invoice_number')->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_billings', function (Blueprint $table) {
            // Kembalikan kolom amount
            $table->decimal('amount', 15, 2)->after('project_id');
            
            // Hapus kolom invoice baru
            $table->dropColumn([
                'nilai_jasa',
                'nilai_material', 
                'subtotal',
                'ppn_rate',
                'ppn_calculation',
                'ppn_amount',
                'total_amount',
                'invoice_number'
            ]);
        });
    }
};
