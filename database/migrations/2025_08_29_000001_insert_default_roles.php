<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = [
            [
                'name' => 'direktur',
                'description' => 'Direktur perusahaan dengan akses penuh',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'project_manager',
                'description' => 'Manager proyek dengan akses manajemen proyek',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'finance_manager',
                'description' => 'Manager keuangan dengan akses manajemen keuangan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'staf',
                'description' => 'Staf dengan akses input data dasar',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($roles as $role) {
            // Check if role doesn't exist before inserting
            if (!DB::table('roles')->where('name', $role['name'])->exists()) {
                DB::table('roles')->insert($role);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete roles on rollback as they might have users assigned
        // This is a safety measure to prevent breaking existing data
    }
};