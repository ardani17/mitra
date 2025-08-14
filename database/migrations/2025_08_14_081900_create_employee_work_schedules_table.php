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
        Schema::create('employee_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('schedule_type', ['standard', 'custom', 'flexible'])->default('standard');
            $table->integer('work_days_per_month')->nullable()->comment('Jumlah hari kerja per bulan untuk tipe flexible');
            $table->json('standard_off_days')->nullable()->comment('Hari libur tetap: [0,6] untuk Minggu,Sabtu');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active'], 'idx_employee_active');
            $table->index(['effective_from', 'effective_until'], 'idx_effective_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_work_schedules');
    }
};