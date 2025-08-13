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
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_release_id')->nullable()->after('notes');
            $table->foreign('salary_release_id')->references('id')->on('salary_releases')->onDelete('set null');
            $table->index('salary_release_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_salaries', function (Blueprint $table) {
            $table->dropForeign(['salary_release_id']);
            $table->dropIndex(['salary_release_id']);
            $table->dropColumn('salary_release_id');
        });
    }
};
