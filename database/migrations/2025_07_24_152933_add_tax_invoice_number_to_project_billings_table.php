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
            $table->string('tax_invoice_number')->nullable()->after('sp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_billings', function (Blueprint $table) {
            $table->dropColumn('tax_invoice_number');
        });
    }
};
