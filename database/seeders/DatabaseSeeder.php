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
        // Create roles first
        $this->call(RoleSeeder::class);
        
        // Create users with roles
        $this->call(UserSeeder::class);
        
        // CATATAN: Seeder data dummy untuk testing
        // Uncomment baris di bawah jika ingin mengisi data dummy untuk testing
        
        $this->call(ProjectBillingSeeder::class);
        $this->call(ProjectTimelineSeeder::class);
        $this->call(ProjectExpenseSeeder::class);
        $this->call(ProjectRevenueSeeder::class);
    }
}
