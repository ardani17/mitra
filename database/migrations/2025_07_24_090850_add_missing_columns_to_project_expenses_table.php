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
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('expense_date');
            $table->string('receipt_number')->nullable()->after('category');
            $table->string('vendor')->nullable()->after('receipt_number');
            $table->text('notes')->nullable()->after('vendor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_expenses', function (Blueprint $table) {
            $table->dropColumn(['category', 'receipt_number', 'vendor', 'notes']);
        });
    }
};
