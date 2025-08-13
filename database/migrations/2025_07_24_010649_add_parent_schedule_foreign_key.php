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
            // Add parent_schedule_id column
            $table->foreignId('parent_schedule_id')->nullable()->after('is_final_termin');
            
            // Add foreign key constraint
            $table->foreign('parent_schedule_id')
                  ->references('id')
                  ->on('project_payment_schedules')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_billings', function (Blueprint $table) {
            $table->dropForeign(['parent_schedule_id']);
            $table->dropColumn('parent_schedule_id');
        });
    }
};