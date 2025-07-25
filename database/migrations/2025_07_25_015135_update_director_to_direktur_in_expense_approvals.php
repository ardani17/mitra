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
        // Untuk PostgreSQL, kita perlu drop constraint lama dan buat yang baru
        DB::statement("ALTER TABLE expense_approvals DROP CONSTRAINT IF EXISTS expense_approvals_level_check");
        
        // Update existing records yang menggunakan 'director' menjadi 'direktur'
        DB::table('expense_approvals')
            ->where('level', 'director')
            ->update(['level' => 'direktur']);
        
        // Tambah constraint baru dengan nilai yang benar
        DB::statement("ALTER TABLE expense_approvals ADD CONSTRAINT expense_approvals_level_check CHECK (level IN ('finance_manager', 'project_manager', 'direktur'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraint baru
        DB::statement("ALTER TABLE expense_approvals DROP CONSTRAINT IF EXISTS expense_approvals_level_check");
        
        // Kembalikan ke 'director'
        DB::table('expense_approvals')
            ->where('level', 'direktur')
            ->update(['level' => 'director']);
        
        // Tambah constraint lama
        DB::statement("ALTER TABLE expense_approvals ADD CONSTRAINT expense_approvals_level_check CHECK (level IN ('finance_manager', 'project_manager', 'director'))");
    }
};
