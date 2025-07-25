<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectRevenue;
use App\Models\Project;

class ProjectRevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        
        foreach ($projects as $project) {
            // Buat 1-3 revenue per proyek
            $revenueCount = rand(1, 3);
            
            for ($i = 0; $i < $revenueCount; $i++) {
                $totalAmount = rand(100000000, 500000000); // 100-500 juta
                $netProfit = rand(10000000, 100000000); // 10-100 juta
                $profitMargin = ($netProfit / $totalAmount) * 100;
                
                ProjectRevenue::create([
                    'project_id' => $project->id,
                    'total_amount' => $totalAmount,
                    'net_profit' => $netProfit,
                    'profit_margin' => $profitMargin,
                    'revenue_date' => fake()->dateTimeBetween($project->start_date, $project->end_date ?? 'now'),
                    'calculation_details' => $this->getCalculationDetails($totalAmount, $netProfit),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function getCalculationDetails(int $totalAmount, int $netProfit): string
    {
        $totalExpenses = $totalAmount - $netProfit;
        
        return json_encode([
            'total_revenue' => $totalAmount,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => round(($netProfit / $totalAmount) * 100, 2) . '%',
            'calculation_date' => now()->format('Y-m-d H:i:s')
        ]);
    }
    
    private function getRevenueDescription(int $sequence): string
    {
        $descriptions = [
            "Pembayaran termin {$sequence} - Jasa instalasi",
            "Pembayaran termin {$sequence} - Material dan peralatan",
            "Pembayaran termin {$sequence} - Penyelesaian proyek",
            "Pembayaran progress {$sequence} - Konstruksi",
            "Pembayaran milestone {$sequence} - Testing dan commissioning"
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
}
