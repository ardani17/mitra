<?php

namespace App\Http\Controllers;

use App\Models\DailySalary;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailySalaryController extends Controller
{
    // Constructor removed - middleware handled by routes

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', DailySalary::class);

        $query = DailySalary::with(['employee', 'createdBy']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Released status filter
        if ($request->filled('released')) {
            if ($request->released === 'yes') {
                $query->whereNotNull('salary_release_id');
            } else {
                $query->whereNull('salary_release_id');
            }
        }

        $dailySalaries = $query->orderBy('work_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(20);

        // Get employees for filter
        $employees = Employee::active()->orderBy('name')->get();

        // Calculate summary
        $summary = [
            'total_amount' => $query->sum(DB::raw('amount + overtime_amount')),
            'total_days' => $query->count(),
            'confirmed_amount' => $query->where('status', 'confirmed')->sum(DB::raw('amount + overtime_amount')),
            'draft_amount' => $query->where('status', 'draft')->sum(DB::raw('amount + overtime_amount'))
        ];

        return view('daily-salaries.index', compact('dailySalaries', 'employees', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize('create', DailySalary::class);

        $employees = Employee::active()->orderBy('name')->get();
        $selectedEmployee = null;
        $workDate = $request->get('date', now()->format('Y-m-d'));

        if ($request->filled('employee_id')) {
            $selectedEmployee = Employee::find($request->employee_id);
        }

        return view('daily-salaries.create', compact('employees', 'selectedEmployee', 'workDate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Employee $employee = null)
    {
        Gate::authorize('create', DailySalary::class);

        // Handle both regular and employee-specific routes
        $employeeId = $employee ? $employee->id : $request->input('employee_id');
        
        // Debug logging
        \Log::info('DailySalary store request', [
            'employee_from_route' => $employee ? $employee->id : null,
            'employee_from_request' => $request->input('employee_id'),
            'all_request_data' => $request->all()
        ]);
        
        // Clean numeric fields by removing dots (thousand separators)
        $numericFields = ['basic_salary', 'meal_allowance', 'attendance_bonus', 'phone_allowance', 'overtime_amount', 'deductions'];
        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->get($field) !== null) {
                $cleanValue = str_replace('.', '', $request->get($field));
                $request->merge([$field => $cleanValue]);
            }
        }
        
        try {
            $validated = $request->validate([
                'employee_id' => $employee ? 'nullable' : 'required|exists:employees,id',
                'work_date' => 'required|date',
                'basic_salary' => 'required|numeric|min:0',
                'meal_allowance' => 'nullable|numeric|min:0',
                'attendance_bonus' => 'nullable|numeric',
                'phone_allowance' => 'nullable|numeric|min:0',
                'attendance_status' => 'required|in:present,late,absent,sick',
                'overtime_amount' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422, ['Content-Type' => 'application/json']);
            }
            throw $e;
        }

        // Set employee_id if coming from employee route
        if ($employee) {
            $validated['employee_id'] = $employee->id;
        }

        // Check for duplicate entry (excluding soft-deleted records)
        $exists = DailySalary::where('employee_id', $validated['employee_id'])
                            ->where('work_date', $validated['work_date'])
                            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gaji untuk tanggal tersebut sudah ada.']);
            }
            return back()->withErrors(['work_date' => 'Gaji untuk karyawan ini pada tanggal tersebut sudah ada.'])
                        ->withInput();
        }

        // Use provided values or calculate defaults
        $basicSalary = $validated['basic_salary'];
        $mealAllowance = $validated['meal_allowance'] ?? 10000;
        $attendanceBonus = $validated['attendance_bonus'] ?? 20000;
        $phoneAllowance = $validated['phone_allowance'] ?? 5000;
        $overtimeAmount = $validated['overtime_amount'] ?? 0;
        $deductions = $validated['deductions'] ?? 0;
        
        // Adjust values based on attendance status if not manually set
        if (!$request->has('meal_allowance') || !$request->has('attendance_bonus') || !$request->has('phone_allowance') || !$request->has('deductions')) {
            switch ($validated['attendance_status']) {
                case 'present':
                    if (!$request->has('meal_allowance')) $mealAllowance = 10000;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 20000;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 5000;
                    break;
                case 'late':
                    if (!$request->has('meal_allowance')) $mealAllowance = 10000;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 5000;
                    break;
                case 'absent':
                    // For Libur: semua nilai 0, tidak dapat gaji apapun
                    if (!$request->has('basic_salary')) $basicSalary = 0;
                    if (!$request->has('meal_allowance')) $mealAllowance = 0;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 0;
                    break;
                case 'sick':
                    // For Sakit: Gaji Pokok = sesuai database, Uang Makan = 0, Uang Absen = 0, Uang Pulsa = 0, Potongan = 65000
                    if (!$request->has('meal_allowance')) $mealAllowance = 0;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 0;
                    if (!$request->has('deductions')) $deductions = 65000;
                    break;
            }
        }

        // Calculate total amount first
        $totalAmount = $basicSalary + $mealAllowance + $attendanceBonus + $phoneAllowance + $overtimeAmount - $deductions;

        $dailySalaryData = [
            'employee_id' => $validated['employee_id'],
            'work_date' => $validated['work_date'],
            'amount' => $totalAmount, // Required field from original migration
            'basic_salary' => $basicSalary,
            'meal_allowance' => $mealAllowance,
            'attendance_bonus' => $attendanceBonus,
            'phone_allowance' => $phoneAllowance,
            'transport_allowance' => 0,
            'overtime_hours' => 0,
            'overtime_amount' => $overtimeAmount,
            'deductions' => $deductions,
            'total_amount' => $totalAmount,
            'attendance_status' => $validated['attendance_status'],
            'status' => 'confirmed',
            'notes' => $validated['notes'],
            'created_by' => auth()->id()
        ];

        try {
            $dailySalary = DailySalary::create($dailySalaryData);
            \Log::info('DailySalary created successfully', ['id' => $dailySalary->id]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Gaji harian berhasil ditambahkan.'], 200, ['Content-Type' => 'application/json']);
            }

            $redirectRoute = $employee ? 'finance.employees.show' : 'daily-salaries.show';
            $redirectParam = $employee ? $employee : $dailySalary;
            
            return redirect()->route($redirectRoute, $redirectParam)
                ->with('success', 'Gaji harian berhasil ditambahkan.');
        } catch (\Exception $e) {
            \Log::error('Failed to create DailySalary', ['error' => $e->getMessage(), 'data' => $dailySalaryData]);
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan gaji: ' . $e->getMessage()], 500, ['Content-Type' => 'application/json']);
            }
            
            return back()->withInput()->with('error', 'Gagal menyimpan gaji: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($employeeOrSalary, $dailySalary = null)
    {
        // Handle both route patterns:
        // 1. /daily-salaries/{dailySalary} - old route
        // 2. /employees/{employee}/daily-salaries/{dailySalary} - new route
        
        if ($dailySalary === null) {
            // Old route pattern - first parameter is DailySalary
            $dailySalary = $employeeOrSalary;
            $employee = null;
        } else {
            // New route pattern - first parameter is Employee, second is DailySalary
            $employee = $employeeOrSalary;
            // Find the daily salary by ID
            $dailySalary = DailySalary::findOrFail($dailySalary);
        }

        Gate::authorize('view', $dailySalary);

        $dailySalary->load(['employee', 'salaryRelease', 'createdBy']);

        // If called via AJAX from employee page, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'id' => $dailySalary->id,
                'work_date' => $dailySalary->work_date->format('Y-m-d'),
                'basic_salary' => $dailySalary->basic_salary,
                'meal_allowance' => $dailySalary->meal_allowance,
                'attendance_bonus' => $dailySalary->attendance_bonus,
                'phone_allowance' => $dailySalary->phone_allowance,
                'attendance_status' => $dailySalary->attendance_status,
                'overtime_hours' => $dailySalary->overtime_hours,
                'overtime_amount' => $dailySalary->overtime_amount,
                'deductions' => $dailySalary->deductions,
                'notes' => $dailySalary->notes
            ]);
        }

        return view('daily-salaries.show', compact('dailySalary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailySalary $dailySalary)
    {
        Gate::authorize('update', $dailySalary);

        // Cannot edit if already released
        if ($dailySalary->is_released) {
            return redirect()->route('daily-salaries.show', $dailySalary)
                ->with('error', 'Tidak dapat mengedit gaji yang sudah dirilis.');
        }

        $employees = Employee::active()->orderBy('name')->get();

        return view('daily-salaries.edit', compact('dailySalary', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee, DailySalary $dailySalary)
    {
        Gate::authorize('update', $dailySalary);

        // Cannot update if already released
        if ($dailySalary->is_released) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah gaji yang sudah dirilis.']);
            }
            return redirect()->route('daily-salaries.show', $dailySalary)
                ->with('error', 'Tidak dapat mengubah gaji yang sudah dirilis.');
        }

        // Clean numeric fields by removing dots (thousand separators)
        $numericFields = ['basic_salary', 'meal_allowance', 'attendance_bonus', 'phone_allowance', 'overtime_amount', 'deductions'];
        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->get($field) !== null) {
                $cleanValue = str_replace('.', '', $request->get($field));
                $request->merge([$field => $cleanValue]);
            }
        }
        
        $validated = $request->validate([
            'work_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'attendance_bonus' => 'nullable|numeric',
            'phone_allowance' => 'nullable|numeric|min:0',
            'attendance_status' => 'required|in:present,late,absent,sick',
            'overtime_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Check for duplicate entry (excluding current record and soft-deleted records)
        $exists = DailySalary::where('employee_id', $employee->id)
                            ->where('work_date', $validated['work_date'])
                            ->where('id', '!=', $dailySalary->id)
                            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gaji untuk tanggal tersebut sudah ada.']);
            }
            return back()->withErrors(['work_date' => 'Gaji untuk karyawan ini pada tanggal tersebut sudah ada.'])
                        ->withInput();
        }

        // Use provided values or calculate defaults
        $basicSalary = $validated['basic_salary'];
        $mealAllowance = $validated['meal_allowance'] ?? $dailySalary->meal_allowance;
        $attendanceBonus = $validated['attendance_bonus'] ?? $dailySalary->attendance_bonus;
        $phoneAllowance = $validated['phone_allowance'] ?? $dailySalary->phone_allowance;
        $overtimeAmount = $validated['overtime_amount'] ?? $dailySalary->overtime_amount;
        $deductions = $validated['deductions'] ?? $dailySalary->deductions;
        
        // Adjust values based on attendance status if not manually set
        if (!$request->has('meal_allowance') || !$request->has('attendance_bonus') || !$request->has('phone_allowance') || !$request->has('deductions')) {
            switch ($validated['attendance_status']) {
                case 'present':
                    if (!$request->has('meal_allowance')) $mealAllowance = 10000;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 20000;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 5000;
                    break;
                case 'late':
                    if (!$request->has('meal_allowance')) $mealAllowance = 10000;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 5000;
                    break;
                case 'absent':
                    // For Libur: semua nilai 0, tidak dapat gaji apapun
                    if (!$request->has('basic_salary')) $basicSalary = 0;
                    if (!$request->has('meal_allowance')) $mealAllowance = 0;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 0;
                    break;
                case 'sick':
                    // For Sakit: Gaji Pokok = sesuai database, Uang Makan = 0, Uang Absen = 0, Uang Pulsa = 0, Potongan = 65000
                    if (!$request->has('meal_allowance')) $mealAllowance = 0;
                    if (!$request->has('attendance_bonus')) $attendanceBonus = 0;
                    if (!$request->has('phone_allowance')) $phoneAllowance = 0;
                    if (!$request->has('deductions')) $deductions = 65000;
                    break;
            }
        }

        // Calculate total amount first
        $totalAmount = $basicSalary + $mealAllowance + $attendanceBonus + $phoneAllowance + $overtimeAmount - $deductions;

        $updateData = [
            'work_date' => $validated['work_date'],
            'amount' => $totalAmount, // Required field from original migration
            'basic_salary' => $basicSalary,
            'meal_allowance' => $mealAllowance,
            'attendance_bonus' => $attendanceBonus,
            'phone_allowance' => $phoneAllowance,
            'overtime_hours' => 0,
            'overtime_amount' => $overtimeAmount,
            'deductions' => $deductions,
            'total_amount' => $totalAmount,
            'attendance_status' => $validated['attendance_status'],
            'notes' => $validated['notes']
        ];

        $dailySalary->update($updateData);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Gaji harian berhasil diperbarui.']);
        }

        return redirect()->route('finance.employees.show', $employee)
            ->with('success', 'Gaji harian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee, DailySalary $dailySalary)
    {
        Gate::authorize('delete', $dailySalary);

        // Cannot delete if already released
        if ($dailySalary->is_released) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus gaji yang sudah dirilis.'], 400);
            }
            return redirect()->route('daily-salaries.index')
                ->with('error', 'Tidak dapat menghapus gaji yang sudah dirilis.');
        }

        try {
            $dailySalary->delete();
            \Log::info('DailySalary deleted successfully', ['id' => $dailySalary->id, 'employee_id' => $employee->id]);

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Gaji harian berhasil dihapus.'], 200);
            }

            return redirect()->route('finance.employees.show', $employee)
                ->with('success', 'Gaji harian berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete DailySalary', ['error' => $e->getMessage(), 'id' => $dailySalary->id]);
            
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus gaji: ' . $e->getMessage()], 500);
            }
            
            return redirect()->route('finance.employees.show', $employee)
                ->with('error', 'Gagal menghapus gaji: ' . $e->getMessage());
        }
    }

    /**
     * Calendar view for daily salary input
     */
    public function calendar(Request $request)
    {
        Gate::authorize('viewAny', DailySalary::class);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $employeeId = $request->get('employee_id');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = DailySalary::with('employee')
                           ->whereBetween('work_date', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $dailySalaries = $query->get()->keyBy(function ($item) {
            return $item->employee_id . '-' . $item->work_date->format('Y-m-d');
        });

        $employees = Employee::active()->orderBy('name')->get();
        $selectedEmployee = $employeeId ? Employee::find($employeeId) : null;

        return view('daily-salaries.calendar', compact(
            'dailySalaries', 
            'employees', 
            'selectedEmployee', 
            'year', 
            'month', 
            'startDate', 
            'endDate'
        ));
    }

    /**
     * Bulk confirm daily salaries
     */
    public function bulkConfirm(Request $request)
    {
        Gate::authorize('create', DailySalary::class);

        $validated = $request->validate([
            'salary_ids' => 'required|array',
            'salary_ids.*' => 'exists:daily_salaries,id'
        ]);

        $updated = DailySalary::whereIn('id', $validated['salary_ids'])
                             ->where('status', 'draft')
                             ->whereNull('salary_release_id')
                             ->update(['status' => 'confirmed']);

        return back()->with('success', "{$updated} gaji berhasil dikonfirmasi.");
    }

    /**
     * Get employee daily rate for AJAX
     */
    public function getEmployeeRate(Employee $employee)
    {
        return response()->json([
            'daily_rate' => $employee->daily_rate,
            'formatted_rate' => $employee->formatted_daily_rate
        ]);
    }
}
