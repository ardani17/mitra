<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeWorkSchedule;
use App\Services\SalaryPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmployeeWorkScheduleController extends Controller
{
    protected $salaryPeriodService;

    public function __construct(SalaryPeriodService $salaryPeriodService)
    {
        $this->salaryPeriodService = $salaryPeriodService;
    }

    /**
     * Display work schedules for an employee
     */
    public function index(Employee $employee)
    {
        Gate::authorize('view', $employee);

        $schedules = $employee->workSchedules()
            ->orderBy('effective_from', 'desc')
            ->paginate(10);

        $currentSchedule = $employee->currentWorkSchedule();
        
        return view('employees.work-schedules.index', compact('employee', 'schedules', 'currentSchedule'));
    }

    /**
     * Show the form for creating a new work schedule
     */
    public function create(Employee $employee)
    {
        Gate::authorize('update', $employee);

        $dayOptions = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa', 
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        return view('employees.work-schedules.create', compact('employee', 'dayOptions'));
    }

    /**
     * Store a newly created work schedule
     */
    public function store(Request $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        $rules = [
            'schedule_type' => 'required|in:standard,custom,flexible',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:1000'
        ];

        // Add conditional validation based on schedule type
        if ($request->schedule_type === 'flexible') {
            $rules['work_days_per_month'] = 'required|integer|min:1|max:31';
        } else {
            $rules['standard_off_days'] = 'required|array|min:1';
            $rules['standard_off_days.*'] = 'integer|min:0|max:6';
        }

        $validated = $request->validate($rules);

        // Deactivate previous schedules if this is set as active
        if ($request->has('set_as_active') && $request->set_as_active) {
            $employee->workSchedules()->update(['is_active' => false]);
            $validated['is_active'] = true;
        }

        $schedule = $employee->workSchedules()->create($validated);

        return redirect()->route('finance.employees.work-schedules.index', $employee)
            ->with('success', 'Jadwal kerja berhasil dibuat.');
    }

    /**
     * Display the specified work schedule
     */
    public function show(Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('view', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        // Get period for demonstration
        $currentPeriod = $this->salaryPeriodService->getCurrentPeriod();
        $workingDays = $employee->getWorkingDaysInPeriod($currentPeriod['start'], $currentPeriod['end']);

        return view('employees.work-schedules.show', compact('employee', 'workSchedule', 'currentPeriod', 'workingDays'));
    }

    /**
     * Show the form for editing the specified work schedule
     */
    public function edit(Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('update', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        $dayOptions = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        return view('employees.work-schedules.edit', compact('employee', 'workSchedule', 'dayOptions'));
    }

    /**
     * Update the specified work schedule
     */
    public function update(Request $request, Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('update', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        $rules = [
            'schedule_type' => 'required|in:standard,custom,flexible',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:1000'
        ];

        // Add conditional validation based on schedule type
        if ($request->schedule_type === 'flexible') {
            $rules['work_days_per_month'] = 'required|integer|min:1|max:31';
        } else {
            $rules['standard_off_days'] = 'required|array|min:1';
            $rules['standard_off_days.*'] = 'integer|min:0|max:6';
        }

        $validated = $request->validate($rules);

        $workSchedule->update($validated);

        return redirect()->route('finance.employees.work-schedules.show', [$employee, $workSchedule])
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified work schedule
     */
    public function destroy(Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('update', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        // Don't allow deletion of active schedule if it's the only one
        if ($workSchedule->is_active && $employee->workSchedules()->active()->count() === 1) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus jadwal kerja aktif terakhir. Buat jadwal baru terlebih dahulu.');
        }

        $workSchedule->delete();

        return redirect()->route('finance.employees.work-schedules.index', $employee)
            ->with('success', 'Jadwal kerja berhasil dihapus.');
    }

    /**
     * Activate a work schedule
     */
    public function activate(Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('update', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        $workSchedule->activate();

        return redirect()->back()
            ->with('success', 'Jadwal kerja berhasil diaktifkan.');
    }

    /**
     * Deactivate a work schedule
     */
    public function deactivate(Employee $employee, EmployeeWorkSchedule $workSchedule)
    {
        Gate::authorize('update', $employee);

        // Ensure the work schedule belongs to the employee
        if ($workSchedule->employee_id !== $employee->id) {
            abort(404);
        }

        // Don't allow deactivation if it's the only active schedule
        if ($employee->workSchedules()->active()->count() === 1) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menonaktifkan jadwal kerja terakhir. Buat jadwal baru terlebih dahulu.');
        }

        $workSchedule->deactivate();

        return redirect()->back()
            ->with('success', 'Jadwal kerja berhasil dinonaktifkan.');
    }

    /**
     * Create default schedule for employee
     */
    public function createDefault(Employee $employee)
    {
        Gate::authorize('update', $employee);

        // Check if employee already has active schedule
        if ($employee->workSchedules()->active()->exists()) {
            return redirect()->back()
                ->with('error', 'Karyawan sudah memiliki jadwal kerja aktif.');
        }

        $this->salaryPeriodService->createDefaultScheduleForEmployee($employee->id);

        return redirect()->route('finance.employees.work-schedules.index', $employee)
            ->with('success', 'Jadwal kerja default berhasil dibuat.');
    }

    /**
     * Bulk create default schedules for all employees
     */
    public function bulkCreateDefault()
    {
        Gate::authorize('viewAny', Employee::class);

        $created = $this->salaryPeriodService->createDefaultSchedulesForAllEmployees();

        return redirect()->back()
            ->with('success', "Berhasil membuat jadwal kerja default untuk {$created} karyawan.");
    }

    /**
     * Get schedule summary
     */
    public function summary()
    {
        Gate::authorize('viewAny', Employee::class);

        $summary = $this->salaryPeriodService->getScheduleTypeSummary();
        $customScheduleEmployees = $this->salaryPeriodService->getEmployeesWithCustomSchedules();

        return view('employees.work-schedules.summary', compact('summary', 'customScheduleEmployees'));
    }
}