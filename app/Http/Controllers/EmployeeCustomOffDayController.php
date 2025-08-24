<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCustomOffDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\DailySalary;
use Illuminate\Support\Facades\Auth;

class EmployeeCustomOffDayController extends Controller
{
    /**
     * Display custom off days for an employee
     */
    public function index(Employee $employee, Request $request)
    {
        Gate::authorize('view', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $offDays = $employee->customOffDays()
            ->forPeriod($year, $month)
            ->orderBy('off_date')
            ->get();

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.index', compact(
            'employee', 'offDays', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Show the form for creating new custom off days
     */
    public function create(Employee $employee, Request $request)
    {
        Gate::authorize('update', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.create', compact(
            'employee', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Store newly created custom off days
     */
    public function store(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_dates' => 'required|array|min:1',
            'off_dates.*' => 'date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $created = EmployeeCustomOffDay::createMultiple(
            $employee->id,
            $validated['off_dates'],
            $validated['reason'],
            $validated['period_month'],
            $validated['period_year']
        );

        return redirect()->route('finance.employees.custom-off-days.index', [
            'employee' => $employee,
            'year' => $validated['period_year'],
            'month' => $validated['period_month']
        ])->with('success', "Berhasil menambahkan {$created->count()} hari libur custom.");
    }

    /**
     * Display the specified custom off day
     */
    public function show(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('view', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        return view('employees.custom-off-days.show', compact('employee', 'customOffDay'));
    }

    /**
     * Show the form for editing the specified custom off day
     */
    public function edit(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.edit', compact(
            'employee', 'customOffDay', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Update the specified custom off day
     */
    public function update(Request $request, Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $validated = $request->validate([
            'off_date' => 'required|date',
            'reason' => 'nullable|string|max:255',
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $customOffDay->update($validated);

        return redirect()->route('finance.employees.custom-off-days.show', [$employee, $customOffDay])
            ->with('success', 'Hari libur custom berhasil diperbarui.');
    }

    /**
     * Remove the specified custom off day
     */
    public function destroy(Employee $employee, EmployeeCustomOffDay $customOffDay)
    {
        Gate::authorize('update', $employee);

        // Ensure the custom off day belongs to the employee
        if ($customOffDay->employee_id !== $employee->id) {
            abort(404);
        }

        $customOffDay->delete();

        return redirect()->route('finance.employees.custom-off-days.index', $employee)
            ->with('success', 'Hari libur custom berhasil dihapus.');
    }

    /**
     * Bulk delete custom off days for a period
     */
    public function bulkDelete(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030'
        ]);

        $deleted = $employee->customOffDays()
            ->forPeriod($validated['period_year'], $validated['period_month'])
            ->delete();

        return redirect()->back()
            ->with('success', "Berhasil menghapus {$deleted} hari libur custom.");
    }

    /**
     * Get calendar view for attendance status
     */
    public function calendar(Employee $employee, Request $request)
    {
        Gate::authorize('view', $employee);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Get all days in the month
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Get daily salaries for the month (attendance status)
        $dailySalaries = $employee->dailySalaries()
            ->whereBetween('work_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function($item) {
                return $item->work_date->day;
            });

        // Get custom off days for backward compatibility
        $offDays = $employee->customOffDays()
            ->forPeriod($year, $month)
            ->get()
            ->keyBy(function($item) {
                return $item->off_date->day;
            });

        // Build calendar array with attendance status
        $calendar = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dailySalary = $dailySalaries->get($day);
            $customOffDay = $offDays->get($day);
            
            // Determine attendance status
            $attendanceStatus = null;
            if ($dailySalary) {
                $attendanceStatus = $dailySalary->attendance_status;
            } elseif ($customOffDay) {
                // For backward compatibility, treat custom off days as 'absent'
                $attendanceStatus = 'absent';
            }
            
            $calendar[] = [
                'date' => $date,
                'day_number' => $day,
                'day_name' => $this->getDayName($date->format('l')),
                'is_weekend' => $date->isWeekend(),
                'is_today' => $date->isToday(),
                'attendance_status' => $attendanceStatus,
                'daily_salary' => $dailySalary,
                'custom_off_day' => $customOffDay,
                'has_custom_off' => $offDays->has($day) // Keep for backward compatibility
            ];
        }

        $monthOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $yearOptions = range(2020, 2030);

        return view('employees.custom-off-days.calendar', compact(
            'employee', 'calendar', 'year', 'month', 'monthOptions', 'yearOptions'
        ));
    }

    /**
     * Quick add custom off day via AJAX
     */
    public function quickAdd(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_date' => 'required|date',
            'reason' => 'nullable|string|max:255'
        ]);

        $date = Carbon::parse($validated['off_date']);
        
        $customOffDay = EmployeeCustomOffDay::updateOrCreate([
            'employee_id' => $employee->id,
            'off_date' => $date->format('Y-m-d')
        ], [
            'reason' => $validated['reason'] ?: 'Libur custom',
            'period_month' => $date->month,
            'period_year' => $date->year
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hari libur custom berhasil ditambahkan',
                'data' => $customOffDay
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hari libur custom berhasil ditambahkan.');
    }

    /**
     * Quick remove custom off day via AJAX
     */
    public function quickRemove(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'off_date' => 'required|date'
        ]);

        $deleted = $employee->customOffDays()
            ->where('off_date', $validated['off_date'])
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Hari libur custom berhasil dihapus',
                'deleted' => $deleted
            ]);
        }

        return redirect()->back()
            ->with('success', 'Hari libur custom berhasil dihapus.');
    }

    /**
     * Get day name in Indonesian
     */
    private function getDayName($englishDay)
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
     * Update attendance status for a specific date
     */
    public function updateAttendanceStatus(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'work_date' => 'required|date',
            'attendance_status' => 'required|in:present,late,absent,sick'
        ]);

        $workDate = Carbon::parse($validated['work_date']);
        
        // Set default values based on attendance status (same as form "Tambah Gaji Harian")
        $defaultValues = $this->getDefaultSalaryValues($validated['attendance_status'], $employee);
        
        // Create or update daily salary record with proper default values
        $dailySalary = DailySalary::updateOrCreate([
            'employee_id' => $employee->id,
            'work_date' => $workDate->format('Y-m-d')
        ], array_merge($defaultValues, [
            'attendance_status' => $validated['attendance_status'],
            'hours_worked' => 8,
            'status' => 'confirmed',
            'created_by' => Auth::id()
        ]));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status kehadiran berhasil diperbarui',
                'data' => [
                    'attendance_status' => $dailySalary->attendance_status,
                    'formatted_date' => $workDate->format('d/m/Y'),
                    'day_name' => $this->getDayName($workDate->format('l')),
                    'salary_details' => [
                        'basic_salary' => $dailySalary->basic_salary,
                        'meal_allowance' => $dailySalary->meal_allowance,
                        'attendance_bonus' => $dailySalary->attendance_bonus,
                        'phone_allowance' => $dailySalary->phone_allowance,
                        'overtime_amount' => $dailySalary->overtime_amount,
                        'deductions' => $dailySalary->deductions,
                        'total_amount' => $dailySalary->total_amount
                    ]
                ]
            ]);
        }

        return redirect()->back()
            ->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    /**
     * Get default salary values based on attendance status (same as form "Tambah Gaji Harian")
     */
    private function getDefaultSalaryValues($attendanceStatus, Employee $employee)
    {
        $basicSalary = $employee->daily_rate;
        
        switch ($attendanceStatus) {
            case 'present':
                // Hadir: Gaji Pokok = database, Uang Makan = 10000, Uang Absen = 20000, Uang Pulsa = 5000
                return [
                    'basic_salary' => $basicSalary,
                    'meal_allowance' => 10000,
                    'attendance_bonus' => 20000,
                    'phone_allowance' => 5000,
                    'overtime_amount' => 0,
                    'deductions' => 0,
                    'amount' => $basicSalary,
                    'total_amount' => $basicSalary + 10000 + 20000 + 5000
                ];
                
            case 'late':
                // Telat: Gaji Pokok = database, Uang Makan = 10000, Uang Absen = 0, Uang Pulsa = 5000
                return [
                    'basic_salary' => $basicSalary,
                    'meal_allowance' => 10000,
                    'attendance_bonus' => 0,
                    'phone_allowance' => 5000,
                    'overtime_amount' => 0,
                    'deductions' => 0,
                    'amount' => $basicSalary,
                    'total_amount' => $basicSalary + 10000 + 5000
                ];
                
            case 'absent':
                // Libur: semua nilai 0, tidak dapat gaji apapun
                return [
                    'basic_salary' => 0,
                    'meal_allowance' => 0,
                    'attendance_bonus' => 0,
                    'phone_allowance' => 0,
                    'overtime_amount' => 0,
                    'deductions' => 0,
                    'amount' => 0,
                    'total_amount' => 0
                ];
                
            case 'sick':
                // Sakit: Gaji Pokok = database, Uang Makan = 0, Uang Absen = 0, Uang Pulsa = 0, Potongan = 65000
                return [
                    'basic_salary' => $basicSalary,
                    'meal_allowance' => 0,
                    'attendance_bonus' => 0,
                    'phone_allowance' => 0,
                    'overtime_amount' => 0,
                    'deductions' => 65000,
                    'amount' => $basicSalary,
                    'total_amount' => $basicSalary - 65000
                ];
                
            default:
                // Default case
                return [
                    'basic_salary' => $basicSalary,
                    'meal_allowance' => 10000,
                    'attendance_bonus' => 20000,
                    'phone_allowance' => 5000,
                    'overtime_amount' => 0,
                    'deductions' => 0,
                    'amount' => $basicSalary,
                    'total_amount' => $basicSalary + 10000 + 20000 + 5000
                ];
        }
    }

    /**
     * Get attendance status for a specific date (AJAX)
     */
    public function getAttendanceStatus(Request $request, Employee $employee)
    {
        Gate::authorize('view', $employee);

        $workDate = $request->get('work_date');
        if (!$workDate) {
            return response()->json(['error' => 'Tanggal tidak valid'], 400);
        }

        $dailySalary = $employee->dailySalaries()
            ->where('work_date', $workDate)
            ->first();

        return response()->json([
            'attendance_status' => $dailySalary ? $dailySalary->attendance_status : null,
            'has_record' => $dailySalary ? true : false
        ]);
    }

    /**
     * Delete attendance status for a specific date
     */
    public function deleteAttendanceStatus(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'work_date' => 'required|date'
        ]);

        $workDate = Carbon::parse($validated['work_date']);
        
        // Find and delete the daily salary record for this date
        $deleted = $employee->dailySalaries()
            ->where('work_date', $workDate->format('Y-m-d'))
            ->delete();

        if ($request->expectsJson()) {
            if ($deleted > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status kehadiran berhasil dihapus',
                    'deleted' => $deleted
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data kehadiran untuk tanggal ini'
                ], 404);
            }
        }

        if ($deleted > 0) {
            return redirect()->back()
                ->with('success', 'Status kehadiran berhasil dihapus.');
        } else {
            return redirect()->back()
                ->with('error', 'Tidak ada data kehadiran untuk tanggal ini.');
        }
    }
}