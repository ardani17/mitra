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
        Schema::create('employee_custom_off_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('off_date');
            $table->string('reason')->nullable()->comment('Alasan libur: cuti, libur custom, dll');
            $table->integer('period_month')->comment('Bulan periode (1-12)');
            $table->integer('period_year')->comment('Tahun periode');
            $table->timestamps();
            
            $table->unique(['employee_id', 'off_date'], 'unique_employee_off_date');
            $table->index(['employee_id', 'period_year', 'period_month'], 'idx_employee_period');
            $table->index(['off_date'], 'idx_off_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_custom_off_days');
    }
};