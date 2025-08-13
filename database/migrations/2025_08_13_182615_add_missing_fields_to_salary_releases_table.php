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
        Schema::table('salary_releases', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('salary_releases', 'released_by')) {
                $table->unsignedBigInteger('released_by')->nullable()->after('notes');
                $table->foreign('released_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('salary_releases', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('released_by');
            }
            
            if (!Schema::hasColumn('salary_releases', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('released_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_releases', function (Blueprint $table) {
            if (Schema::hasColumn('salary_releases', 'released_by')) {
                $table->dropForeign(['released_by']);
                $table->dropColumn('released_by');
            }
            
            if (Schema::hasColumn('salary_releases', 'released_at')) {
                $table->dropColumn('released_at');
            }
            
            if (Schema::hasColumn('salary_releases', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
