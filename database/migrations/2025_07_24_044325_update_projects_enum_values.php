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
        // Drop existing columns and recreate with new enum values
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['type', 'status']);
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('type', ['konstruksi', 'maintenance', 'other'])->default('other')->after('description');
            $table->enum('status', ['planning', 'in_progress', 'completed', 'cancelled'])->default('planning')->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['type', 'status']);
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('type', ['fiber_optic', 'pole_planting', 'tower_installation', 'other'])->default('other')->after('description');
            $table->enum('status', ['draft', 'planning', 'on_progress', 'on_hold', 'completed', 'billed', 'paid'])->default('draft')->after('end_date');
        });
    }
};
