<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ====================================================
        // IMPORTANT: Data master sudah dipindahkan ke migration!
        // ====================================================
        // Roles, Settings, CashflowCategories, dan Default Users
        // sudah otomatis terinstal melalui migration.
        // 
        // Migration files:
        // - 2025_08_29_000001_insert_default_roles.php
        // - 2025_08_29_000002_insert_default_settings.php  
        // - 2025_08_29_000003_insert_default_cashflow_categories.php
        // - 2025_08_29_000004_insert_default_users.php
        // ====================================================

        // Seeder berikut SUDAH TIDAK DIPERLUKAN di production:
        // - RoleSeeder::class (sudah di migration)
        // - SettingsSeeder::class (sudah di migration)
        // - CashflowCategorySeeder::class (sudah di migration)
        // - UserSeeder::class (sudah di migration)

        // ====================================================
        // DEVELOPMENT/TESTING ONLY
        // ====================================================
        // Uncomment baris di bawah HANYA untuk testing/development
        // JANGAN uncomment untuk production!
        
        if (app()->environment(['local', 'staging', 'testing'])) {
            $this->command->warn('Running seeders for TESTING environment...');
            
            // Uncomment seeder yang diperlukan untuk testing:
            // $this->call(ProjectBillingSeeder::class);
            // $this->call(ProjectTimelineSeeder::class);
            // $this->call(ProjectExpenseSeeder::class);
            // $this->call(ProjectRevenueSeeder::class);
            // $this->call(TestEmployeeSeeder::class);
            // $this->call(DailySalarySeeder::class);
            // $this->call(CurrentPeriodSalarySeeder::class);
            
            $this->command->info('Testing seeders completed.');
        } else {
            $this->command->info('Production environment detected. No seeders needed.');
            $this->command->info('Master data is automatically installed via migrations.');
        }
    }
}
