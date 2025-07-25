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
        // First, update any existing NULL values to 0
        DB::table('projects')->whereNull('planned_service_value')->update(['planned_service_value' => 0]);
        DB::table('projects')->whereNull('planned_material_value')->update(['planned_material_value' => 0]);
        DB::table('projects')->whereNull('planned_total_value')->update(['planned_total_value' => 0]);
        
        // Then modify the columns to be NOT NULL with default 0
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('planned_service_value', 15, 2)->default(0)->nullable(false)->change();
            $table->decimal('planned_material_value', 15, 2)->default(0)->nullable(false)->change();
            $table->decimal('planned_total_value', 15, 2)->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('planned_service_value', 15, 2)->nullable()->change();
            $table->decimal('planned_material_value', 15, 2)->nullable()->change();
            $table->decimal('planned_total_value', 15, 2)->nullable()->change();
        });
    }
};
