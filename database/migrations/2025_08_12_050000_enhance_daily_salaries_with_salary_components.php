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
            // Komponen gaji harian
            $table->decimal('basic_salary', 10, 2)->default(0)->after('amount')->comment('Gaji pokok harian');
            $table->decimal('meal_allowance', 10, 2)->default(0)->after('basic_salary')->comment('Uang makan');
            $table->decimal('attendance_bonus', 10, 2)->default(0)->after('meal_allowance')->comment('Uang absen (bonus jika tepat waktu)');
            $table->decimal('phone_allowance', 10, 2)->default(0)->after('attendance_bonus')->comment('Uang pulsa');
            $table->decimal('transport_allowance', 10, 2)->default(0)->after('phone_allowance')->comment('Uang transport');
            
            // Status kehadiran untuk menentukan bonus/potongan absen
            $table->enum('attendance_status', ['present', 'late', 'absent', 'sick', 'leave'])->default('present')->after('transport_allowance')->comment('Status kehadiran');
            $table->time('check_in_time')->nullable()->after('attendance_status')->comment('Waktu masuk');
            $table->time('check_out_time')->nullable()->after('check_in_time')->comment('Waktu pulang');
            
            // Potongan
            $table->decimal('deductions', 10, 2)->default(0)->after('check_out_time')->comment('Potongan (telat, dll)');
            
            // Update kolom amount menjadi calculated field
            $table->decimal('total_amount', 10, 2)->default(0)->after('deductions')->comment('Total gaji harian (calculated)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary',
                'meal_allowance', 
                'attendance_bonus',
                'phone_allowance',
                'transport_allowance',
                'attendance_status',
                'check_in_time',
                'check_out_time',
                'deductions',
                'total_amount'
            ]);
        });
    }
};