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
        // Untuk PostgreSQL, kita perlu drop dan recreate kolom
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('type', ['konstruksi', 'maintenance', 'psb', 'other'])->default('other')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum sebelumnya
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('type', ['konstruksi', 'maintenance', 'other'])->default('other')->after('description');
        });
    }
};
