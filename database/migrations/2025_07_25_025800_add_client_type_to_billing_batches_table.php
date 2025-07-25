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
        Schema::table('billing_batches', function (Blueprint $table) {
            $table->enum('client_type', ['wapu', 'non_wapu'])->default('non_wapu')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_batches', function (Blueprint $table) {
            $table->dropColumn('client_type');
        });
    }
};
