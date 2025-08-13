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
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->decimal('overtime_amount', 10, 2)->default(0)->after('overtime_rate')->comment('Jumlah uang lembur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->dropColumn('overtime_amount');
        });
    }
};