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
        // For PostgreSQL, we need to drop and recreate the column
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'submitted', 'approved', 'rejected'])->default('pending')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->change();
        });
    }
};
