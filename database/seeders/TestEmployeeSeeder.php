<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\DailySalary;
use Carbon\Carbon;

class TestEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test employee if not exists
        $employee = Employee::firstOrCreate(
            ['employee_code' => 'EMP001'],
            [
                'name' => 'Ifta Isnanto Ardani',
                'email' => 'adfita22@gmail.com',
                'phone' => '081334744557',
                'position' => 'Teknisi',
                'department' => 'Teknik',
                'hire_date' => Carbon::now()->subMonths(6),
                'employment_type' => 'freelance',
                'daily_rate' => 115000,
                'status' => 'active'
            ]
        );

        // Create some daily salary records for current period
        $startDate = Carbon::now()->day(11)->subMonth(); // Start from 11th of last month
        $endDate = Carbon::now()->day(10); // End at 10th of current month

        // Clear existing test data
        DailySalary::where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->delete();

        // Create salary records for some days (not all to show partial status)
        $workDays = 0;
        for ($date = $startDate->copy(); $date <= $endDate && $workDays < 15; $date->addDay()) {
            if (!$date->isWeekend() && $workDays < 15) {
                DailySalary::create([
                    'employee_id' => $employee->id,
                    'work_date' => $date->copy(),
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
                    'notes' => 'Test data',
                    'created_by' => 1 // Assuming user ID 1 exists
                ]);
                $workDays++;
            }
        }

        $this->command->info("Test employee and salary data created successfully!");
        $this->command->info("Employee: {$employee->name} ({$employee->employee_code})");
        $this->command->info("Salary records created: {$workDays}");
    }
}