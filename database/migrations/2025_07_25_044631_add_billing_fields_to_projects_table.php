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
        Schema::table('projects', function (Blueprint $table) {
            // Status tagihan proyek
            $table->enum('billing_status', ['not_billed', 'partially_billed', 'fully_billed'])
                  ->default('not_billed')
                  ->after('status');
            
            // Dokumen tagihan terakhir
            $table->string('latest_po_number')->nullable()->after('billing_status');
            $table->string('latest_sp_number')->nullable()->after('latest_po_number');
            $table->string('latest_invoice_number')->nullable()->after('latest_sp_number');
            
            // Informasi finansial tagihan
            $table->decimal('total_billed_amount', 15, 2)->default(0)->after('latest_invoice_number');
            $table->decimal('billing_percentage', 5, 2)->default(0)->after('total_billed_amount');
            
            // Tanggal tagihan terakhir
            $table->date('last_billing_date')->nullable()->after('billing_percentage');
            
            // Index untuk performa query
            $table->index('billing_status');
            $table->index('last_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['billing_status']);
            $table->dropIndex(['last_billing_date']);
            
            $table->dropColumn([
                'billing_status',
                'latest_po_number',
                'latest_sp_number',
                'latest_invoice_number',
                'total_billed_amount',
                'billing_percentage',
                'last_billing_date'
            ]);
        });
    }
};
