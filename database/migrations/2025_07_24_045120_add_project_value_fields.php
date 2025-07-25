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
        Schema::table('projects', function (Blueprint $table) {
            // Nilai jasa, material, dan total untuk plan
            $table->decimal('planned_service_value', 15, 2)->default(0)->after('planned_budget');
            $table->decimal('planned_material_value', 15, 2)->default(0)->after('planned_service_value');
            $table->decimal('planned_total_value', 15, 2)->default(0)->after('planned_material_value');
            
            // Nilai akhir proyek (actual values)
            $table->decimal('final_service_value', 15, 2)->nullable()->after('actual_budget');
            $table->decimal('final_material_value', 15, 2)->nullable()->after('final_service_value');
            $table->decimal('final_total_value', 15, 2)->nullable()->after('final_material_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'planned_service_value',
                'planned_material_value', 
                'planned_total_value',
                'final_service_value',
                'final_material_value',
                'final_total_value'
            ]);
        });
    }
};
