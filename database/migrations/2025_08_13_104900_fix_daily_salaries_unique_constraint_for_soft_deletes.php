<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing unique constraint
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->dropUnique('unique_employee_work_date');
        });
        
        // Create a partial unique index that excludes soft-deleted records
        // This only applies to records where deleted_at IS NULL
        DB::statement('CREATE UNIQUE INDEX unique_employee_work_date_not_deleted ON daily_salaries (employee_id, work_date) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the partial unique index
        DB::statement('DROP INDEX IF EXISTS unique_employee_work_date_not_deleted');
        
        // Restore the original unique constraint
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->unique(['employee_id', 'work_date'], 'unique_employee_work_date');
        });
    }
};