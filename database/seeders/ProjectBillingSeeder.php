<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectBilling;
use App\Models\Project;

class ProjectBillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil proyek yang sudah ada
        $projects = Project::all();
        
        if ($projects->isEmpty()) {
            $this->command->info('Tidak ada proyek yang tersedia. Membuat proyek terlebih dahulu...');
            return;
        }
        
        // Buat billing untuk setiap proyek yang ada
        foreach ($projects as $project) {
            // Buat 2-4 billing per proyek dengan status berbeda
            $billingCount = rand(2, 4);
            
            for ($i = 0; $i < $billingCount; $i++) {
                $nilaiJasa = rand(50000000, 200000000); // 50-200 juta
                $nilaiMaterial = rand(20000000, 100000000); // 20-100 juta
                $ppnAmount = ($nilaiJasa + $nilaiMaterial) * 0.11; // 11% PPN
                $totalAmount = $nilaiJasa + $nilaiMaterial + $ppnAmount;
                
                ProjectBilling::create([
                    'project_id' => $project->id,
                    'billing_date' => fake()->dateTimeBetween('-6 months', 'now'),
                    'nilai_jasa' => $nilaiJasa,
                    'nilai_material' => $nilaiMaterial,
                    'ppn_amount' => $ppnAmount,
                    'total_amount' => $totalAmount,
                    'status' => fake()->randomElement(['draft', 'sent', 'paid']),
                    'notes' => fake()->optional()->paragraph(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Project billing data berhasil dibuat untuk ' . $projects->count() . ' proyek.');
    }
}
