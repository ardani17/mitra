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
        Schema::table('bot_registration_requests', function (Blueprint $table) {
            // Add processed_at and processed_by columns
            $table->timestamp('processed_at')->nullable()->after('reviewed_at');
            $table->bigInteger('processed_by')->nullable()->after('processed_at')->comment('User ID who processed the request');
            
            // Also add message and notes columns if they don't exist
            if (!Schema::hasColumn('bot_registration_requests', 'message')) {
                $table->text('message')->nullable()->after('additional_info')->comment('Message from user during registration');
            }
            
            if (!Schema::hasColumn('bot_registration_requests', 'notes')) {
                $table->text('notes')->nullable()->after('review_note')->comment('Admin notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_registration_requests', function (Blueprint $table) {
            $table->dropColumn(['processed_at', 'processed_by']);
            
            if (Schema::hasColumn('bot_registration_requests', 'message')) {
                $table->dropColumn('message');
            }
            
            if (Schema::hasColumn('bot_registration_requests', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};