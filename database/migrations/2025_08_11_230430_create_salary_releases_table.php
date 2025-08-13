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
        Schema::create('salary_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('release_code', 50)->unique()->comment('Kode rilis gaji');
            $table->date('period_start')->comment('Tanggal mulai periode');
            $table->date('period_end')->comment('Tanggal akhir periode');
            $table->integer('total_days')->default(0)->comment('Total hari kerja');
            $table->decimal('total_amount', 12, 2)->comment('Total gaji sebelum potongan');
            $table->decimal('deductions', 10, 2)->default(0)->comment('Total potongan');
            $table->decimal('net_amount', 12, 2)->comment('Gaji bersih setelah potongan');
            $table->date('release_date')->nullable()->comment('Tanggal rilis gaji');
            $table->enum('status', ['draft', 'released', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('cashflow_entry_id')->nullable()->constrained('cashflow_entries')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('released_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['employee_id', 'period_start', 'period_end']);
            $table->index(['status']);
            $table->index(['release_date']);
            $table->index(['release_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_releases');
    }
};
