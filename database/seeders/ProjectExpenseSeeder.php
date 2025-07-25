<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectExpense;
use App\Models\Project;
use App\Models\User;

class ProjectExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();
        
        foreach ($projects as $project) {
            // Buat 3-8 expense per proyek
            $expenseCount = rand(3, 8);
            
            for ($i = 0; $i < $expenseCount; $i++) {
                $amount = rand(5000000, 50000000); // 5-50 juta
                $status = ['pending', 'approved', 'rejected'][rand(0, 2)];
                
                ProjectExpense::create([
                    'project_id' => $project->id,
                    'user_id' => $users->random()->id,
                    'description' => $this->getExpenseDescription(),
                    'amount' => $amount,
                    'expense_date' => fake()->dateTimeBetween($project->start_date, $project->end_date ?? 'now'),
                    'status' => ['draft', 'submitted', 'approved', 'rejected'][rand(0, 3)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function getExpenseDescription(): string
    {
        $descriptions = [
            'Pembelian kabel fiber optic',
            'Biaya transportasi tim',
            'Upah pekerja harian',
            'Sewa alat berat',
            'Pembelian material tiang',
            'Biaya konsumsi tim',
            'Pembelian peralatan safety',
            'Biaya perizinan',
            'Pembelian connector',
            'Biaya maintenance peralatan',
            'Pembelian spare parts',
            'Biaya akomodasi tim',
            'Pembelian bahan bakar',
            'Biaya sertifikasi',
            'Pembelian tools khusus'
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
}
