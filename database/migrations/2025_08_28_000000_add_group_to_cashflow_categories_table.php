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
        Schema::table('cashflow_categories', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->after('type');
            $table->integer('sort_order')->default(0)->after('is_system');
            $table->index('group');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflow_categories', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn('group');
            $table->dropColumn('sort_order');
        });
    }
};