<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\DailySalary;
use App\Models\Setting;
use Carbon\Carbon;

class CurrentPeriodSalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current cut-off settings
        $startDay = (int) $this->getSetting('salary_cutoff_start_day', 11);
        $endDay = (int) $this->getSetting('salary_cutoff_end_day', 10);
        
        $today = Carbon::now();
        
        // Calculate current period based on cut-off
        if ($today->day >= $startDay) {
            // Current period: startDay of this month to endDay of next month
            $periodStart = $today->copy()->day($startDay);
            $periodEnd = $today->copy()->addMonth()->day($endDay);
        } else {
            // Current period: startDay of last month to endDay of this month
            $periodStart = $today->copy()->subMonth()->day($startDay);
            $periodEnd = $today->copy()->day($endDay);
        }
        
        $this->command->info("Current period: {$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')}");
        
        // Get all employees
        $employees = Employee::all();
        
        if ($employees->count() === 0) {
            $this->command->error('No employees found! Please run TestEmployeeSeeder first.');
            return;
        }
        
        foreach ($employees as $employee) {
            // Clear existing salary data for this period
            DailySalary::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$periodStart, $periodEnd])
                ->delete();
            
            // Create salary records for recent workdays in current period
            $createdDays = 0;
            
            // Start from today and go backwards, but also include some future dates for testing
            for ($i = -2; $i <= 5 && $createdDays < 8; $i++) {
                $date = $today->copy()->addDays($i);
                
                // Only create for workdays and within period
                if (!$date->isWeekend() && $date >= $periodStart && $date <= $periodEnd) {
                    DailySalary::create([
                        'employee_id' => $employee->id,
                        'work_date' => $date,
                        'amount' => $employee->daily_rate,
                        'hours_worked' => 8.00,
                        'basic_salary' => $employee->daily_rate,
                        'meal_allowance' => 0,
                        'attendance_bonus' => 0,
                        'phone_allowance' => 0,
                        'transport_allowance' => 0,
                        'overtime_hours' => 0,
                        'overtime_amount' => 0,
                        'deductions' => 0,
                        'total_amount' => $employee->daily_rate,
                        'attendance_status' => 'present',
                        'status' => 'confirmed',
                        'notes' => 'Current period test data',
                        'created_by' => 1
                    ]);
                    $createdDays++;
                }
            }
            
            $this->command->info("Created {$createdDays} salary records for {$employee->name}");
        }
        
        $this->command->info("Current period salary data created successfully!");
    }
    
    private function getSetting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}