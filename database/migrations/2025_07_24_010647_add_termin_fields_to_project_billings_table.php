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
            // Payment type: 'full' or 'termin'
            $table->enum('payment_type', ['full', 'termin'])->default('full')->after('status');
            
            // Termin information
            $table->integer('termin_number')->nullable()->after('payment_type');
            $table->integer('total_termin')->nullable()->after('termin_number');
            $table->boolean('is_final_termin')->default(false)->after('total_termin');
            
            // Parent schedule reference for termin payments (will be added after project_payment_schedules table is created)
            // $table->foreignId('parent_schedule_id')->nullable()->constrained('project_payment_schedules')->onDelete('cascade')->after('is_final_termin');
            
            // Add index for better performance
            $table->index(['payment_type', 'termin_number']);
            $table->index(['project_id', 'payment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_billings', function (Blueprint $table) {
            // $table->dropForeign(['parent_schedule_id']);
            $table->dropIndex(['payment_type', 'termin_number']);
            $table->dropIndex(['project_id', 'payment_type']);
            $table->dropColumn([
                'payment_type',
                'termin_number',
                'total_termin',
                'is_final_termin'
                // 'parent_schedule_id'
            ]);
        });
    }
};