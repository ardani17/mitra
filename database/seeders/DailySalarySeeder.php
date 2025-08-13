<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\DailySalary;
use Carbon\Carbon;

class DailySalarySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get first employee or create one if none exists
        $employee = Employee::first();
        
        if (!$employee) {
            $employee = Employee::create([
                'employee_code' => 'EMP001',
                'name' => 'Basuki Suryanto',
                'email' => 'basuki@example.com',
                'phone' => '081234567890',
                'position' => 'Staff IT',
                'department' => 'IT',
                'hire_date' => now()->subMonths(6),
                'daily_rate' => 150000,
                'status' => 'active',
                'employment_type' => 'permanent'
            ]);
        }

        // Create daily salary data for current month
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        // Standard allowances
        $mealAllowance = 15000;
        $phoneAllowance = 10000;
        $transportAllowance = 20000;
        $attendanceBonus = 5000;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends for this example
            if ($date->isWeekend()) {
                continue;
            }
            
            // Skip if data already exists
            if (DailySalary::where('employee_id', $employee->id)
                           ->where('work_date', $date->format('Y-m-d'))
                           ->exists()) {
                continue;
            }
            
            // Skip some random days to simulate absences
            if (rand(1, 10) > 8) {
                continue;
            }
            
            // Determine attendance status
            $attendanceStatuses = ['present', 'present', 'present', 'present', 'late'];
            $attendanceStatus = $attendanceStatuses[array_rand($attendanceStatuses)];
            
            // Calculate attendance bonus/deduction
            $finalAttendanceBonus = $attendanceStatus === 'present' ? $attendanceBonus : 
                                   ($attendanceStatus === 'late' ? -($attendanceBonus / 2) : 0);
            
            // Random overtime (0-3 hours)
            $overtimeHours = rand(0, 3);
            $overtimeAmount = $overtimeHours * ($employee->daily_rate / 8) * 1.5;
            
            // Random deductions (0-10000)
            $deductions = rand(0, 1) > 0.8 ? rand(5000, 10000) : 0;
            
            // Calculate total
            $totalAmount = $employee->daily_rate + $mealAllowance + $finalAttendanceBonus + 
                          $phoneAllowance + $transportAllowance + $overtimeAmount - $deductions;

            DailySalary::create([
                'employee_id' => $employee->id,
                'work_date' => $date->format('Y-m-d'),
                'amount' => $employee->daily_rate, // Keep for backward compatibility
                'basic_salary' => $employee->daily_rate,
                'meal_allowance' => $mealAllowance,
                'attendance_bonus' => $finalAttendanceBonus,
                'phone_allowance' => $phoneAllowance,
                'transport_allowance' => $transportAllowance,
                'attendance_status' => $attendanceStatus,
                'check_in_time' => $attendanceStatus === 'late' ? '08:30:00' : '08:00:00',
                'check_out_time' => '17:00:00',
                'hours_worked' => 8,
                'overtime_hours' => $overtimeHours,
                'overtime_amount' => $overtimeAmount,
                'deductions' => $deductions,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
                'notes' => $attendanceStatus === 'late' ? 'Terlambat 30 menit' : null,
                'created_by' => 1
            ]);
        }

        // Create data for previous month as well
        $prevStartDate = now()->subMonth()->startOfMonth();
        $prevEndDate = now()->subMonth()->endOfMonth();
        
        for ($date = $prevStartDate->copy(); $date->lte($prevEndDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }
            
            // Skip if data already exists
            if (DailySalary::where('employee_id', $employee->id)
                           ->where('work_date', $date->format('Y-m-d'))
                           ->exists()) {
                continue;
            }
            
            if (rand(1, 10) > 8) {
                continue;
            }
            
            $attendanceStatus = ['present', 'present', 'present', 'late'][array_rand(['present', 'present', 'present', 'late'])];
            $finalAttendanceBonus = $attendanceStatus === 'present' ? $attendanceBonus : 
                                   ($attendanceStatus === 'late' ? -($attendanceBonus / 2) : 0);
            
            $overtimeHours = rand(0, 2);
            $overtimeAmount = $overtimeHours * ($employee->daily_rate / 8) * 1.5;
            $deductions = rand(0, 1) > 0.9 ? rand(5000, 8000) : 0;
            
            $totalAmount = $employee->daily_rate + $mealAllowance + $finalAttendanceBonus + 
                          $phoneAllowance + $transportAllowance + $overtimeAmount - $deductions;

            DailySalary::create([
                'employee_id' => $employee->id,
                'work_date' => $date->format('Y-m-d'),
                'amount' => $employee->daily_rate,
                'basic_salary' => $employee->daily_rate,
                'meal_allowance' => $mealAllowance,
                'attendance_bonus' => $finalAttendanceBonus,
                'phone_allowance' => $phoneAllowance,
                'transport_allowance' => $transportAllowance,
                'attendance_status' => $attendanceStatus,
                'check_in_time' => $attendanceStatus === 'late' ? '08:15:00' : '08:00:00',
                'check_out_time' => '17:00:00',
                'hours_worked' => 8,
                'overtime_hours' => $overtimeHours,
                'overtime_amount' => $overtimeAmount,
                'deductions' => $deductions,
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
                'notes' => $attendanceStatus === 'late' ? 'Terlambat 15 menit' : null,
                'created_by' => 1
            ]);
        }

        $this->command->info('Daily salary sample data created successfully!');
    }
}