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
        Schema::create('daily_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date');
            $table->decimal('amount', 10, 2)->comment('Gaji harian');
            $table->decimal('hours_worked', 4, 2)->default(8)->comment('Jam kerja');
            $table->decimal('overtime_hours', 4, 2)->default(0)->comment('Jam lembur');
            $table->decimal('overtime_rate', 10, 2)->default(0)->comment('Rate lembur per jam');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint: satu karyawan hanya bisa punya satu record per tanggal
            $table->unique(['employee_id', 'work_date'], 'unique_employee_work_date');
            
            $table->index(['employee_id', 'work_date']);
            $table->index(['status']);
            $table->index(['work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_salaries');
    }
};
