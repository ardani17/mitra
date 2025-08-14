<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\DailySalary;
use App\Models\Setting;
use App\Models\EmployeeCustomOffDay;
use App\Models\EmployeeWorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalaryPeriodService
{
    /**
     * Get current salary period based on cut-off settings
     */
    public function getCurrentPeriod($date = null): array
    {
        $date = $date ? Carbon::parse($date) : now();
        $startDay = (int) $this->getSetting('salary_cutoff_start_day', 11);
        $endDay = (int) $this->getSetting('salary_cutoff_end_day', 10);
        
        // Jika tanggal sekarang >= start day, periode dimulai bulan ini
        if ($date->day >= $startDay) {
            $periodStart = $date->copy()->day($startDay);
            $periodEnd = $date->copy()->addMonth()->day($endDay);
        } else {
            // Jika tanggal sekarang < start day, periode dimulai bulan lalu
            $periodStart = $date->copy()->subMonth()->day($startDay);
            $periodEnd = $date->copy()->day($endDay);
        }
        
        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'name' => $this->getPeriodName($periodStart, $periodEnd),
            'working_days' => $this->getWorkingDaysInPeriod($periodStart, $periodEnd)
        ];
    }
    
    /**
     * Get period for specific month/year
     */
    public function getPeriodForMonth($month, $year): array
    {
        $startDay = (int) $this->getSetting('salary_cutoff_start_day', 11);
        $endDay = (int) $this->getSetting('salary_cutoff_end_day', 10);
        
        // Create period end date first (target month)
        $periodEnd = Carbon::create($year, $month, $endDay);
        
        // Period start is previous month
        $periodStart = $periodEnd->copy()->subMonth()->day($startDay);
        
        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'name' => $this->getPeriodName($periodStart, $periodEnd),
            'working_days' => $this->getWorkingDaysInPeriod($periodStart, $periodEnd)
        ];
    }
    
    /**
     * Generate period name based on end date
     */
    public function getPeriodName($startDate, $endDate): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Nama periode berdasarkan bulan akhir
        $endMonth = $months[$endDate->month];
        $endYear = $endDate->year;
        
        return "{$endMonth} {$endYear}";
    }
    
    /**
     * Calculate working days in period (total days without weekend deduction)
     * New formula: Total days in period = working days base
     * Individual off days will be deducted per employee
     */
    public function getWorkingDaysInPeriod($startDate, $endDate): int
    {
        return $startDate->diffInDays($endDate) + 1;
    }
    
    /**
     * Get salary status for single employee
     */
    public function getEmployeeSalaryStatus($employeeId, $period = null): array
    {
        $period = $period ?: $this->getCurrentPeriod();
        $employee = Employee::find($employeeId);
        
        $inputDays = DailySalary::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$period['start'], $period['end']])
            ->where('status', 'confirmed')
            ->count();
        
        // PERUBAHAN: Gunakan perhitungan per employee
        $workingDays = $employee ? $employee->getWorkingDaysInPeriod($period['start'], $period['end']) : $period['working_days'];
        $percentage = $workingDays > 0 ? ($inputDays / $workingDays) * 100 : 0;
        
        return [
            'employee_id' => $employeeId,
            'employee' => $employee,
            'period' => $period,
            'working_days' => $workingDays, // Sekarang per employee
            'input_days' => $inputDays,
            'percentage' => round($percentage, 1),
            'status' => $this->determineSalaryStatus($percentage),
            'last_input_date' => $this->getLastSalaryInputDate($employeeId, $period)
        ];
    }
    
    /**
     * Get salary status for all employees
     */
    public function getAllEmployeesSalaryStatus($period = null): Collection
    {
        $period = $period ?: $this->getCurrentPeriod();
        
        $employees = Employee::active()->get();
        $statuses = collect();
        
        // Get all salary data for the period at once
        $salaryData = DailySalary::whereBetween('work_date', [$period['start'], $period['end']])
            ->where('status', 'confirmed')
            ->selectRaw('employee_id, COUNT(*) as input_days, MAX(work_date) as last_input_date')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');
        
        foreach ($employees as $employee) {
            $salary = $salaryData->get($employee->id);
            $inputDays = $salary ? $salary->input_days : 0;
            
            // PERUBAHAN: Gunakan perhitungan per employee
            $workingDays = $employee->getWorkingDaysInPeriod($period['start'], $period['end']);
            $percentage = $workingDays > 0 ? ($inputDays / $workingDays) * 100 : 0;
            
            $statuses->push([
                'employee' => $employee,
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'employee_code' => $employee->employee_code,
                'period' => $period,
                'working_days' => $workingDays, // Sekarang per employee
                'input_days' => $inputDays,
                'percentage' => round($percentage, 1),
                'status' => $this->determineSalaryStatus($percentage),
                'last_input_date' => $salary ? $salary->last_input_date : null,
                'off_days_count' => $employee->customOffDays()
                    ->whereBetween('off_date', [$period['start'], $period['end']])
                    ->count()
            ]);
        }
        
        return $statuses;
    }
    
    /**
     * Get salary status summary
     */
    public function getSalaryStatusSummary($period = null): array
    {
        $period = $period ?: $this->getCurrentPeriod();
        $statuses = $this->getAllEmployeesSalaryStatus($period);
        
        $summary = [
            'period' => $period,
            'total' => $statuses->count(),
            'complete' => $statuses->where('status', 'complete')->count(),
            'partial' => $statuses->where('status', 'partial')->count(),
            'empty' => $statuses->where('status', 'empty')->count(),
            'employees' => $statuses->toArray()
        ];
        
        // Add percentage calculations
        $summary['complete_percentage'] = $summary['total'] > 0 ? round(($summary['complete'] / $summary['total']) * 100, 1) : 0;
        $summary['partial_percentage'] = $summary['total'] > 0 ? round(($summary['partial'] / $summary['total']) * 100, 1) : 0;
        $summary['empty_percentage'] = $summary['total'] > 0 ? round(($summary['empty'] / $summary['total']) * 100, 1) : 0;
        
        return $summary;
    }
    
    /**
     * Get calendar data for period
     */
    public function getCalendarData($period = null, $employeeId = null): array
    {
        $period = $period ?: $this->getCurrentPeriod();
        $calendar = [];
        
        $query = DailySalary::whereBetween('work_date', [$period['start'], $period['end']])
            ->where('status', 'confirmed');
        
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        
        $salaries = $query->get()->groupBy(function($item) {
            return $item->work_date->format('Y-m-d');
        });
        
        $totalEmployees = Employee::active()->count();
        
        for ($date = $period['start']->copy(); $date <= $period['end']; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayData = [
                'date' => $date->copy(),
                'day_number' => $date->day,
                'day_name' => $this->getDayName($date->format('l')),
                'is_weekend' => $date->isWeekend(),
                'has_input' => isset($salaries[$dateStr]),
                'input_count' => isset($salaries[$dateStr]) ? $salaries[$dateStr]->count() : 0,
                'total_employees' => $totalEmployees
            ];
            
            // Determine status
            if ($dayData['is_weekend']) {
                $dayData['status'] = 'weekend';
                $dayData['icon'] = '🔒';
            } elseif ($dayData['input_count'] == 0) {
                $dayData['status'] = 'empty';
                $dayData['icon'] = '❌';
            } elseif ($dayData['input_count'] == $dayData['total_employees']) {
                $dayData['status'] = 'complete';
                $dayData['icon'] = '✅';
            } else {
                $dayData['status'] = 'partial';
                $dayData['icon'] = '⚠️';
            }
            
            $calendar[] = $dayData;
        }
        
        return $calendar;
    }
    
    /**
     * Determine salary status based on percentage
     */
    private function determineSalaryStatus($percentage): string
    {
        $completeThreshold = (int) $this->getSetting('salary_status_complete_threshold', 90);
        $partialThreshold = (int) $this->getSetting('salary_status_partial_threshold', 50);
        
        if ($percentage >= $completeThreshold) return 'complete';
        if ($percentage >= $partialThreshold) return 'partial';
        return 'empty';
    }
    
    /**
     * Get last salary input date for employee
     */
    private function getLastSalaryInputDate($employeeId, $period): ?string
    {
        return DailySalary::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$period['start'], $period['end']])
            ->where('status', 'confirmed')
            ->latest('work_date')
            ->value('work_date');
    }
    
    /**
     * Get day name in Indonesian
     */
    private function getDayName($englishDay): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        return $days[$englishDay] ?? $englishDay;
    }
    
    /**
     * Get setting value with fallback
     */
    private function getSetting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Get next period
     */
    public function getNextPeriod($currentPeriod = null): array
    {
        $currentPeriod = $currentPeriod ?: $this->getCurrentPeriod();
        $nextMonth = $currentPeriod['end']->copy()->addDay();
        
        return $this->getCurrentPeriod($nextMonth);
    }
    
    /**
     * Get previous period
     */
    public function getPreviousPeriod($currentPeriod = null): array
    {
        $currentPeriod = $currentPeriod ?: $this->getCurrentPeriod();
        $prevMonth = $currentPeriod['start']->copy()->subDay();
        
        return $this->getCurrentPeriod($prevMonth);
    }
    
    /**
     * Get working days for specific employee (backward compatibility method)
     */
    public function getWorkingDaysForEmployee($employeeId, $startDate, $endDate): int
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            return $this->getWorkingDaysInPeriod($startDate, $endDate);
        }
        
        return $employee->getWorkingDaysInPeriod($startDate, $endDate);
    }
    
    /**
     * Get employees with custom off days
     */
    public function getEmployeesWithCustomOffDays($period = null): Collection
    {
        $period = $period ?: $this->getCurrentPeriod();
        
        return Employee::active()
            ->whereHas('customOffDays', function($query) use ($period) {
                $query->whereBetween('off_date', [$period['start'], $period['end']]);
            })
            ->with(['customOffDays' => function($query) use ($period) {
                $query->whereBetween('off_date', [$period['start'], $period['end']])
                      ->orderBy('off_date');
            }])
            ->get();
    }
    
    /**
     * Get summary of off days usage
     */
    public function getOffDaysSummary($period = null): array
    {
        $period = $period ?: $this->getCurrentPeriod();
        $employees = Employee::active()->get();
        
        $summary = [
            'total_employees' => $employees->count(),
            'employees_with_off_days' => 0,
            'total_off_days' => 0,
            'average_off_days_per_employee' => 0
        ];
        
        foreach ($employees as $employee) {
            $offDaysCount = $employee->customOffDays()
                ->whereBetween('off_date', [$period['start'], $period['end']])
                ->count();
            
            if ($offDaysCount > 0) {
                $summary['employees_with_off_days']++;
                $summary['total_off_days'] += $offDaysCount;
            }
        }
        
        if ($summary['employees_with_off_days'] > 0) {
            $summary['average_off_days_per_employee'] = round($summary['total_off_days'] / $summary['employees_with_off_days'], 1);
        }
        
        return $summary;
    }
}