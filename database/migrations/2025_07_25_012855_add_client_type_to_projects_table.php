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
            $table->enum('client_type', ['non_wapu', 'wapu'])
                  ->default('non_wapu')
                  ->after('client_name')
                  ->comment('Tipe klien: non_wapu (umum) atau wapu (BUMN/Pemerintah)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('client_type');
        });
    }
};
